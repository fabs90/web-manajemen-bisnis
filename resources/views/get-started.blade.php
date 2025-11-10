@extends('layouts.partial.layouts')
@section('page-title', 'Get Started | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-row')
    <div class="container py-5">
        <!-- Welcome Section -->
        <div class="text-center mb-5">
            <h1 class="fw-bold display-5 text-primary">
                Selamat Datang, {{ Auth::user()->name ?? 'Pengguna Baru' }}! 🎉
            </h1>
            <p class="lead text-secondary mt-3">
                Akun Anda telah berhasil dibuat. Langkah selanjutnya, mari pelajari cara menggunakan <strong>Digitrans</strong>
                untuk mengelola administrasi dan transaksi bisnis Anda secara efisien.
            </p>
        </div>

        <!-- Video Section -->
        <div class="d-flex justify-content-center mb-5">
            <div class="ratio ratio-16x9 shadow-lg rounded-4 overflow-hidden" style="max-width: 900px; width: 100%;">
                <iframe
                    src="https://www.youtube.com/embed/-xuUehefw7Y?si=V3p8zkPddC2Ru9XK"
                    title="Panduan Singkat Digitrans"
                    allowfullscreen
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                </iframe>
            </div>
        </div>

        <!-- Instruction Section -->
        <div class="mb-5">
            <h4 class="fw-semibold text-primary text-center mb-4">Langkah-langkah Awal Menggunakan Digitrans</h4>
            <ol class="mb-4 fs-5 text-secondary">
                <li>
                    Buka menu di sidebar untuk mengakses fitur utama seperti
                    <strong>Barang</strong>, <strong>Debitur-Kreditur</strong>, <strong>Pendapatan</strong>,
                    <strong>Pengeluaran</strong>, dan <strong>Neraca</strong>.
                </li>
                <li>
                    Tambahkan terlebih dahulu
                    <a href="{{ url('/barang/create') }}"><strong>Data Barang</strong></a> dan
                    <a href="{{ url('/debitur-kreditur/create') }}"><strong>Kreditur-Debitur</strong></a>.
                </li>
                <li>
                    Isi
                    <a href="{{ url('/laporan-keuangan/aset-hutang/create') }}"><strong>Hutang/Neraca Awal</strong></a>
                    pada menu <strong>Aset Hutang</strong>.
                </li>
                <li>
                    Masukan data pendapatan penjualan melalui menu
                    <a href="{{ url('/keuangan/pendapatan/create') }}"><strong>Pendapatan</strong></a> yang terdapat di dalam menu
                    <strong>Transaksi Bisnis</strong>.
                </li>
                <li>
                    Masukan data pengeluaran pembelian melalui menu
                    <a href="{{ url('/keuangan/pengeluaran/create') }}"><strong>Pengeluaran</strong></a> yang terdapat di dalam menu
                    <strong>Transaksi Bisnis</strong>.
                </li>
                <li>
                    Untuk melihat hasil
                    <a href="{{ url('/laporan-keuangan/rugi-laba') }}"><strong>Rugi Laba</strong></a> dan
                    <a href="{{ url('/laporan-keuangan/neraca-akhir') }}"><strong>Neraca Akhir</strong></a>,
                    dapat diakses melalui menu <strong>Transaksi Bisnis</strong>.
                </li>
                <li>
                    Setiap perubahan otomatis tercatat di Buku Besar dan laporan keuangan.
                </li>
            </ol>
        </div>

        <!-- CTA Section -->
        <div class="text-center">
            <h4 class="mb-3">Siap memulai digitalisasi bisnis Anda?</h4>
            <a href="{{ route('dashboard') }}" class="btn btn-lg btn-primary shadow-sm px-4 py-2">
                Masuk ke Dashboard
            </a>
        </div>
    </div>
@endsection

@push('script')
@endpush
