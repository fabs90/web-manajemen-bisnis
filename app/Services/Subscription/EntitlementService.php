<?php

namespace App\Services\Subscription;

use App\Enum\BillingType;
use App\Enum\Feature;
use App\Enum\SubscriptionStatus;
use App\Models\Organization;
use App\Models\Subscription;
use App\Models\User;

class EntitlementService
{
    private array $memo = [];

    public function canAccess(User $user, Feature|string $feature, ?Organization $org = null): bool
    {
        $code = $feature instanceof Feature ? $feature->value : Feature::from($feature)->value;

        return in_array($code, $this->features($user, $org), true);
    }

    public function features(User $user, ?Organization $org = null): array
    {
        $orgId = $org?->id ?? $user->current_organization_id;

        return $this->memo["{$user->id}:{$orgId}"] ??= $this->query($user, $orgId);
    }

    private function query(User $user, ?int $orgId): array
    {
        $now = now();
        $graceCutoff = $now->copy()->subDays(config('subscription.grace_days'));

        return Subscription::query()
            ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
            ->join('plan_features', 'plan_features.plan_id', '=', 'plans.id')
            ->whereIn('subscriptions.status', [SubscriptionStatus::Active, SubscriptionStatus::Cancelled])
            ->where('subscriptions.starts_at', '<=', $now)
            ->where(fn ($q) => $q
                ->whereNull('subscriptions.ends_at')
                ->orWhere('subscriptions.ends_at', '>', $now)
                // grace period: hanya recurring untuk active
                ->orWhere(fn ($q) => $q->where('subscriptions.status', SubscriptionStatus::Active)
                    ->where('plans.billing_type', BillingType::Recurring)
                    ->where('subscriptions.ends_at', '>', $graceCutoff)))
            ->where(function ($q) use ($user, $orgId) {
                $q->where('subscriptions.user_id', $user->id);
                if ($orgId) {
                    $q->orWhere(fn ($q) => $q->where('subscriptions.organization_id', $orgId)
                        ->whereExists(fn ($s) => $s->from('organization_members')->selectRaw('1')
                            ->whereColumn('organization_members.organization_id', 'subscriptions.organization_id')
                            ->where('organization_members.user_id', $user->id)));
                }
            })
            ->distinct()->pluck('plan_features.feature')->all();
    }
}
