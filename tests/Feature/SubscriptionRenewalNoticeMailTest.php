<?php

use App\Enum\BillingType;
use App\Enum\PlanScope;
use App\Enum\SubscriptionStatus;
use App\Mail\SubscriptionRenewalNoticeMail;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('renders subscription renewal notice email for user subscription', function () {
    $user = User::factory()->create([
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
    ]);

    $plan = Plan::create([
        'code' => 'pro-monthly',
        'name' => 'Paket Pro Bulanan',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 150000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $subscription = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => SubscriptionStatus::Active,
        'starts_at' => now()->subDays(25),
        'ends_at' => now()->addDays(5),
    ]);

    $mailable = new SubscriptionRenewalNoticeMail($subscription);

    $mailable->assertHasSubject('Pemberitahuan Perpanjangan Langganan: Paket Pro Bulanan');
    $mailable->assertSeeInHtml('Budi Santoso');
    $mailable->assertSeeInHtml('Paket Pro Bulanan');
    $mailable->assertSeeInHtml('pro-monthly');
    $mailable->assertSeeInHtml('Rp 150.000');
    $mailable->assertSeeInHtml('30 Hari');
    $mailable->assertSeeInHtml('5 Hari Lagi');
    $mailable->assertSeeInHtml(route('subscriptions.index'));
});

it('renders subscription renewal notice email for organization subscription', function () {
    $user = User::factory()->create([
        'name' => 'Siti Aminah',
        'email' => 'siti@example.com',
    ]);

    $organization = Organization::create([
        'name' => 'CV Maju Bersama',
        'slug' => 'cv-maju-bersama',
    ]);
    $organization->members()->attach($user->id, ['role' => 'owner']);

    $plan = Plan::create([
        'code' => 'org-enterprise',
        'name' => 'Paket Enterprise Organisasi',
        'scope' => PlanScope::Organization,
        'billing_type' => BillingType::Recurring,
        'price' => 500000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $subscription = Subscription::create([
        'organization_id' => $organization->id,
        'plan_id' => $plan->id,
        'status' => SubscriptionStatus::Active,
        'starts_at' => now()->subDays(27),
        'ends_at' => now()->addDays(3),
    ]);

    $mailable = new SubscriptionRenewalNoticeMail($subscription);

    $mailable->assertHasSubject('Pemberitahuan Perpanjangan Langganan: Paket Enterprise Organisasi');
    $mailable->assertSeeInHtml('Siti Aminah');
    $mailable->assertSeeInHtml('CV Maju Bersama');
    $mailable->assertSeeInHtml('Paket Enterprise Organisasi');
    $mailable->assertSeeInHtml('Rp 500.000');
    $mailable->assertSeeInHtml('3 Hari Lagi');
});

it('can be sent via Mail facade', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'pelanggan@example.com',
    ]);

    $plan = Plan::create([
        'code' => 'starter-user',
        'name' => 'Starter User',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 50000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $subscription = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => SubscriptionStatus::Active,
        'starts_at' => now()->subDays(23),
        'ends_at' => now()->addDays(7),
    ]);

    Mail::to($user->email)->send(new SubscriptionRenewalNoticeMail($subscription, $user));

    Mail::assertQueued(SubscriptionRenewalNoticeMail::class, function (SubscriptionRenewalNoticeMail $mail) use ($user, $subscription) {
        return $mail->hasTo($user->email)
            && $mail->subscription->is($subscription);
    });
});

it('sends renewal notice emails via artisan command for user and organization subscriptions', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'user-owner@example.com',
    ]);

    $orgMember = User::factory()->create([
        'name' => 'Regular Member',
        'email' => 'member@example.com',
    ]);

    $orgOwner = User::factory()->create([
        'name' => 'Org Owner',
        'email' => 'org-owner@example.com',
    ]);

    $organization = Organization::create([
        'name' => 'PT Sukses',
        'slug' => 'pt-sukses',
    ]);
    $organization->members()->attach($orgMember->id, ['role' => 'member']);
    $organization->members()->attach($orgOwner->id, ['role' => 'owner']);

    $userPlan = Plan::create([
        'code' => 'user-plan',
        'name' => 'User Plan',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 100000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $orgPlan = Plan::create([
        'code' => 'org-plan',
        'name' => 'Org Plan',
        'scope' => PlanScope::Organization,
        'billing_type' => BillingType::Recurring,
        'price' => 300000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $userSub = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $userPlan->id,
        'status' => SubscriptionStatus::Active,
        'starts_at' => now()->subDays(25),
        'ends_at' => now()->addDays(5),
    ]);

    $orgSub = Subscription::create([
        'organization_id' => $organization->id,
        'plan_id' => $orgPlan->id,
        'status' => SubscriptionStatus::Active,
        'starts_at' => now()->subDays(27),
        'ends_at' => now()->addDays(3),
    ]);

    $this->artisan('subscription:send-subscription-renewal-notice')
        ->assertSuccessful();

    Mail::assertQueued(SubscriptionRenewalNoticeMail::class, function (SubscriptionRenewalNoticeMail $mail) use ($user, $userSub) {
        return $mail->hasTo($user->email) && $mail->subscription->is($userSub);
    });

    Mail::assertQueued(SubscriptionRenewalNoticeMail::class, function (SubscriptionRenewalNoticeMail $mail) use ($orgOwner, $orgSub) {
        return $mail->hasTo($orgOwner->email)
            && $mail->subscription->is($orgSub)
            && $mail->user?->is($orgOwner);
    });
});
