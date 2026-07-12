@extends('layouts.partial.layouts')
@section('page-title', 'List Retur Penjualan | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'List Retur Penjualan (Piutang/Kas)')
@section('section-row')

<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white fw-bold">
        <span>List Retur Penjualan (Memo Kredit)</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="returPenjualanTable">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Nomor Memo</th>
                        <th>Pelanggan</th>
                        <th>Alasan</th>
                        <th>Total (Rp)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($returPenjualan as $retur)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($retur->tanggal)->format('d/m/Y') }}</td>
                            <td><code>{{ $retur->nomor_memo }}</code></td>
                            <td>{{ $retur->fakturPenjualan->suratPengirimanBarang->pesananPenjualan->pelanggan->nama ?? '-' }}</td>
                            <td>{{ Str::limit($retur->alasan_pengembalian, 50) }}</td>
                            <td class="text-end fw-bold text-danger">
                                Rp {{ number_format($retur->total, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal{{ $retur->id }}" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="{{ route('administrasi.memo-kredit.generatePdf', ['fakturId' => $retur->faktur_penjualan_id]) }}" class="btn btn-success btn-sm" title="Download PDF">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail Section -->
@foreach ($returPenjualan as $retur)
<div class="modal fade" id="detailModal{{ $retur->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detail Retur Penjualan ({{ $retur->nomor_memo }})</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($retur->tanggal)->format('d F Y') }}<br>
                        <strong>Pelanggan:</strong> {{ $retur->fakturPenjualan->suratPengirimanBarang->pesananPenjualan->pelanggan->nama ?? '-' }}<br>
                        <strong>Nomor Faktur:</strong> {{ $retur->fakturPenjualan->kode_faktur ?? '-' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Alasan:</strong><br>
                        {{ $retur->alasan_pengembalian }}
                    </div>
                </div>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Harga Satuan (Rp)</th>
                            <th>Subtotal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($retur->memoKreditDetail as $detail)
                        <tr>
                            <td>{{ $detail->nama_barang }}</td>
                            <td class="text-center">{{ $detail->kuantitas }}</td>
                            <td class="text-end">{{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($detail->jumlah, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th class="text-end">Rp {{ number_format($retur->total, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('script')
<script>
    $(document).ready(function() {
        $('#returPenjualanTable').DataTable({
            pageLength: 10,
            language: {
                search: "Cari:",
                paginate: { previous: "Sebelumnya", next: "Berikutnya" },
                emptyTable: "Tidak ada data retur penjualan",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 dari 0 data",
            },
            columnDefs: [{ targets: 6, orderable: false }]
        });
    });
</script>
@endpush
