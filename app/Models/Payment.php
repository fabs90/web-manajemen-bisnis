<?php

namespace App\Models;

use App\Enum\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $guarded = [];
    protected function casts(): array
    {
        return ['status' => PaymentStatus::class, 'paid_at' => 'datetime', 'payload' => 'array'];
    }
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
