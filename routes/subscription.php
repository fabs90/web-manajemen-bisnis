<?php

use App\Http\Controllers\Payment\PaymentHookController;
use App\Http\Controllers\Subscription\SubscriptionCheckoutController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'ensureUserIsVerified', 'ensureProfileCompleted'])->group(function () {
    Route::get('dashboard/subscriptions', [SubscriptionCheckoutController::class, 'index'])
        ->name('subscriptions.index');

    Route::post('dashboard/subscriptions/checkout/{plan}', [SubscriptionCheckoutController::class, 'checkout'])
        ->name('subscriptions.checkout');

    Route::get('dashboard/subscriptions/success', [SubscriptionCheckoutController::class, 'success'])
        ->name('subscriptions.success');
});

Route::post('webhooks/payments/{provider}', PaymentHookController::class)
    ->middleware('throttle:120,1')
    ->name('webhooks.payments');
