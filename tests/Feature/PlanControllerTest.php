<?php

use App\Enum\BillingType;
use App\Enum\Feature;
use App\Enum\PlanScope;
use App\Enum\SubscriptionStatus;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Subscription;
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
    $plan = Plan::create([
        'code' => 'GUEST_TEST',
        'name' => 'Guest Test',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 10000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $resolvedRoute = str_replace('{id}', (string) $plan->id, $route);

    $response = $this->{$method}($resolvedRoute, $data);

    $response->assertRedirect(route('login'));
})->with([
    'index' => ['get', '/superadmin/plan'],
    'create' => ['get', '/superadmin/plan/create'],
    'store' => ['post', '/superadmin/plan', []],
    'edit' => ['get', '/superadmin/plan/{id}/edit'],
    'update' => ['put', '/superadmin/plan/{id}', []],
]);

test('non-admin users are forbidden from accessing plan routes', function (string $method, string $route, array $data = []) {
    $plan = Plan::create([
        'code' => 'AUTH_TEST',
        'name' => 'Auth Test',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 10000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $resolvedRoute = str_replace('{id}', (string) $plan->id, $route);

    $response = $this->actingAs($this->regularUser)->{$method}($resolvedRoute, $data);

    $response->assertForbidden();
})->with([
    'index' => ['get', '/superadmin/plan'],
    'create' => ['get', '/superadmin/plan/create'],
    'store' => ['post', '/superadmin/plan', []],
    'edit' => ['get', '/superadmin/plan/{id}/edit'],
    'update' => ['put', '/superadmin/plan/{id}', []],
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

/*
|--------------------------------------------------------------------------
| Edit Action
|--------------------------------------------------------------------------
*/

test('superadmin can view edit plan page with prefilled data', function () {
    $plan = Plan::create([
        'code' => 'TEST_EDIT_PAGE',
        'name' => 'Paket Edit Test',
        'scope' => PlanScope::Organization,
        'billing_type' => BillingType::Recurring,
        'price' => 150000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    PlanFeature::create([
        'plan_id' => $plan->id,
        'feature' => Feature::FinancialReport,
    ]);

    $response = $this->actingAs($this->superadmin)
        ->get(route('superadmin.plan.edit', $plan));

    $response->assertSuccessful();
    $response->assertViewIs('superadmin.plan.edit');
    $response->assertViewHas('plan', fn ($viewPlan) => $viewPlan->id === $plan->id);
    $response->assertSee('TEST_EDIT_PAGE');
    $response->assertSee('Paket Edit Test');
    $response->assertSee('150000');
});

/*
|--------------------------------------------------------------------------
| Update Action
|--------------------------------------------------------------------------
*/

test('superadmin can successfully update plan attributes and features', function () {
    $plan = Plan::create([
        'code' => 'PLAN_ORIGINAL',
        'name' => 'Paket Lama',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 100000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    PlanFeature::create([
        'plan_id' => $plan->id,
        'feature' => Feature::FinancialReport,
    ]);

    $payload = [
        'plan_code' => 'PLAN_UPDATED',
        'plan_name' => 'Paket Baru Terupdate',
        'scope' => PlanScope::Organization->value,
        'billing_type' => BillingType::OneTime->value,
        'plan_price' => 300000,
        'plan_duration' => null,
        'features' => [Feature::EmailDispatch->value, Feature::FinancialReport->value],
    ];

    $response = $this->actingAs($this->superadmin)
        ->put(route('superadmin.plan.update', $plan), $payload);

    $response->assertRedirect(route('superadmin.plan.index'));
    $response->assertSessionHas('success', 'Paket berhasil diperbarui.');

    $this->assertDatabaseHas('plans', [
        'id' => $plan->id,
        'code' => 'PLAN_UPDATED',
        'name' => 'Paket Baru Terupdate',
        'scope' => PlanScope::Organization->value,
        'billing_type' => BillingType::OneTime->value,
        'price' => 300000,
        'duration_days' => null,
    ]);

    $this->assertDatabaseHas('plan_features', [
        'plan_id' => $plan->id,
        'feature' => Feature::EmailDispatch->value,
    ]);
    $this->assertDatabaseHas('plan_features', [
        'plan_id' => $plan->id,
        'feature' => Feature::FinancialReport->value,
    ]);
});

test('update plan allows keeping the same plan code', function () {
    $plan = Plan::create([
        'code' => 'SAME_CODE',
        'name' => 'Paket Nama Awal',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 100000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    PlanFeature::create([
        'plan_id' => $plan->id,
        'feature' => Feature::FinancialReport,
    ]);

    $payload = [
        'plan_code' => 'SAME_CODE',
        'plan_name' => 'Paket Nama Baru',
        'scope' => PlanScope::User->value,
        'billing_type' => BillingType::Recurring->value,
        'plan_price' => 125000,
        'plan_duration' => 60,
        'features' => [Feature::FinancialReport->value],
    ];

    $response = $this->actingAs($this->superadmin)
        ->put(route('superadmin.plan.update', $plan), $payload);

    $response->assertRedirect(route('superadmin.plan.index'));
    $response->assertSessionHas('success', 'Paket berhasil diperbarui.');

    $this->assertDatabaseHas('plans', [
        'id' => $plan->id,
        'code' => 'SAME_CODE',
        'name' => 'Paket Nama Baru',
        'price' => 125000,
        'duration_days' => 60,
    ]);
});

test('update plan fails when plan code is already taken by another plan', function () {
    Plan::create([
        'code' => 'ANOTHER_PLAN',
        'name' => 'Paket Lain',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 100000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $myPlan = Plan::create([
        'code' => 'MY_PLAN',
        'name' => 'Paket Saya',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 100000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $payload = [
        'plan_code' => 'ANOTHER_PLAN',
        'plan_name' => 'Coba Pakai Kode Lain',
        'scope' => PlanScope::User->value,
        'billing_type' => BillingType::Recurring->value,
        'plan_price' => 100000,
        'plan_duration' => 30,
        'features' => [Feature::FinancialReport->value],
    ];

    $response = $this->actingAs($this->superadmin)
        ->put(route('superadmin.plan.update', $myPlan), $payload);

    $response->assertSessionHasErrors('plan_code');
});

test('update plan redirects back with error and preserves old input when service throws exception', function () {
    $plan = Plan::create([
        'code' => 'FAIL_UPDATE',
        'name' => 'Fail Update Plan',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 100000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    mock(PlanService::class)
        ->shouldReceive('update')
        ->once()
        ->andThrow(new Exception('Database error during update'));

    $payload = [
        'plan_code' => 'FAIL_UPDATE',
        'plan_name' => 'Modified Name',
        'scope' => PlanScope::User->value,
        'billing_type' => BillingType::Recurring->value,
        'plan_price' => 100000,
        'plan_duration' => 30,
        'features' => [Feature::FinancialReport->value],
    ];

    $response = $this->actingAs($this->superadmin)
        ->from(route('superadmin.plan.edit', $plan))
        ->put(route('superadmin.plan.update', $plan), $payload);

    $response->assertRedirect(route('superadmin.plan.edit', $plan));
    $response->assertSessionHas('error', 'Gagal memperbarui paket. Silakan coba beberapa saat lagi.');
    $response->assertSessionHasInput('plan_name', 'Modified Name');
});

test('superadmin cannot change scope of a plan that is already used by subscriptions', function () {
    $plan = Plan::create([
        'code' => 'USED_PLAN_SCOPE',
        'name' => 'Used Plan Scope Test',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 150000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    PlanFeature::create([
        'plan_id' => $plan->id,
        'feature' => Feature::FinancialReport,
    ]);

    Subscription::create([
        'user_id' => $this->regularUser->id,
        'plan_id' => $plan->id,
        'status' => SubscriptionStatus::Active,
        'starts_at' => now(),
        'ends_at' => now()->addDays(30),
    ]);

    $payload = [
        'plan_code' => 'USED_PLAN_SCOPE',
        'plan_name' => 'Used Plan Changed Scope',
        'scope' => PlanScope::Organization->value, // Attempt to change scope
        'billing_type' => BillingType::Recurring->value,
        'plan_price' => 150000,
        'plan_duration' => 30,
        'features' => [Feature::FinancialReport->value],
    ];

    $response = $this->actingAs($this->superadmin)
        ->put(route('superadmin.plan.update', $plan), $payload);

    $response->assertSessionHasErrors('scope');

    // Verify DB was NOT updated
    expect($plan->fresh()->scope)->toBe(PlanScope::User);
});

test('superadmin cannot change billing_type of a plan that is already used by subscriptions', function () {
    $plan = Plan::create([
        'code' => 'USED_PLAN_BILLING',
        'name' => 'Used Plan Billing Test',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 150000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    PlanFeature::create([
        'plan_id' => $plan->id,
        'feature' => Feature::FinancialReport,
    ]);

    Subscription::create([
        'user_id' => $this->regularUser->id,
        'plan_id' => $plan->id,
        'status' => SubscriptionStatus::Active,
        'starts_at' => now(),
        'ends_at' => now()->addDays(30),
    ]);

    $payload = [
        'plan_code' => 'USED_PLAN_BILLING',
        'plan_name' => 'Used Plan Changed Billing',
        'scope' => PlanScope::User->value,
        'billing_type' => BillingType::OneTime->value, // Attempt to change billing_type
        'plan_price' => 150000,
        'plan_duration' => null,
        'features' => [Feature::FinancialReport->value],
    ];

    $response = $this->actingAs($this->superadmin)
        ->put(route('superadmin.plan.update', $plan), $payload);

    $response->assertSessionHasErrors('billing_type');

    // Verify DB was NOT updated
    expect($plan->fresh()->billing_type)->toBe(BillingType::Recurring);
});

test('superadmin can successfully update features, name, price, and deactivate a used plan', function () {
    $plan = Plan::create([
        'code' => 'USED_PLAN_FEATURE_EDIT',
        'name' => 'Paket Lama Aktif',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 100000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    PlanFeature::create([
        'plan_id' => $plan->id,
        'feature' => Feature::FinancialReport,
    ]);

    Subscription::create([
        'user_id' => $this->regularUser->id,
        'plan_id' => $plan->id,
        'status' => SubscriptionStatus::Active,
        'starts_at' => now(),
        'ends_at' => now()->addDays(30),
    ]);

    $payload = [
        'plan_code' => 'USED_PLAN_FEATURE_EDIT',
        'plan_name' => 'Paket Nama Baru',
        'scope' => PlanScope::User->value,
        'billing_type' => BillingType::Recurring->value,
        'plan_price' => 200000,
        'plan_duration' => 30,
        'is_active' => false,
        'features' => [Feature::ThermalPrinter->value, Feature::AdministrationDocuments->value],
    ];

    $response = $this->actingAs($this->superadmin)
        ->put(route('superadmin.plan.update', $plan), $payload);

    $response->assertRedirect(route('superadmin.plan.index'));
    $response->assertSessionHas('success', 'Paket berhasil diperbarui.');

    $freshPlan = $plan->fresh();
    expect($freshPlan->name)->toBe('Paket Nama Baru')
        ->and($freshPlan->price)->toBe(200000)
        ->and($freshPlan->is_active)->toBeFalse()
        ->and($freshPlan->scope)->toBe(PlanScope::User)
        ->and($freshPlan->billing_type)->toBe(BillingType::Recurring);

    // Verify features were updated
    expect($freshPlan->features()->map(fn ($f) => $f->value)->toArray())
        ->toEqualCanonicalizing([
            Feature::ThermalPrinter->value,
            Feature::AdministrationDocuments->value,
        ]);
});

test('edit view displays locked indicators when plan is used and normal when unused', function () {
    $unusedPlan = Plan::create([
        'code' => 'UNUSED_PLAN',
        'name' => 'Unused Plan',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 100000,
        'duration_days' => 30,
        'is_active' => true,
    ]);
    PlanFeature::create([
        'plan_id' => $unusedPlan->id,
        'feature' => Feature::FinancialReport,
    ]);

    $usedPlan = Plan::create([
        'code' => 'USED_PLAN_VIEW',
        'name' => 'Used Plan View',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 100000,
        'duration_days' => 30,
        'is_active' => true,
    ]);
    PlanFeature::create([
        'plan_id' => $usedPlan->id,
        'feature' => Feature::FinancialReport,
    ]);
    Subscription::create([
        'user_id' => $this->regularUser->id,
        'plan_id' => $usedPlan->id,
        'status' => SubscriptionStatus::Active,
        'starts_at' => now(),
        'ends_at' => now()->addDays(30),
    ]);

    // View unused plan
    $responseUnused = $this->actingAs($this->superadmin)->get(route('superadmin.plan.edit', $unusedPlan));
    $responseUnused->assertSuccessful();
    $responseUnused->assertDontSee('Terkunci (Sudah Dipakai)');

    // View used plan
    $responseUsed = $this->actingAs($this->superadmin)->get(route('superadmin.plan.edit', $usedPlan));
    $responseUsed->assertSuccessful();
    $responseUsed->assertSee('Terkunci (Sudah Dipakai)');
    $responseUsed->assertSee('Keputusan Bisnis & Akses Pelanggan:');
});
