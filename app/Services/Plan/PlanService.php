<?php

namespace App\Services\Plan;

use App\Models\Plan;
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

            $plan->planFeatures()->createMany($features);

            return $plan;
        });

    }
}
