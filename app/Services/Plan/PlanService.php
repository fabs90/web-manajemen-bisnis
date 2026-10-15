<?php

namespace App\Services\Plan;

use App\Enum\BillingType;
use App\Enum\PlanScope;
use App\Models\Plan;
use DomainException;
use Illuminate\Support\Facades\DB;

class PlanService
{
    public function store(array $data): Plan
    {
        return DB::transaction(function () use ($data) {
            $plan = Plan::create([
                'code' => $data['plan_code'],
                'name' => $data['plan_name'],
                'price' => $data['plan_price'],
                'scope' => $data['scope'],
                'billing_type' => $data['billing_type'],
                'duration_days' => $data['plan_duration'],
                'is_active' => true,
            ]);

            // format flat array ke assoc
            $features = array_map(
                fn ($item) => ['feature' => $item],
                $data['features']
            );

            if (isset($data['features'])) {
                $plan->planFeatures()->createMany($features);
            }

            return $plan;
        });

    }

    public function update(Plan $plan, array $data): Plan
    {
        return DB::transaction(function () use ($plan, $data) {
            $plan = Plan::lockForUpdate()->findOrFail($plan->id);
            $locked = $plan->isUsed();

            $scope = $locked ? $plan->scope : PlanScope::from($data['scope'] ?? $plan->scope->value);
            $billing = $locked ? $plan->billing_type : BillingType::from($data['billing_type'] ?? $plan->billing_type->value);

            $plan->update([
                'code' => $data['plan_code'] ?? $plan->code,
                'name' => $data['plan_name'] ?? $plan->name,
                'price' => $data['plan_price'] ?? $plan->price,
                'scope' => $scope,
                'billing_type' => $billing,
                'duration_days' => $billing === BillingType::OneTime
                    ? null
                    : $data['plan_duration'] ?? $plan->duration_days,
                'is_active' => array_key_exists('is_active', $data)
                    ? (bool) $data['is_active']
                    : $plan->is_active,
            ]);

            if (isset($data['features'])) {
                $plan->planFeatures()->delete();
                $plan->planFeatures()->createMany(
                    array_map(fn ($f) => ['feature' => $f], array_values(array_unique($data['features'])))
                );
            }

            return $plan;
        });
    }

    public function delete(Plan $plan): void
    {
        DB::transaction(function () use ($plan) {
            $plan = Plan::lockForUpdate()->findOrFail($plan->id);

            throw_if(
                $plan->isUsed(),
                DomainException::class,
                'Paket ini sudah memiliki pelanggan dan tidak dapat dihapus. Nonaktifkan saja.'
            );

            $plan->delete();
        });
    }
}
