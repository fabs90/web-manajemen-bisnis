<?php

namespace App\Services\Subscription;

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
    public function subscribe(Plan $plan, User|Organization $owner, ?CarbonInterface $startAt = null, ?Subscription $replaces = null): Subscription
    {
        throw_unless($plan->is_active, DomainException::class, 'Plan tidak tersedia.');
        throw_unless($plan->scope === $owner->planScope(), DomainException::class, 'Plan tidak cocok untuk scope ini.');

        return DB::transaction(function () use ($plan, $owner, $startAt, $replaces) {
            // cek apakah user ini sudah berlangganan plan yang sama
            $existingPlan = Subscription::forOwner($owner)
                ->where('plan_id', $plan->id)
                ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Pending])
                ->lockForUpdate()
                ->get();

            throw_if($existingPlan->contains('status', SubscriptionStatus::Active), DomainException::class, 'Anda sudah berlangganan plan ini.');

            // cek apakah user ini punya subscription pending plan yang sama
            if ($pending = $existingPlan->firstWhere('status', SubscriptionStatus::Pending)) {
                return $pending;
            }

            return Subscription::create([
                Subscription::ownerColumn($owner) => $owner->id,
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::Pending,
                'starts_at' => $startAt, // default null
                'replace_subscription_id' => $replaces?->id, // default null
            ]);
        });
    }

    public function activate(Subscription $subscription, User|Organization $owner): Subscription
    {
        return DB::transaction(function () use ($subscription, $owner) {
            // cek apakah user ini sudah berlangganan plan yang sama
            $data = Subscription::with('plan')
                ->forOwner($owner)
                ->lockForUpdate()
                ->findOrFail($subscription->id);

            if ($data->status !== SubscriptionStatus::Pending) {
                return $data;
            }

            $start = $data->starts_at?->isFuture() ? $data->starts_at : now();
            $endsAt = $data->plan->duration_days ? $start->copy()->addDays($data->plan->duration_days) : null;
            $data->update([
                'status' => SubscriptionStatus::Active,
                'starts_at' => $start,
                'ends_at' => $endsAt,
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
            'ends_at' => now(),
        ]);

        return $subscription;
    }

    public function renew(Subscription $subscription, User|Organization $owner): Subscription
    {
        return DB::transaction(function () use ($subscription, $owner) {
            // cek apakah user ini sudah berlangganan plan yang sama
            $data = Subscription::with('plan')
                ->forOwner($owner)
                ->lockForUpdate()
                ->findOrFail($subscription->id);

            if ($data->status !== SubscriptionStatus::Pending) {
                return $data;
            }

            // Ambil durasi plan
            $days = $data->plan->duration_days;
            throw_if(! $days, DomainException::class, 'Subscription plan permanent tidak perlu diperpanjang!');

            // cek apakah bayar lebih awal atau bayar telat.
            $start = $data->ends_at?->isFuture() ? $data->ends_at : now();
            $endsAt = $start->copy()->addDays($days);

            // Kalo bayar lebih awal, perpanjang expired_at
            if ($data->ends_at->isFuture()) {
                $data->update([
                    'status' => SubscriptionStatus::Active,
                    'ends_at' => $endsAt,
                ]);
            } else {
                // Kalo bayar telat, mulai nya dari tanggal dia bayar
                $data->update([
                    'status' => SubscriptionStatus::Active,
                    'starts_at' => $start,
                    'ends_at' => $endsAt,
                ]);
            }

            return $data;
        });
    }

    public function cancel(Subscription $subscription, User|Organization $owner): Subscription
    {
        return DB::transaction(function () use ($subscription, $owner) {
            $subscription = Subscription::with('plan')
                ->forOwner($owner)
                ->lockForUpdate()
                ->findOrFail($subscription->id);

            $subscription->update([
                'status' => SubscriptionStatus::Cancelled,
                'ends_at' => now(),
            ]);

            return $subscription;
        });
    }

    public function changePlan(Subscription $subscription, Plan $plan): Subscription
    {
        return DB::transaction(function () use ($subscription, $plan) {
            $owner = $subscription->owner();
            $this->cancel($subscription, $owner);

            return $this->subscribe($plan, $owner, replaces: $subscription);
        });
    }
}
