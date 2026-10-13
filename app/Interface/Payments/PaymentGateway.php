<?php

namespace App\Interface\Payments;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;

interface PaymentGateway
{
    public function createCheckout(Payment $payment, User $user): CheckoutResult;

    public function parseWebhook(Request $request): WebhookResult;
}
