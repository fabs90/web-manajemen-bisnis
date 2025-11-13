@extends('layouts.partial.layouts')
@section('page-title', 'Jadwal Perjalanan | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'Daftar Jadwal Perjalanan')

@section('section-row')
<div class="card shadow-sm rounded-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <a href="{{ route('jadwal-perjalanan.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah Jadwal
        </a>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif


    </div>
</div>
@endsection
