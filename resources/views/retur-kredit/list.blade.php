@extends('layouts.partial.layouts')
@section('page-title', 'Retur | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'List Retur Penjualan & Pengeluaran')
@section('section-row')

<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white fw-bold d-flex justify-content-between align-items-center">
        <span>Retur Penjualan (Piutang/Kas)</span>
        <a href="{{ route('retur.create-penjualan') }}" class="btn btn-light btn-sm">Tambah Retur Penjualan</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="returPenjualanTable">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Debitur</th>
                        <th>Referensi</th>
                        <th>Keterangan</th>
                        <th>Nominal (Rp)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($returPenjualan as $retur)
                        @php
                            $pihak = $retur->items->firstWhere('sub_ledger_type', 'App\Models\Pelanggan');
                            // Nominal retur adalah nilai debit pada akun pendapatan
                            $nominal = $retur->items->where('account.code', '4101')->sum('debit');
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $retur->date->format('d/m/Y') }}</td>
                            <td>{{ $pihak->subLedger->nama ?? '-' }}</td>
                            <td><code>{{ $retur->reference_number }}</code></td>
                            <td>{{ $retur->description }}</td>
                            <td class="text-end fw-bold text-danger">
                                Rp {{ number_format($nominal, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">Tercatat di Jurnal</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-success text-white fw-bold d-flex justify-content-between align-items-center">
        <span>Retur Pembelian (Hutang / Kas)</span>
        <a href="{{ route('retur.create-pembelian') }}" class="btn btn-light btn-sm">Tambah Retur Pembelian</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="returPengeluaranTable">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Kreditur</th>
                        <th>Referensi</th>
                        <th>Keterangan</th>
                        <th>Nominal (Rp)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($returPengeluaran as $retur)
                        @php
                            $pihak = $retur->items->firstWhere('sub_ledger_type', 'App\Models\Pelanggan');
                            // Nominal retur pembelian adalah nilai kredit pada akun persediaan
                            $nominal = $retur->items->where('account.code', '1105')->sum('credit');
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $retur->date->format('d/m/Y') }}</td>
                            <td>{{ $pihak->subLedger->nama ?? '-' }}</td>
                            <td><code>{{ $retur->reference_number }}</code></td>
                            <td>{{ $retur->description }}</td>
                            <td class="text-end fw-bold text-success">
                                Rp {{ number_format($nominal, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success">Tercatat di Jurnal</span>
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
        $('#returPenjualanTable, #returPengeluaranTable').DataTable({
            pageLength: 10,
            language: {
                search: "Cari:",
                paginate: { previous: "Sebelumnya", next: "Berikutnya" },
                emptyTable: "Tidak ada data retur",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 dari 0 data",
            },
            columnDefs: [{ targets: 0, orderable: false }]
        });
    });
</script>
@endpush
