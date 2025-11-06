@extends('layouts.partial.layouts')
@section('page-title', 'Retur | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnisn')

@section('section-heading', 'List Retur Penjualan & Pengeluaran')
@section('section-row')

<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white fw-bold">
        Retur Penjualan (Piutang)
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="returPenjualanTable">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Debitur</th>
                        <th>Kode Piutang</th>
                        <th>Uraian</th>
                        <th>Nominal (Rp)</th>
                        <th>Penanganan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($returPenjualan as $retur)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($retur->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $retur->pelanggan->nama ?? '-' }}</td>
                            <td><code>{{ $retur->kode }}</code></td>
                            <td>{{ $retur->uraian }}</td>
                            <td class="text-end fw-bold text-danger">
                                Rp {{ number_format($retur->debit > 0 ? $retur->debit : $retur->kredit, 0, ',', '.') }}
                            </td>
                            <td>
                                @if($retur->kredit > 0)
                                    <span class="badge bg-warning text-dark">Tunai Kembali</span>
                                @else
                                    <span class="badge bg-info">Kurangi Piutang</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada retur penjualan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-success text-white fw-bold">
        Retur Pengeluaran (Pembelian / Hutang)
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="returPengeluaranTable">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Kreditur</th>
                        <th>Kode Hutang</th>
                        <th>Uraian</th>
                        <th>Nominal (Rp)</th>
                        <th>Penanganan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($returPengeluaran as $retur)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($retur->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $retur->pelanggan->nama ?? '-' }}</td>
                            <td><code>{{ $retur->kode }}</code></td>
                            <td>{{ $retur->uraian }}</td>
                            <td class="text-end fw-bold text-success">
                                Rp {{ number_format($retur->debit > 0 ? $retur->debit : $retur->kredit, 0, ',', '.') }}
                            </td>
                            <td>
                                @if($retur->uraian && Str::contains(strtolower($retur->uraian), 'tunai'))
                                    <span class="badge bg-warning text-dark">Tunai Kembali</span>
                                @else
                                    <span class="badge bg-info">Kurangi Hutang</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada retur pengeluaran.</td></tr>
                    @endforelse
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
