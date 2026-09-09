<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create([
        'role' => 'ukm',
        'is_verified' => true,
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'role' => 'ukm',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password and receives human friendly error', function () {
    $user = User::factory()->create([
        'role' => 'ukm',
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
        'role' => 'ukm',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors([
        'email' => 'Email atau kata sandi yang Anda masukkan salah. Silakan periksa kembali.',
    ]);
});

test('users see human friendly validation error when required fields are missing', function () {
    $response = $this->post('/login', [
        'email' => '',
        'password' => '',
        'role' => '',
    ]);

    $response->assertSessionHasErrors([
        'email' => 'Alamat email wajib diisi.',
        'password' => 'Kata sandi wajib diisi.',
        'role' => 'Silakan pilih jenis akun terlebih dahulu.',
    ]);
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
