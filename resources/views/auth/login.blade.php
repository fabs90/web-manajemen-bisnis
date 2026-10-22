@extends('layouts.auth.partial.layouts')

@section('page-title', 'Login | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('auth-title', 'Masuk ke Akun')
@section('auth-subtitle', 'Selamat datang kembali! Silakan login untuk melanjutkan.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}" />
@endpush

@section('auth-content')
    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <div class="form-group">
            <label for="email">Alamat Email</label>
            <input id="email" type="email" name="email" placeholder="contoh@email.com" value="{{ old('email') }}"
                required autofocus>
        </div>

        <div class="form-group">
            <label for="role">Pilih Jenis Akun</label>
            <select id="role" name="role" required>
                <option value="" disabled selected>Pilih...</option>
                <option value="ukm" {{ old('role') == 'ukm' ? 'selected' : '' }}>UKM</option>
                <option value="nelayan" {{ old('role') == 'nelayan' ? 'selected' : '' }}>Nelayan</option>
                <option value="koperasi" {{ old('role') == 'koperasi' ? 'selected' : '' }}>Koperasi</option>
            </select>
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

        <div class="form-options">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="forgot">Lupa password?</a>
            @endif
        </div>

        <div class="cf-turnstile" name="cf-turnstile-response" data-sitekey="{{ config('services.turnstile.site_key') }}"
            data-theme="light" data-size="normal"></div>

        <button id="btn-submit" type="submit" class="btn-primary">Masuk</button>
        <p class="register-text">
            Belum punya akun?
            <a href="{{ route('register') }}">Daftar sekarang</a>
        </p>
    </form>
@endsection

@push('script')
    <script src="{{ asset('js/login-admin.js') }}"></script>
@endpush
