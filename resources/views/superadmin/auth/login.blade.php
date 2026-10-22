@extends('layouts.auth.partial.layouts')

@section('page-title', 'Login Admin | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('auth-title', 'Masuk ke Admin')
@section('auth-subtitle', 'Selamat datang kembali! Silakan login untuk melanjutkan.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}" />
@endpush

@section('auth-content')
    <form method="POST" action="{{ route('superadmin.store') }}" class="auth-form">
        @csrf
        <div class="form-group">
            <label for="email">Alamat Email</label>
            <input id="email" type="email" name="email" placeholder="contoh@email.com" value="{{ old('email') }}"
                required autofocus>
        </div>

        <div class="form-group password-group">
            <label for="password">Kata Sandi</label>
            <div class="password-wrapper">
                <input id="password" type="password" name="password" placeholder="••••••••" required>
                <span class="toggle-password" onclick="togglePassword('password', this)">
                    👁️
                </span>
            </div>
        </div>
        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"
            data-size="normal"></div>
        <input type="hidden" name="role" value="superadmin">
        <button id="btn-submit" type="submit" class="btn-primary">Masuk</button>
    </form>
@endsection

@push('script')
    <script src="{{ asset('js/login-admin.js') }}"></script>
@endpush
