<?php

use App\Models\User;

test('superadmin login screen can be rendered', function () {
    $response = $this->get(route('superadmin.login'));

    $response->assertStatus(200);
});

test('superadmin can authenticate and access admin index', function () {
    $admin = User::factory()->create([
        'role' => 'superadmin',
    ]);

    $response = $this->post(route('superadmin.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('superadmin.index'));
});

test('non-superadmin cannot login via superadmin login screen and gets human friendly error', function () {
    $user = User::factory()->create([
        'role' => 'ukm',
    ]);

    $response = $this->post(route('superadmin.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHas('error', 'Akun Anda tidak memiliki hak akses sebagai admin.');
});

test('superadmin login with invalid credentials returns human friendly error', function () {
    $response = $this->post(route('superadmin.store'), [
        'email' => 'unknown@example.com',
        'password' => 'wrong-pass',
    ]);

    $this->assertGuest();
    $response->assertSessionHas('error', 'Email atau kata sandi yang Anda masukkan salah.');
});

test('superadmin login with missing inputs returns human friendly validation errors', function () {
    $response = $this->post(route('superadmin.store'), [
        'email' => '',
        'password' => '',
    ]);

    $response->assertSessionHasErrors([
        'email' => 'Alamat email wajib diisi.',
        'password' => 'Kata sandi wajib diisi.',
    ]);
});
