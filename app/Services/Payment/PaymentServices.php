<?php

namespace App\Services\Payment;

use App\Enum\PaymentStatus;
use App\Enum\SubscriptionStatus;
use App\Interface\Payments\PaymentGateway;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PaymentServices
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private SubscriptionService $subscription
    ) {}

    public function checkout(Subscription $subscription, User $payer, ?string $provider = null): Payment
    {
        $existingPayment = $subscription->payments()
            ->where('status', PaymentStatus::Pending)
            ->whereNotNull('checkout_url')
            ->latest()
            ->first();

        if ($existingPayment) {
            return $existingPayment;
        }

        $subscription->loadMissing('plan');
        $provider = $provider ?? config('payment.default');

        // buat payment row baru
        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'provider' => $provider,
            'reference' => 'PAY-'.Str::ulid(),
            'amount' => $subscription->plan->price,
            'status' => PaymentStatus::Pending,
        ]);

        // kirim ke midtrans
        $result = $this->gateway($provider)->createCheckout($payment, $payer);
        // kirim payment url dari midtrans
        $payment->update([
            'checkout_url' => $result->url,
        ]);

        return $payment;
    }

    public function handleWebhook(string $provider, Request $request): void
    {
        // 1. Verifikasi di luar transaksi: murah dan tidak butuh lock
        $r = $this->gateway($provider)->parseWebhook($request);   // throws InvalidWebhook

        DB::transaction(function () use ($r, $provider) {
            // 2. Lock baris payment agar webhook paralel mengantre
            $payment = Payment::where('reference', $r->reference)
                ->where('provider', $provider)
                ->lockForUpdate()
                ->firstOrFail();

            // 3. Status final tidak boleh berubah lagi (duplikat, replay, urutan terbalik)
            if ($payment->status !== PaymentStatus::Pending) {
                return;
            }

            // 4. Jumlah harus cocok dengan yang kita simpan
            if ($r->amount !== $payment->amount) {
                Log::warning('payment.amount_mismatch', [
                    'reference' => $r->reference,
                    'expected' => $payment->amount,
                    'got' => $r->amount,
                ]);

                return;
            }

            // 5. Pending dari gateway: tidak ada perubahan
            if ($r->status === PaymentStatus::Pending) {
                return;
            }

            $payment->update([
                'status' => $r->status,
                'provider_transaction_id' => $r->transactionId,
                'payload' => $r->payload,
                'paid_at' => $r->status === PaymentStatus::Paid ? now() : null,
            ]);

            if ($r->status !== PaymentStatus::Paid) {
                return;
            }

            // 6. Teruskan ke subscription
            $sub = Subscription::with(['plan', 'user', 'organization'])
                ->lockForUpdate()
                ->findOrFail($payment->subscription_id);

            if ($sub->status === SubscriptionStatus::Pending) {
                $this->subscription->activate($sub, $sub->owner());
            } elseif ($sub->plan->duration_days) {
                $this->subscription->renew($sub, $sub->owner());
            } else {
                // Bayar ganda untuk license permanen: catat saja, refund manual
                Log::warning('payment.duplicate_for_permanent', ['payment' => $payment->id]);
            }
        });
    }

    private function gateway(string $provider): PaymentGateway
    {
        $class = config("payment.gateways.$provider")
            ?? throw new InvalidArgumentException("Provider tidak dikenal: $provider");

        return app($class);
    }
}
