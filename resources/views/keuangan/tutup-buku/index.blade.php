@extends('layouts.partial.layouts')
@section('page-title', 'Tutup Buku Tahun | Digitrans')

@section('section-heading', 'Tutup Buku Tahunan')
@section('section-row')
    <div class="card p-4 shadow">
        <div class="text-center mb-4">
            <h4 class="fw-bold">Tutup Buku Tahun <span class="text-primary">{{ $tahun }}</span></h4>
            <p class="text-muted">Proses ini akan:</p>
            <ul class="text-start mx-auto" style="max-width: 500px;">
                <li>Hitung laba/rugi bersih tahun {{ $tahun }}</li>
                <li>Transfer laba ke modal</li>
                <li>Simpan Neraca Akhir {{ $tahun }}</li>
                <li>Buat Neraca Awal {{ $tahun + 1 }} (otomatis)</li>
                <li>Lock data tahun {{ $tahun }} (tidak bisa diedit lagi)</li>
            </ul>
        </div>

        @if ($sudahDitutup)
            <div class="alert alert-success text-center">
                Tahun {{ $tahun }} <strong>sudah ditutup</strong> pada {{ $ditutupPada }}
            </div>
        @else
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <h5>Pratinjau Laba/Rugi {{ $tahun }}</h5>
                            <hr>
                            <p>Total Pendapatan: <strong>Rp
                                    {{ number_format($pratinjau['labaKotor'], 0, ',', '.') }}</strong></p>
                            <p>HPP + Beban: <strong>Rp
                                    {{ number_format($pratinjau['labaOperasional'], 0, ',', '.') }}</strong>
                            </p>
                            <p class="text-success fs-4 fw-bold">
                                Laba Bersih: Rp {{ number_format($pratinjau['labaSetelahPajak'], 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <form action="{{ route('tutup-buku.proses') }}" method="POST"
                    onsubmit="return confirm('Yakin tutup buku tahun {{ $tahun }}? Proses ini TIDAK BISA DIBATALKAN!')">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-lg px-5">
                        TUTUP BUKU TAHUN {{ $tahun }}
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection
