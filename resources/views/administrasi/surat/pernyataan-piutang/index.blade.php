@extends('layouts.partial.layouts')

@section('page-title', 'Pernyataan Piutang | Digitrans - Administrasi & Transaksi')
@section('section-heading', 'Daftar Pernyataan Piutang')

@section('section-row')
<div class="container mt-4">

    <div class="card shadow-sm p-3">
        {{-- Alert sukses --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <strong>Sukses!</strong> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <table class="table table-bordered table-striped table-hover">
            <thead class="bg-primary text-white">
                <tr class="text-center">
                    <th style="width: 5%">No</th>
                    <th>Nama Pelanggan</th>
                    <th>Total Piutang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dataPiutang as $pelangganId => $items)
                @php
                    $pelanggan = $items->first()->pelanggan;
                    $totalPiutang = $items->sum('debit') - $items->sum('kredit');
                    if ($totalPiutang < 0) $totalPiutang = 0;
                @endphp

                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $pelanggan->nama }}</td>
                        <td>Rp {{ number_format($totalPiutang, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <a href="{{ route('administrasi.pernyataan-piutang.generatePdf', $pelanggan->id) }}"
                               class="btn btn-sm btn-success">
                                <i class="fa fa-print"></i> Cetak Surat
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Tidak ada data piutang</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</div>
@endsection
