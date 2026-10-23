<?php

namespace App\Models;

use App\Enum\BillingType;
use App\Enum\SubscriptionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'organization_id',
        'plan_id',
        'replace_subscription_id',
        'status',
        'starts_at',
        'ends_at',
        'canceled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function owner(): User|Organization
    {
        return $this->user ?? $this->organization;
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Untuk scheduler: yang sudah lewat masa aktif (+ grace khusus recurring)
    public function scopeLapsed(Builder $q): Builder
    {
        $cutoff = now()->subDays(config('subscription.grace_days'));

        return $q->where('status', SubscriptionStatus::Active)->whereNotNull('ends_at')
            ->where(fn($q) => $q
                ->where(fn($q) => $q->where('ends_at', '<', now())
                    ->whereHas('plan', fn($p) => $p->where('billing_type', BillingType::OneTime)))
                ->orWhere(fn($q) => $q->where('ends_at', '<', $cutoff)
                    ->whereHas('plan', fn($p) => $p->where('billing_type', BillingType::Recurring))));
    }

    public function scopeRenewalNotice(Builder $q): Builder
    {
        $notice = config('subscription.renewal_notice_days');

        return $q->where('status', SubscriptionStatus::Active)
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [now(), now()->addDays($notice)]);
    }

    public static function ownerColumn(User|Organization $owner): string
    {
        return $owner instanceof Organization ? 'organization_id' : 'user_id';
    }

    public function scopeForOwner(Builder $query, User|Organization $owner): Builder
    {
        return $query->where(static::ownerColumn($owner), $owner->id);
    }

    public function receipentEmail(): ?string
    {
        return $this->owner()?->email;
    }
}