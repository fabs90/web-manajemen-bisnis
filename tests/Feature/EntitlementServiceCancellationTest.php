<?php

use App\Enum\BillingType;
use App\Enum\Feature;
use App\Enum\PlanScope;
use App\Enum\SubscriptionStatus;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\EntitlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows access to features if subscription is cancelled but ends_at has not passed', function () {
    $user = User::factory()->create([
        'alamat' => 'Jl. Test No. 1',
        'nomor_telepon' => '081234567890',
    ]);

    $plan = Plan::create([
        'code' => 'pro-plan',
        'name' => 'Pro Plan',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 50000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    PlanFeature::create([
        'plan_id' => $plan->id,
        'feature' => Feature::FinancialReport,
    ]);

    $subscription = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => SubscriptionStatus::Cancelled,
        'starts_at' => now()->subDays(5),
        'ends_at' => now()->addDays(25),
        'canceled_at' => now(),
    ]);

    $entitlement = app(EntitlementService::class);

    // User harus tetap bisa akses karena masa aktif belum lewat
    expect($entitlement->canAccess($user, Feature::FinancialReport))->toBeTrue();
    expect($user->hasFeature(Feature::FinancialReport))->toBeTrue();
});

it('denies access if cancelled subscription ends_at has already passed', function () {
    $user = User::factory()->create([
        'alamat' => 'Jl. Test No. 1',
        'nomor_telepon' => '081234567890',
    ]);

    $plan = Plan::create([
        'code' => 'pro-plan-2',
        'name' => 'Pro Plan 2',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 50000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    PlanFeature::create([
        'plan_id' => $plan->id,
        'feature' => Feature::FinancialReport,
    ]);

    $subscription = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => SubscriptionStatus::Cancelled,
        'starts_at' => now()->subDays(35),
        'ends_at' => now()->subDays(5), // sudah lewat
        'canceled_at' => now()->subDays(10),
    ]);

    $entitlement = app(EntitlementService::class);

    // User tidak boleh akses karena masa aktif sudah habis
    expect($entitlement->canAccess($user, Feature::FinancialReport))->toBeFalse();
    expect($user->hasFeature(Feature::FinancialReport))->toBeFalse();
});
