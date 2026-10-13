<?php

namespace Database\Seeders;

use App\Enum\BillingType;
use App\Enum\PlanScope;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::updateOrCreate(
            ['code' => 'starter-user'],
            [
                'name' => 'Starter (Bulanan)',
                'scope' => PlanScope::User,
                'billing_type' => BillingType::Recurring,
                'price' => 50000,
                'duration_days' => 30,
                'is_active' => true,
            ]
        );

        Plan::updateOrCreate(
            ['code' => 'pro-user'],
            [
                'name' => 'Pro (Tahunan)',
                'scope' => PlanScope::User,
                'billing_type' => BillingType::Recurring,
                'price' => 500000,
                'duration_days' => 365,
                'is_active' => true,
            ]
        );

        Plan::updateOrCreate(
            ['code' => 'lifetime-user'],
            [
                'name' => 'Lifetime (Permanen)',
                'scope' => PlanScope::User,
                'billing_type' => BillingType::OneTime,
                'price' => 1500000,
                'duration_days' => null,
                'is_active' => true,
            ]
        );
    }
}
