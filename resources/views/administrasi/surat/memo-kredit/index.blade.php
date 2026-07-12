@extends('layouts.partial.layouts')

@section('page-title', 'Memo Kredit | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-row')
    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <a href="{{ route('administrasi.memo-kredit.penjual') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-elevate">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="bi bi-box-arrow-in-down text-warning" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="card-title fw-bold text-primary mb-3">Memo Kredit dari Penjual</h4>
                        <p class="card-text text-muted">
                            Kelola memo kredit yang diterima dari penjual (vendor) untuk pengurangan hutang usaha akibat
                            pengembalian barang atau komplain.
                        </p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="{{ route('administrasi.memo-kredit.pelanggan') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-elevate">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="bi bi-box-arrow-up text-primary" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="card-title fw-bold text-primary mb-3">Memo Kredit kepada Pelanggan</h4>
                        <p class="card-text text-muted">
                            Kelola memo kredit yang diterbitkan untuk pelanggan akibat pengembalian barang rusak atau
                            pengurangan piutang pelanggan.
                        </p>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .hover-elevate {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-elevate:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        }
    </style>
@endpush
