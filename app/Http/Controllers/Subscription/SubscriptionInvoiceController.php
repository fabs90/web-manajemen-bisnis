<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Barryvdh\DomPDF\Facade\Pdf;

class SubscriptionInvoiceController extends Controller
{
    public function invoice(Subscription $subscription)
    {
        $user = auth()->user();
        $subscription = Subscription::with(['plan', 'payments', 'organization', 'user'])
            ->forOwner($user)
            ->findOrFail($subscription->id);

        $fileName = 'invoice-'.($subscription->payments->sortByDesc('id')->first()?->reference ?? $subscription->id).'.pdf';

        $pdf = Pdf::loadView('subscription.invoice-pdf', [
            'subscription' => $subscription,
            // Latest payment transaction
            'payment' => $subscription->payments->sortByDesc('id')->first(),
            'user' => $user,
        ]);

        return $pdf->stream($fileName);
    }
}
