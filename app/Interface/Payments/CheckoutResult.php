<?php

namespace App\Interface\Payments;

final readonly class CheckoutResult
{
    public string $url;

    public function __construct(string $url)
    {
        // otomatis return url
        $this->url = $url;
    }
}
