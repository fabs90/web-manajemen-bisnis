<?php

namespace App\Services\Subscription;

use App\Enum\PlanScope;
use App\Enum\SubscriptionStatus;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /** Membuat data subscription awal dengan status pending. Aktif hanya setelah payment paid. */
    public function subscribe(Plan $plan, User|Organization $owner, ?CarbonInterface $startAt = null, ?Subscription $replaces = null)
    {
        throw_unless($plan->is_active, DomainException::class, 'Plan tidak tersedia.');
        $ownerType = $owner instanceof Organization ? PlanScope::Organization : PlanScope::User;
        throw_unless($plan->scope === $ownerType, DomainException::class, 'Plan tidak cocok untuk scope ini.');

        // Cek pake kolom user_id atau organization_id
        $columnUser = $ownerType === PlanScope::User ? 'user_id' : 'organization_id';

        return DB::transaction(function () use ($plan, $owner, $columnUser, $startAt, $replaces) {
            // cek apakah user ini sudah berlangganan plan yang sama
            $existingPlan = Subscription::where($columnUser, $owner->id)->where('plan_id', $plan->id)->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Pending])->lockForUpdate()->get();

            throw_if($existingPlan->contains('status', SubscriptionStatus::Active), DomainException::class, 'Anda sudah berlangganan plan ini.');

            // cek apakah user ini punya subscription pending plan yang sama
            if ($existingPlan->firstWhere('status', SubscriptionStatus::Pending)) {
                return $existingPlan->firstWhere('status', SubscriptionStatus::Pending);
            }

            Subscription::create([
                $columnUser => $owner->id,
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::Pending,
                'starts_at' => $startAt, // default null
                'replace_subscription_id' => $replaces?->id // default null
            ]);
        });
    }

    public function activate(Subscription $subscription, User|Organization $owner): Subscription
    {
        $ownerType = $owner instanceof Organization ? PlanScope::Organization : PlanScope::User;
        $columnUser = $ownerType === PlanScope::User ? 'user_id' : 'organization_id';
        return DB::transaction(function () use ($subscription, $owner, $columnUser) {
            // cek apakah user ini sudah berlangganan plan yang sama
            $data = Subscription::with('plan')->where($columnUser, $owner->id)->lockForUpdate()->findOrFail($subscription->id);
            if ($data->status !== SubscriptionStatus::Pending) {
                return $data;
            }

            $start = $data->starts_at?->isFuture() ? $data->starts_at : now();
            $endsAt = $data->plan->duration_days ? $start->copy()->addDays($data->plan->duration_days) : null;
            $data->update([
                'status' => SubscriptionStatus::Active,
                'starts_at' => $start,
                'ends_at' => $endsAt
            ]);

            // cek apakah ada subscription yang di replace
            if ($data->replace_subscription_id) {
                // get data subscription yang di replace
                $old = Subscription::where('id', $data->replace_subscription_id)->first();
                if ($old) {
                    $this->expire($old);
                }
            }
            return $data;
        });
    }

    public function expire(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => SubscriptionStatus::Expired,
            'ends_at' => now()
        ]);
        return $subscription;
    }
}