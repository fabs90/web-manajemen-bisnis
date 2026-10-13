<?php

namespace App\Interface\Payments;

use App\Enum\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MidtransGateway implements PaymentGateway
{
    public function createCheckout(Payment $payment, User $user): CheckoutResult
    {
        $plan = $payment->subscription->plan;
        $res = Http::withBasicAuth(config('services.midtrans.server_key'), '')
            ->post(config('services.midtrans.endpoints'), [
                'transaction_details' => [
                    'order_id' => $payment->reference,
                    'gross_amount' => $payment->amount,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->nomor_telepon,
                ],
                'item_details' => [
                    'id' => (string) ($plan->code ?? $plan->id),
                    'price' => (int) $payment->amount,
                    'quantity' => 1,
                    'name' => mb_substr($plan->name, 0, 50),
                ],
                'callbacks' => [
                    'finish' => route('subscriptions.success'),
                ],
                'override_notification_urls' => [
                    route('webhooks.payments', ['provider' => 'midtrans']),
                ],
            ])
            ->throw()
            ->json();

        return new CheckoutResult($res['redirect_url']);
    }

    public function parseWebhook(Request $request): WebhookResult
    {
        $data = $request->all();
        $expected = hash(
            'sha512',
            ($data['order_id'] ?? '').($data['status_code'] ?? '')
            .($data['gross_amount'] ?? '').config('services.midtrans.server_key')
        );

        if (! hash_equals($expected, (string) ($data['signature_key'] ?? ''))) {
            throw new InvalidWebhook('Signature tidak cocok');
        }
        $ts = $data['transaction_status'] ?? '';
        $status = match (true) {
            $ts === 'settlement',
            $ts === 'capture' && ($data['fraud_status'] ?? 'accept') === 'accept' => PaymentStatus::Paid,
            in_array($ts, ['deny', 'cancel', 'failure'], true) => PaymentStatus::Failed,
            $ts === 'expire' => PaymentStatus::Expired,
            default => PaymentStatus::Pending,
        };

        return new WebhookResult(
            reference: $data['order_id'],
            status: $status,
            transactionId: $data['transaction_id'] ?? null,
            amount: (int) round((float) $data['gross_amount']),
            payload: $data,
        );

    }
}
