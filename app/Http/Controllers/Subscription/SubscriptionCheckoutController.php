<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Payment\PaymentServices;
use App\Services\Subscription\SubscriptionService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubscriptionCheckoutController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected PaymentServices $paymentService,
    ) {}

    /**
     * Halaman uji coba langganan & pembayaran.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $plans = Plan::query()
            ->with('planFeatures')
            ->where('is_active', true)
            ->get();

        $subscriptions = $user->subscriptions()
            ->with(['plan', 'payments' => fn ($query) => $query->latest()])
            ->latest()
            ->get();

        return view('subscription.index', [
            'user' => $user,
            'plans' => $plans,
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Memproses checkout paket langganan dan redirect ke Midtrans Snap.
     */
    public function checkout(Request $request, Plan $plan): RedirectResponse
    {
        $user = $request->user();

        try {
            // 1. Buat atau ambil pending subscription
            $subscription = $this->subscriptionService->subscribe($plan, $user);

            // 2. Generate pembayaran & dapatkan Checkout URL dari gateway
            $payment = $this->paymentService->checkout($subscription, $user);

            // 3. Redirect ke halaman Midtrans Snap dari model Payment
            return redirect()->away($payment->checkout_url);
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses checkout: '.$e->getMessage());
        }
    }

    /**
     * Halaman callback setelah user selesai transaksi di Midtrans.
     */
    public function success(Request $request): View
    {
        return view('subscription.success', [
            'orderId' => $request->query('order_id'),
            'statusCode' => $request->query('status_code'),
            'transactionStatus' => $request->query('transaction_status'),
        ]);
    }

    public function renew(Request $request, Subscription $subscription): RedirectResponse
    {
        try {
            $payment = $this->paymentService->checkout($subscription, $subscription->owner());

            return redirect()->away($payment->checkout_url);
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses checkout renewal: '.$e->getMessage());
        }
    }

    public function cancel(Request $request, Subscription $subscription): RedirectResponse
    {
        try {
            $subscription = $this->subscriptionService->cancel($subscription, $subscription->owner());

            return redirect()->route('subscriptions.index')->with('success', 'Langganan berhasil dibatalkan, kamu masih bisa menikmati sampai langganan berakhir!');
        } catch (\Throwable $th) {
            return back()->with('error', 'Gagal membatalkan langganan: '.$th->getMessage());
        }
    }
}
