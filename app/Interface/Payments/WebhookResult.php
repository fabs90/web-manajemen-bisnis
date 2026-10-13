<?php

namespace App\Interface\Payments;

use App\Enum\PaymentStatus;

final readonly class WebhookResult
{
    public function __construct(
        public string $reference,          // order_id yang kita kirim saat checkout
        public PaymentStatus $status,      // sudah dinormalisasi ke enum PaymentStatus
        public ?string $transactionId,
        public int $amount,                // rupiah, integer
        public array $payload,             // mentah, untuk disimpan sebagai jejak
    ) {}
}
