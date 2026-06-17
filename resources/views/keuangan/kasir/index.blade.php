@extends('layouts.partial.layouts')
@section('page-title', 'Kasir | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-row')

{{-- Alert sukses --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Sukses!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Alert error --}}
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Gagal!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm p-3">
    <div class="d-flex justify-content-between mb-3">
         <h5 class="mb-0">Semua Data Kasir</h5>
        <a href="{{ route('keuangan.kasir.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Transaksi Kasir
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="kasir-transaction-table">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal Transaksi</th>
                    <th>Uraian</th>
                    <th>Jumlah</th>
                    <th style="width: 120px">Aksi</th>
                </tr>
            </thead>

            <tbody>
                    @foreach ($kasirTransactions as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{$item->created_at }}</td>
                            <td>{{ $item->uraian }}</td>
                            <td>Rp {{ number_format($item->jumlah ?? 0, 0, ',', '.') }}</td>

                            <td class="text-center">
                                <button class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#detailModal{{ $item->id }}" title="Lihat Detail Barang">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <form action="{{route('keuangan.kasir.destroy', ["id"=>$item->id])}}"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Hapus transaksi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Hapus Transaksi">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>

                                <!-- Modal Detail -->
                                <div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="detailModalLabel{{ $item->id }}">Detail Transaksi #{{ $item->journalEntry->reference_number }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped">
                                                        <thead class="table-light text-center">
                                                            <tr>
                                                                <th style="width: 50px;">No</th>
                                                                <th>Nama Barang</th>
                                                                <th style="width: 100px;">Qty</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if($item->journalEntry && $item->journalEntry->kartuGudang && $item->journalEntry->kartuGudang->count() > 0)
                                                                @foreach($item->journalEntry->kartuGudang as $kg)
                                                                    <tr>
                                                                        <td class="text-center">{{ $loop->iteration }}</td>
                                                                        <td>{{ $kg->barang ? $kg->barang->nama : 'Barang tidak ditemukan' }}</td>
                                                                        <td class="text-center">{{ $kg->dikeluarkan }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td colspan="3" class="text-center text-muted">Tidak ada detail barang untuk transaksi ini.</td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('script')
<script>
$(document).ready(function() {
    $('#kasir-transaction-table').DataTable({
        searching: true,
        paging: true,
        responsive: true,
        pageLength: 10,
        language: {
            emptyTable: "Tidak ada data kasir untuk ditampilkan",
        }
    });
});
</script>
@endpush
