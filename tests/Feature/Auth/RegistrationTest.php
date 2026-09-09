<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'ukm',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('account-verification.show', absolute: false));
});

test('registration fails with human friendly errors when input is invalid', function () {
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->post('/register', [
        'name' => '',
        'email' => 'existing@example.com',
        'role' => '',
        'password' => 'pass1',
        'password_confirmation' => 'pass2',
    ]);

    $response->assertSessionHasErrors([
        'name' => 'Nama UMKM/perusahaan wajib diisi.',
        'email' => 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau masuk ke akun Anda.',
        'role' => 'Silakan pilih jenis akun.',
        'password' => 'Konfirmasi kata sandi tidak cocok.',
    ]);
});
