<?php

use App\Interface\Payments\MidtransGateway;

return [
    'default' => env('PAYMENT_GATEWAY', 'midtrans'),

    'gateways' => [
        'midtrans' => MidtransGateway::class,
    ],

];
