<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Interface\Payments\InvalidWebhook;
use App\Services\Payment\PaymentServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentHookController extends Controller
{
    public function __invoke(Request $request, string $provider, PaymentServices $payments)
    {
        Log::info("Webhook masuk dari provider: {$provider}", $request->all());
        try {
            $payments->handleWebhook($provider, $request);
        } catch (InvalidWebhook) {
            return response()->json(['message' => 'invalid'], 400);
        }

        return response()->json(['ok' => true]);
    }
}
