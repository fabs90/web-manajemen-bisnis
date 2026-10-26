<?php

namespace App\Models;

use App\Enum\BillingType;
use App\Enum\PlanScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Plan extends Model
{
    protected $fillable = [
        'code',
        'name',
        'scope',
        'billing_type',
        'price',
        'duration_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'scope' => PlanScope::class,
            'billing_type' => BillingType::class,
            'is_active' => 'boolean',
        ];
    }

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function features(): Collection
    {
        return $this->planFeatures->pluck('feature');
    }

    public function scopeWithSubscriptionsCount(Builder $query): Builder
    {
        return $query->withCount('subscriptions');
    }

    public function countSubscriptions(): int
    {
        return $this->subscriptions_count = $this->subscriptions_count ?? $this->subscriptions()->count();
    }

    public function isUsed(): bool
    {
        return $this->countSubscriptions() > 0;
    }

    public function revenueEstimated(): float
    {
        return $this->price * $this->countSubscriptions();
    }
}
