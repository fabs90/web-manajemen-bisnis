<?php

use App\Enum\BillingType;
use App\Enum\PlanScope;
use App\Enum\SubscriptionStatus;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->organization = Organization::create([
        'name' => 'PT Testing Indonesia',
        'slug' => 'pt-testing-indonesia',
    ]);

    $this->userPlan = Plan::create([
        'code' => 'starter-user',
        'name' => 'Starter User',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 50000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $this->orgPlan = Plan::create([
        'code' => 'starter-org',
        'name' => 'Starter Org',
        'scope' => PlanScope::Organization,
        'billing_type' => BillingType::Recurring,
        'price' => 200000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $this->service = app(SubscriptionService::class);
});

it('resolves correct ownerColumn and scopeForOwner for User and Organization', function () {
    expect(Subscription::ownerColumn($this->user))->toBe('user_id')
        ->and(Subscription::ownerColumn($this->organization))->toBe('organization_id');

    $userSub = Subscription::create([
        'user_id' => $this->user->id,
        'plan_id' => $this->userPlan->id,
        'status' => SubscriptionStatus::Pending,
    ]);

    $orgSub = Subscription::create([
        'organization_id' => $this->organization->id,
        'plan_id' => $this->orgPlan->id,
        'status' => SubscriptionStatus::Pending,
    ]);

    expect(Subscription::forOwner($this->user)->pluck('id')->all())->toBe([$userSub->id])
        ->and(Subscription::forOwner($this->organization)->pluck('id')->all())->toBe([$orgSub->id]);
});

it('allows User and Organization to subscribe, activate, renew, and cancel', function () {
    // User subscription workflow
    $subUser = $this->service->subscribe($this->userPlan, $this->user);
    expect($subUser->status)->toBe(SubscriptionStatus::Pending)
        ->and($subUser->user_id)->toBe($this->user->id);

    $activatedUser = $this->service->activate($subUser, $this->user);
    expect($activatedUser->status)->toBe(SubscriptionStatus::Active)
        ->and($activatedUser->ends_at)->not->toBeNull();

    $renewedUser = $this->service->renew($activatedUser, $this->user);
    expect($renewedUser->status)->toBe(SubscriptionStatus::Active);

    $cancelledUser = $this->service->cancel($renewedUser, $this->user);
    expect($cancelledUser->status)->toBe(SubscriptionStatus::Cancelled);

    // Organization subscription workflow
    $subOrg = $this->service->subscribe($this->orgPlan, $this->organization);
    expect($subOrg->status)->toBe(SubscriptionStatus::Pending)
        ->and($subOrg->organization_id)->toBe($this->organization->id);

    $activatedOrg = $this->service->activate($subOrg, $this->organization);
    expect($activatedOrg->status)->toBe(SubscriptionStatus::Active)
        ->and($activatedOrg->ends_at)->not->toBeNull();

    $renewedOrg = $this->service->renew($activatedOrg, $this->organization);
    expect($renewedOrg->status)->toBe(SubscriptionStatus::Active);

    $cancelledOrg = $this->service->cancel($renewedOrg, $this->organization);
    expect($cancelledOrg->status)->toBe(SubscriptionStatus::Cancelled);
});

it('rejects subscribe when plan scope does not match owner', function () {
    expect(fn () => $this->service->subscribe($this->orgPlan, $this->user))
        ->toThrow(DomainException::class, 'Plan tidak cocok untuk scope ini.');

    expect(fn () => $this->service->subscribe($this->userPlan, $this->organization))
        ->toThrow(DomainException::class, 'Plan tidak cocok untuk scope ini.');
});

it('can change plan and replaces previous subscription', function () {
    $subUser = $this->service->subscribe($this->userPlan, $this->user);
    $activated = $this->service->activate($subUser, $this->user);

    $newUserPlan = Plan::create([
        'code' => 'pro-user',
        'name' => 'Pro User',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 100000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $newSub = $this->service->changePlan($activated, $newUserPlan);
    expect($newSub->status)->toBe(SubscriptionStatus::Pending)
        ->and($newSub->replace_subscription_id)->toBe($activated->id)
        ->and($activated->fresh()->status)->toBe(SubscriptionStatus::Cancelled);
});
