@extends('layouts.partial.layouts')
@section('page-title', 'Neraca Awal | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'Neraca Awal ')
@section('section-row')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Daftar Neraca Awal</h5>
        <a href="{{ route('laporan-keuangan.aset-hutang.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Neraca Awal
        </a>
    </div>


    <table class="table" id="table-neraca-awal">
        <thead>
            <tr>
                <th>per-Tanggal</th>
                <td>Debit</td>
                <td>Kredit</td>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($neracaAwal as $index => $item)
                <tr>
                    <td>{{ $item->created_at }}</td>
                    <td>Rp {{ number_format($item->total_debit) }}</td>
                    <td>Rp {{ number_format($item->total_kredit) }}</td>
                    <td>
                        <a href="{{ route('laporan-keuangan.aset-hutang.show', ['id' => $item->id]) }}" class="btn btn-sm btn-info text-white">
                            <i class="bi bi-eye"></i>
                        </a>

                        <form action="{{route('laporan-keuangan.aset-hutang.destroy', ['id' => $item->id])}}" method="POST" class="d-inline"
                            onsubmit="return confirm('Yakin ingin menghapus barang ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-3">
                        <em>Tidak ada data barang.</em>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>


@endsection
