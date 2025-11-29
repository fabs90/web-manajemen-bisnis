@extends('layouts.partial.layouts')
@section('page-title', 'Kasir | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
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
                            <td>{{ date('d-m-Y', strtotime($item->tanggal_transaksi)) }}</td>
                            <td>{{ $item->uraian }}</td>
                            <td>Rp {{ number_format($item->jumlah ?? 0, 0, ',', '.') }}</td>

                            <td class="text-center">
                                <form action="{{route('keuangan.kasir.destroy', ["id"=>$item->id])}}"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Hapus transaksi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
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
