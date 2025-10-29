@extends('layouts.partial.layouts')
@section('page-title', 'List Barang')
@section('section-heading', 'List Barang')
@section('section-row')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Daftar Barang</h5>
        <a href="{{ route('barang.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Barang
        </a>
    </div>

    @if($barang->isEmpty())
        <div class="alert alert-primary">
            Tidak ada data barang.
        </div>
    @else
    <table class="table" id="table-barang">
        <thead>
            <tr>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Jumlah Min (Per-kemasan)</th>
                <th>Jumlah Max (Per-kemasan)</th>
                <th>Harga Beli per-Kemasan</th>
                <th>Harga Beli (Unit)</th>
                <th>Harga Jual (Unit)</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($barang as $index => $item)
                <tr>
                    <td>{{ $item->kode_barang }}</td>
                    <td><strong>{{ $item->nama }}</strong></td>
                    <td>{{ number_format($item->jumlah_min) }}</td>
                    <td>{{ number_format($item->jumlah_max) }}</td>
                    <td>Rp {{ number_format($item->harga_beli_per_kemas, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->harga_beli_per_unit, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->harga_jual_per_unit, 0, ',', '.') }}</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info text-white">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="#" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="#" method="POST" class="d-inline"
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
    @endif


@endsection
@push('script')
    <script>
        $(document).ready(function() {
            $('#table-barang').DataTable({
                searching: true,
                paging: true,
                responsive: false
            });
        });
    </script>
@endpush
