<?php

use App\Enum\BillingType;
use App\Enum\Feature;
use App\Enum\PlanScope;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'email' => 'superadmin_test@example.com',
        'role' => 'superadmin',
        'is_verified' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'email' => 'regular_user_test@example.com',
        'role' => 'ukm',
        'is_verified' => true,
    ]);

    $this->plan = Plan::create([
        'code' => 'pro-user',
        'name' => 'Pro (Tahunan)',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 500000,
        'duration_days' => 365,
        'is_active' => true,
    ]);

    PlanFeature::create([
        'plan_id' => $this->plan->id,
        'feature' => Feature::FinancialReport,
    ]);
});

it('renders the superadmin plan index page with stats, table, and features', function () {
    $response = $this->actingAs($this->superadmin)->get(route('superadmin.plan.index'));

    $response->assertSuccessful();
    $response->assertSee('Kelola Paket Langganan');
    $response->assertSee('Pro (Tahunan)');
    $response->assertSee('pro-user');
    $response->assertSee('Rp 500.000');
    $response->assertSee('Financial Report');
});

it('blocks non-admin users from accessing the superadmin plan index', function () {
    $response = $this->actingAs($this->regularUser)->get(route('superadmin.plan.index'));

    $response->assertForbidden();
});
