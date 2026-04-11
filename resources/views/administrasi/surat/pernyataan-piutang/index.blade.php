@extends('layouts.partial.layouts')

@section('page-title', 'Pernyataan Piutang | Digitrans - Administrasi & Transaksi')
@section('section-heading', 'Daftar Pernyataan Piutang')

@section('section-row')
    <div class="container mt-4">
        {{-- Alert sukses --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <strong>Sukses!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        {{-- Alert Error --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Gagal!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <strong>PERNYATAAN Piutang</strong>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped table-hover" id="pernyataan-piutang-table">
                    <thead class="text-white">
                        <tr class="text-center">
                            <th style="width: 5%">No</th>
                            <th>Nama Pelanggan</th>
                            <th>Total Piutang</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataPiutang as $item)
                            @php
                                $pelanggan = $item->pelanggan;
                                $totalPiutang = max($item->saldo ?? 0, 0);
                            @endphp

                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $pelanggan->nama ?? '-' }}</td>
                                <td>Rp {{ number_format($totalPiutang, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('administrasi.pernyataan-piutang.generatePdf', $pelanggan->id) }}"
                                        class="btn btn-sm btn-success">
                                        <i class="fa fa-print"></i> Cetak Surat
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        $(document).ready(function() {
            $('#pernyataan-piutang-table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    emptyTable: "Tidak ada data pernyataan piutang."
                }
            });
        });
    </script>
@endpush
