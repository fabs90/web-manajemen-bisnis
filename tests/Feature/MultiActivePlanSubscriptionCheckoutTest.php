<?php

use App\Enum\BillingType;
use App\Enum\PlanScope;
use App\Enum\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('marks multiple active plans as active on the subscription index page', function () {
    $user = User::factory()->create([
        'alamat' => 'Jl. Test No. 1',
        'nomor_telepon' => '081234567890',
    ]);

    $planA = Plan::create([
        'code' => 'plan-a',
        'name' => 'Paket Alpha',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 50000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $planB = Plan::create([
        'code' => 'plan-b',
        'name' => 'Paket Beta',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 75000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $planC = Plan::create([
        'code' => 'plan-c',
        'name' => 'Paket Gamma',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 100000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    // Aktifkan plan A dan plan B untuk user
    Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $planA->id,
        'status' => SubscriptionStatus::Active,
        'starts_at' => now()->subDays(5),
        'ends_at' => now()->addDays(25),
    ]);

    Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $planB->id,
        'status' => SubscriptionStatus::Active,
        'starts_at' => now()->subDays(2),
        'ends_at' => now()->addDays(28),
    ]);

    $response = $this->actingAs($user)->get(route('subscriptions.index'));

    $response->assertSuccessful();

    // Pastikan badge "PAKET AKTIF ANDA" muncul sebanyak 2 kali (karena 2 plan aktif)
    $content = $response->getContent();
    expect(substr_count($content, 'PAKET AKTIF ANDA'))->toBe(2);
    // 2 definisi di tag <style> + 2 di HTML class kartu
    expect(substr_count($content, 'current-active-plan'))->toBe(4);
});
