<?php

use App\Enum\BillingType;
use App\Enum\Feature;
use App\Enum\PlanScope;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\User;
use App\Services\Plan\PlanService;

use function Pest\Laravel\mock;

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'email' => 'superadmin@example.com',
        'role' => 'superadmin',
        'is_verified' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'email' => 'user@example.com',
        'role' => 'ukm',
        'is_verified' => true,
    ]);
});

/*
|--------------------------------------------------------------------------
| Authorization & Access Control
|--------------------------------------------------------------------------
*/

test('guests are redirected to login when accessing plan routes', function (string $method, string $route, array $data = []) {
    $response = $this->{$method}($route, $data);

    $response->assertRedirect(route('login'));
})->with([
    'index' => ['get', '/superadmin/plan'],
    'create' => ['get', '/superadmin/plan/create'],
    'store' => ['post', '/superadmin/plan', []],
]);

test('non-admin users are forbidden from accessing plan routes', function (string $method, string $route, array $data = []) {
    $response = $this->actingAs($this->regularUser)->{$method}($route, $data);

    $response->assertForbidden();
})->with([
    'index' => ['get', '/superadmin/plan'],
    'create' => ['get', '/superadmin/plan/create'],
    'store' => ['post', '/superadmin/plan', []],
]);

/*
|--------------------------------------------------------------------------
| Index Action
|--------------------------------------------------------------------------
*/

test('superadmin can view plan index with existing plans and features', function () {
    $plan = Plan::create([
        'code' => 'PRO_YEARLY',
        'name' => 'Pro Tahunan',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 750000,
        'duration_days' => 365,
        'is_active' => true,
    ]);

    PlanFeature::create([
        'plan_id' => $plan->id,
        'feature' => Feature::FinancialReport,
    ]);

    PlanFeature::create([
        'plan_id' => $plan->id,
        'feature' => Feature::ThermalPrinter,
    ]);

    $response = $this->actingAs($this->superadmin)->get(route('superadmin.plan.index'));

    $response->assertSuccessful();
    $response->assertViewIs('superadmin.plan.index');
    $response->assertViewHas('plan');
    $response->assertSee('Pro Tahunan');
    $response->assertSee('PRO_YEARLY');
    $response->assertSee('Rp 750.000');
    $response->assertSee(Feature::FinancialReport->label());
    $response->assertSee(Feature::ThermalPrinter->label());
});

test('superadmin can view plan index when no plans exist', function () {
    $response = $this->actingAs($this->superadmin)->get(route('superadmin.plan.index'));

    $response->assertSuccessful();
    $response->assertViewIs('superadmin.plan.index');
    $response->assertViewHas('plan', fn ($plans) => $plans->isEmpty());
});

/*
|--------------------------------------------------------------------------
| Create Action
|--------------------------------------------------------------------------
*/

test('superadmin can view create plan page', function () {
    $response = $this->actingAs($this->superadmin)->get(route('superadmin.plan.create'));

    $response->assertSuccessful();
    $response->assertViewIs('superadmin.plan.create');
    $response->assertSee('Buat Paket Langganan');
});

/*
|--------------------------------------------------------------------------
| Store Action - Happy Paths
|--------------------------------------------------------------------------
*/

test('superadmin can successfully create a recurring user plan', function () {
    $payload = [
        'plan_code' => 'STARTER_USER',
        'plan_name' => 'Starter User Package',
        'scope' => PlanScope::User->value,
        'billing_type' => BillingType::Recurring->value,
        'plan_price' => 150000,
        'plan_duration' => 30,
        'features' => [
            Feature::FinancialReport->value,
            Feature::MeetingManagement->value,
        ],
    ];

    $response = $this->actingAs($this->superadmin)->post(route('superadmin.plan.store'), $payload);

    $response->assertRedirect(route('superadmin.plan.index'));
    $response->assertSessionHas('success', 'Paket berhasil dibuat.');

    $this->assertDatabaseHas('plans', [
        'code' => 'STARTER_USER',
        'name' => 'Starter User Package',
        'scope' => PlanScope::User->value,
        'billing_type' => BillingType::Recurring->value,
        'price' => 150000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $createdPlan = Plan::where('code', 'STARTER_USER')->first();

    $this->assertDatabaseHas('plan_features', [
        'plan_id' => $createdPlan->id,
        'feature' => Feature::FinancialReport->value,
    ]);

    $this->assertDatabaseHas('plan_features', [
        'plan_id' => $createdPlan->id,
        'feature' => Feature::MeetingManagement->value,
    ]);
});

test('superadmin can successfully create an organization lifetime plan', function () {
    $payload = [
        'plan_code' => 'LIFETIME_ORG',
        'plan_name' => 'Lifetime Organisasi',
        'scope' => PlanScope::Organization->value,
        'billing_type' => BillingType::OneTime->value,
        'plan_price' => 2500000,
        'plan_duration' => 999, // Should be normalized to null by prepareForValidation
        'features' => [
            Feature::FinancialReport->value,
            Feature::ThermalPrinter->value,
            Feature::AdministrationDocuments->value,
        ],
    ];

    $response = $this->actingAs($this->superadmin)->post(route('superadmin.plan.store'), $payload);

    $response->assertRedirect(route('superadmin.plan.index'));
    $response->assertSessionHas('success', 'Paket berhasil dibuat.');

    $this->assertDatabaseHas('plans', [
        'code' => 'LIFETIME_ORG',
        'name' => 'Lifetime Organisasi',
        'scope' => PlanScope::Organization->value,
        'billing_type' => BillingType::OneTime->value,
        'price' => 2500000,
        'duration_days' => null,
        'is_active' => true,
    ]);
});

test('store plan normalizes lowercase code and formatted price string', function () {
    $payload = [
        'plan_code' => 'lowercase-code',
        'plan_name' => 'Formatted Input Plan',
        'scope' => PlanScope::User->value,
        'billing_type' => BillingType::Recurring->value,
        'plan_price' => 'Rp 250.000',
        'plan_duration' => 60,
        'features' => [Feature::EmailDispatch->value],
    ];

    $response = $this->actingAs($this->superadmin)->post(route('superadmin.plan.store'), $payload);

    $response->assertRedirect(route('superadmin.plan.index'));

    $this->assertDatabaseHas('plans', [
        'code' => 'LOWERCASE-CODE',
        'price' => 250000,
    ]);
});

/*
|--------------------------------------------------------------------------
| Store Action - Validation & Error Paths
|--------------------------------------------------------------------------
*/

test('store plan validates required fields', function () {
    $response = $this->actingAs($this->superadmin)->post(route('superadmin.plan.store'), []);

    $response->assertSessionHasErrors([
        'plan_code',
        'plan_name',
        'scope',
        'billing_type',
        'plan_price',
        'features',
    ]);
});

test('store plan validates uniqueness of plan code', function () {
    Plan::create([
        'code' => 'EXISTING_PLAN',
        'name' => 'Paket Lama',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 100000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $payload = [
        'plan_code' => 'existing_plan', // Will be converted to uppercase EXISTING_PLAN
        'plan_name' => 'Paket Baru',
        'scope' => PlanScope::User->value,
        'billing_type' => BillingType::Recurring->value,
        'plan_price' => 200000,
        'plan_duration' => 30,
        'features' => [Feature::FinancialReport->value],
    ];

    $response = $this->actingAs($this->superadmin)->post(route('superadmin.plan.store'), $payload);

    $response->assertSessionHasErrors('plan_code');
});

test('store plan validates regex pattern for plan code', function () {
    $payload = [
        'plan_code' => 'INVALID CODE!',
        'plan_name' => 'Paket Invalid',
        'scope' => PlanScope::User->value,
        'billing_type' => BillingType::Recurring->value,
        'plan_price' => 100000,
        'plan_duration' => 30,
        'features' => [Feature::FinancialReport->value],
    ];

    $response = $this->actingAs($this->superadmin)->post(route('superadmin.plan.store'), $payload);

    $response->assertSessionHasErrors('plan_code');
});

test('store plan validates minimum price', function () {
    $payload = [
        'plan_code' => 'VALID_CODE',
        'plan_name' => 'Paket Min',
        'scope' => PlanScope::User->value,
        'billing_type' => BillingType::Recurring->value,
        'plan_price' => -5000,
        'plan_duration' => 30,
        'features' => [Feature::FinancialReport->value],
    ];

    $response = $this->actingAs($this->superadmin)->post(route('superadmin.plan.store'), $payload);

    $response->assertSessionHasErrors('plan_price');
});

test('store plan requires duration when billing type is recurring', function () {
    $payload = [
        'plan_code' => 'NO_DURATION',
        'plan_name' => 'Tanpa Durasi',
        'scope' => PlanScope::User->value,
        'billing_type' => BillingType::Recurring->value,
        'plan_price' => 100000,
        'plan_duration' => null,
        'features' => [Feature::FinancialReport->value],
    ];

    $response = $this->actingAs($this->superadmin)->post(route('superadmin.plan.store'), $payload);

    $response->assertSessionHasErrors('plan_duration');
});

test('store plan validates duration minimum value of 1', function () {
    $payload = [
        'plan_code' => 'ZERO_DURATION',
        'plan_name' => 'Durasi Nol',
        'scope' => PlanScope::User->value,
        'billing_type' => BillingType::Recurring->value,
        'plan_price' => 100000,
        'plan_duration' => 0,
        'features' => [Feature::FinancialReport->value],
    ];

    $response = $this->actingAs($this->superadmin)->post(route('superadmin.plan.store'), $payload);

    $response->assertSessionHasErrors('plan_duration');
});

test('store plan validates enum values for scope and billing type', function () {
    $payload = [
        'plan_code' => 'ENUM_TEST',
        'plan_name' => 'Enum Test',
        'scope' => 'invalid_scope',
        'billing_type' => 'invalid_billing',
        'plan_price' => 100000,
        'plan_duration' => 30,
        'features' => [Feature::FinancialReport->value],
    ];

    $response = $this->actingAs($this->superadmin)->post(route('superadmin.plan.store'), $payload);

    $response->assertSessionHasErrors(['scope', 'billing_type']);
});

test('store plan validates features array must not be empty and must have valid enum values', function () {
    $payloadEmpty = [
        'plan_code' => 'NO_FEATURES',
        'plan_name' => 'Tanpa Fitur',
        'scope' => PlanScope::User->value,
        'billing_type' => BillingType::Recurring->value,
        'plan_price' => 100000,
        'plan_duration' => 30,
        'features' => [],
    ];

    $responseEmpty = $this->actingAs($this->superadmin)->post(route('superadmin.plan.store'), $payloadEmpty);
    $responseEmpty->assertSessionHasErrors('features');

    $payloadInvalid = [
        'plan_code' => 'BAD_FEATURE',
        'plan_name' => 'Fitur Salah',
        'scope' => PlanScope::User->value,
        'billing_type' => BillingType::Recurring->value,
        'plan_price' => 100000,
        'plan_duration' => 30,
        'features' => ['non_existent_feature'],
    ];

    $responseInvalid = $this->actingAs($this->superadmin)->post(route('superadmin.plan.store'), $payloadInvalid);
    $responseInvalid->assertSessionHasErrors('features.0');
});

/*
|--------------------------------------------------------------------------
| Store Action - Exception / Service Failure Handling
|--------------------------------------------------------------------------
*/

test('store plan redirects back with error and preserves old input when service throws exception', function () {
    mock(PlanService::class)
        ->shouldReceive('store')
        ->once()
        ->andThrow(new Exception('Database connection failed'));

    $payload = [
        'plan_code' => 'FAIL_TEST',
        'plan_name' => 'Fail Test Plan',
        'scope' => PlanScope::User->value,
        'billing_type' => BillingType::Recurring->value,
        'plan_price' => 100000,
        'plan_duration' => 30,
        'features' => [Feature::FinancialReport->value],
    ];

    $response = $this->actingAs($this->superadmin)
        ->from(route('superadmin.plan.create'))
        ->post(route('superadmin.plan.store'), $payload);

    $response->assertRedirect(route('superadmin.plan.create'));
    $response->assertSessionHas('error', 'Gagal membuat paket. Silakan coba beberapa saat lagi.');
    $response->assertSessionHasInput('plan_code', 'FAIL_TEST');
    $response->assertSessionHasInput('plan_name', 'Fail Test Plan');
});
