<?php

use App\Mail\MailSend;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

test('account verification screen can be rendered', function () {
    $user = User::factory()->create([
        'is_verified' => false,
        'otp' => hash('sha256', '123456'),
        'otp_expires_at' => Carbon::now('Asia/Makassar')->addMinutes(30),
    ]);

    $response = $this->actingAs($user)->get(route('account-verification.show'));

    $response->assertStatus(200);
});

test('account can be verified with valid OTP', function () {
    $user = User::factory()->create([
        'is_verified' => false,
        'otp' => hash('sha256', '123456'),
        'otp_expires_at' => Carbon::now('Asia/Makassar')->addMinutes(30),
    ]);

    $response = $this->actingAs($user)->post(route('account-verification.store'), [
        'otp' => ['1', '2', '3', '4', '5', '6'],
    ]);

    expect($user->fresh()->is_verified)->toBeTruthy();
    $response->assertRedirect(route('dashboard.getStarted'));
});

test('account verification returns human friendly error with invalid OTP', function () {
    $user = User::factory()->create([
        'is_verified' => false,
        'otp' => hash('sha256', '123456'),
        'otp_expires_at' => Carbon::now('Asia/Makassar')->addMinutes(30),
    ]);

    $response = $this->actingAs($user)->post(route('account-verification.store'), [
        'otp' => ['6', '5', '4', '3', '2', '1'],
    ]);

    $response->assertSessionHasErrors([
        'otp' => 'Kode OTP yang Anda masukkan salah atau telah kedaluwarsa. Silakan periksa kembali email Anda.',
    ]);
});

test('user can request resend OTP', function () {
    Mail::fake();

    $user = User::factory()->create([
        'is_verified' => false,
        'otp' => hash('sha256', '123456'),
        'otp_expires_at' => Carbon::now('Asia/Makassar')->addMinutes(30),
    ]);

    $response = $this->actingAs($user)->post(route('account-verification.resend'));

    $response->assertRedirect(route('account-verification.show'));
    $response->assertSessionHas('status', 'Kode OTP baru telah berhasil dikirim ke email Anda.');

    Mail::assertSent(MailSend::class, function ($mail) use ($user) {
        return strlen($mail->otp) === 6 && ctype_digit((string) $mail->otp) && hash_equals($user->fresh()->otp, hash('sha256', (string) $mail->otp));
    });
});
