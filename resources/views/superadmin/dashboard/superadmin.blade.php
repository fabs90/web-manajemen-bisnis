@extends('layouts.superadmin-partial.layouts')

@section('page-title', 'Dashboard Admin | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Superadmin Dashboard')
@section('section-row')
    <div class="row">
        <div class="col-6 col-lg-4 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon blue mb-2">
                                <i class="iconly-boldProfile"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Total Pengguna</h6>
                            <h6 class="font-extrabold mb-0">{{ $totalUsers }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-4 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon green mb-2">
                                <i class="iconly-boldTick-Square"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Akun Terverifikasi</h6>
                            <h6 class="font-extrabold mb-0">{{ $verifiedUsers }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 col-md-12">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon red mb-2">
                                <i class="iconly-boldDanger"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Belum Verifikasi</h6>
                            <h6 class="font-extrabold mb-0">{{ $unverifiedUsers }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Selamat Datang, Superadmin!</h4>
                </div>
                <div class="card-body">
                    <p>
                        Ini adalah pusat kendali untuk mengelola pendaftaran pada sistem TRANSDIGITAL.
                    </p>
                    <p>
                        Setiap pendaftar baru tidak akan bisa login atau menggunakan sistem sampai status mereka diverifikasi. 
                        Silakan buka menu <strong><a href="{{ route('superadmin.verify-account.index') }}">Verify Account</a></strong> untuk memberikan akses. Anda juga dapat memantau dan mengubah akses profil setiap user pada menu <strong><a href="{{ route('superadmin.user-management.index') }}">Manage User</a></strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
