@extends('layouts.partial.layouts')
@section('page-title', 'Neraca Awal | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'Neraca Awal ')
@section('section-row')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Daftar Neraca Awal</h5>
        <a href="{{ route('laporan-keuangan.neraca-awal.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Neraca Awal
        </a>
    </div>


    <table class="table" id="table-neraca-awal">
        <thead>
            <tr>
                <th>No. Referensi</th>
                <th>Tanggal</th>
                <th>Deskripsi</th>
                <th>Total Aset (Debit)</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $item)
                <tr>
                    <td><strong>{{ $item->reference_number }}</strong></td>
                    <td>{{ $item->date->format('d/m/Y') }}</td>
                    <td>{{ $item->description }}</td>
                    <td>Rp {{ number_format($item->items->sum('debit'), 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('laporan-keuangan.neraca-awal.show', ['id' => $item->id]) }}" class="btn btn-sm btn-info text-white">
                            <i class="bi bi-eye"></i>
                        </a>

                        <form action="{{route('laporan-keuangan.neraca-awal.destroy', ['id' => $item->id])}}" method="POST" class="d-inline"
                            onsubmit="return confirm('Yakin ingin menghapus data Neraca Awal ini?')">
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
                        <em>Belum ada data Neraca Awal.</em>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>


@endsection
