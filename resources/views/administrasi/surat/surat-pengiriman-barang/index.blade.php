@extends('layouts.partial.layouts')

@section('page-title', 'Surat Pengiriman Barang (SPB) | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-row')
<div class="container mt-4">

    {{-- Alert Sukses --}}
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
            <strong>DAFTAR SURAT PENGIRIMAN BARANG (SPB)</strong>
            <a href="{{ route('administrasi.spb.create') }}" class="btn btn-light btn-sm">
                <i class="bi bi-plus-circle"></i> Buat SPB Baru
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="spb-table">
                    <thead class="table-primary text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nomor SPB</th>
                            <th>Tanggal SPB</th>
                            <th>No. Pesanan Pembelian</th>
                            <th>Supplier / Pelanggan</th>
                            <th>Keadaan Barang</th>
                            <th>Penerima</th>
                            <th width="14%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($suratPengirimanBarang as $spb)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="fw-bold">{{ $spb->nomor_pengiriman_barang }}</td>
                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($spb->tanggal_pengiriman)->format('d-m-Y') }}
                                </td>
                                <td>
                                    {{ $spb->pesananPembelian?->nomor_pesanan_pembelian ?? '-' }}
                                </td>
                                <td>
                                    {{ $spb->pesananPembelian?->pelanggan?->nama ?? '-' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $spb->keadaan == 'baik' ? 'success' : 'warning' }}">
                                        {{ ucfirst($spb->keadaan ?? '-') }}
                                    </span>
                                </td>
                                <td>{{ $spb->nama_penerima ?? '-' }}</td>
                                <td class="text-center">
                                    {{-- Tombol Lihat Detail --}}
                                    @if ($spb->suratPengirimanBarangDetail->count() > 0)
                                        <button class="btn btn-info btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detailModal{{ $spb->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        {{-- Modal Detail SPB --}}
                                        <div class="modal fade" id="detailModal{{ $spb->id }}" tabindex="-1"
                                            aria-labelledby="modalSPBLabel{{ $spb->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">

                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">
                                                            Detail SPB - {{ $spb->nomor_pengiriman_barang }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <div class="modal-body">

                                                        <div class="row mb-2">
                                                            <div class="col-6">
                                                                <strong>Tgl Kirim:</strong>
                                                                {{ \Carbon\Carbon::parse($spb->tanggal_terima)->format('d-m-Y') }}
                                                            </div>
                                                            <div class="col-6">
                                                                <strong>Penerima:</strong> {{ $spb->nama_penerima ?? '-' }}
                                                            </div>
                                                            <div class="col-6">
                                                                <strong>Pengirim:</strong> {{ $spb->nama_pengirim ?? '-' }}
                                                            </div>
                                                            <div class="col-6">
                                                                <strong>Keadaan:</strong> {{ ucfirst($spb->keadaan ?? '-') }}
                                                            </div>
                                                        </div>

                                                        <hr>

                                                        <table class="table table-bordered table-sm text-center">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>Barang</th>
                                                                    <th>Jumlah Dikirim</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            @foreach ($spb->suratPengirimanBarangDetail as $detail)
                                                                <tr>
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td>{{ $detail->pesananPembelianDetail->nama_barang }}</td>
                                                                    <td>{{ $detail->jumlah_dikirim }}</td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>

                                                    </div>

                                                    <div class="modal-footer">
                                                        <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Tidak ada detail</span>
                                    @endif
                                    <a href="{{ route('administrasi.spb.generatePdf', $spb->id) }}"
                                       class="btn btn-warning btn-sm" target="_blank" title="Download PDF SPB">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>

                                    {{-- Hapus --}}
                                    <form action="{{ route('administrasi.spb.destroy', $spb->id) }}"
                                          method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm delete-btn" title="Hapus SPB">
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
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        $('#spb-table').DataTable({
            responsive: true,
            pageLength: 15,
            language: {
                emptyTable: "Tidak ada data yang tersedia"
            }
        });

        // Konfirmasi hapus dengan SweetAlert2
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            const form = this;

            Swal.fire({
                title: 'Yakin ingin menghapus SPB ini?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
