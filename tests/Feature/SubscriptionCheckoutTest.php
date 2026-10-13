<?php

use App\Enum\BillingType;
use App\Enum\PlanScope;
use App\Interface\Payments\CheckoutResult;
use App\Interface\Payments\PaymentGateway;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'alamat' => 'Jl. Test No. 1',
        'nomor_telepon' => '081234567890',
    ]);

    $this->plan = Plan::create([
        'code' => 'test-plan',
        'name' => 'Paket Uji Coba',
        'scope' => PlanScope::User,
        'billing_type' => BillingType::Recurring,
        'price' => 50000,
        'duration_days' => 30,
        'is_active' => true,
    ]);
});

it('renders the subscription index page with plans', function () {
    $response = $this->actingAs($this->user)->get(route('subscriptions.index'));

    $response->assertSuccessful();
    $response->assertSee('Uji Coba Langganan & Pembayaran');
    $response->assertSee('Paket Uji Coba');
});

it('redirects to gateway checkout url on checkout', function () {
    $fakeGateway = Mockery::mock(PaymentGateway::class);
    $fakeGateway->shouldReceive('createCheckout')
        ->once()
        ->andReturn(new CheckoutResult('https://app.sandbox.midtrans.com/snap/v2/vtweb/fake-snap-token'));

    $this->app->instance('mock.gateway', $fakeGateway);
    config(['payment.gateways.midtrans' => 'mock.gateway']);

    $response = $this->actingAs($this->user)->post(route('subscriptions.checkout', $this->plan));

    $response->assertRedirect('https://app.sandbox.midtrans.com/snap/v2/vtweb/fake-snap-token');
});

it('renders the subscription success page', function () {
    $response = $this->actingAs($this->user)->get(route('subscriptions.success', [
        'order_id' => 'PAY-TEST-123',
        'status_code' => '200',
        'transaction_status' => 'settlement',
    ]));

    $response->assertSuccessful();
    $response->assertSee('PAY-TEST-123');
    $response->assertSee('Terima Kasih!');
});
