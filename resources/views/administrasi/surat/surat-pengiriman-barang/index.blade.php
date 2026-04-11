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
        <div class="row mb-3">
            <div class="col-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="fw-bold">SPB dari Supplier</h5>
                            <p class="text-muted mb-3">
                                Catat dan kelola pengiriman barang yang diterima dari supplier ke perusahaan.
                            </p>
                        </div>

                        <a href="{{ route('administrasi.spb.createTransaksiKeluar') }}" class="btn btn-primary w-100">
                            + Buat SPB Supplier
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="fw-bold">SPB ke Pelanggan</h5>
                            <p class="text-muted mb-3">
                                Buat dan dokumentasikan pengiriman barang dari perusahaan ke pelanggan.
                            </p>
                        </div>

                        <a href="{{ route('administrasi.spb.createTransaksiMasuk') }}" class="btn btn-success w-100">
                            + Buat SPB Pelanggan
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <strong>DAFTAR SURAT PENGIRIMAN BARANG (SPB)</strong>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="spb-table">
                        <thead class="table-primary text-center">
                            <tr>
                                <th width="5%">No</th>
                                <th>Jenis</th>
                                <th>Nomor SPB</th>
                                <th>Tanggal Dikirim</th>
                                <th>Status</th>
                                <th>Pelanggan/Supplier</th>
                                <th>Keadaan Barang</th>
                                <th>Penerima</th>
                                <th width="14%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suratPengirimanBarang as $spb)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>
                                        @if ($spb->pesananPembelian?->jenis == 'transaksi_keluar')
                                            <span class="badge bg-danger">
                                                Supplier
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                Pelanggan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="fw-bold">{{ $spb->nomor_pengiriman_barang }}</td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($spb->created_at)->format('d-m-Y') }}
                                    </td>
                                    <td>
                                        @php
                                                $statusConfig = [
                                                    'diproses'     => ['bg-secondary', 'Diproses'],
                                                    'dikirim'      => ['bg-primary', 'Dikirim'],
                                                    'diterima'     => ['bg-success', 'Diterima'],
                                                    'dibatalkan'   => ['bg-danger', 'Dibatalkan'],
                                                    'dikembalikan' => ['bg-warning', 'Dikembalikan'],
                                                ];
                                           $config = $statusConfig[$spb->status_pengiriman] ?? ['bg-dark', 'Tidak Diketahui'];
@endphp
<span class="badge {{ $config[0] }}">{{ $config[1] }}</span>
                                    </td>
                                    <td>
                                        @if ($spb->pesananPembelian?->jenis == 'transaksi_keluar')
                                            {{ $spb->pesananPembelian->supplier->nama ?? '-' }}
                                        @else
                                            {{ $spb->pesananPembelian->pelanggan->nama ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $keadaanConfig = [
                                                'baik' => ['bg-success', 'Baik'],
                                                'rusak_ringan' => ['bg-warning', 'Rusak Ringan'],
                                                'rusak_berat' => ['bg-danger', 'Rusak Berat'],
                                            ];
                                            $config = $keadaanConfig[$spb->keadaan] ?? null;
                                        @endphp
                                        @if ($config)
                                            <span class="badge {{ $config[0] }}">{{ $config[1] }}</span>
                                        @else
                                            <span class="badge bg-secondary">Belum Diterima</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($spb->nama_penerima)
                                            {{ $spb->nama_penerima }}
                                        @else
                                            <span class="text-muted fst-italic">Belum Diterima</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            {{-- Tombol Lihat Detail --}}
                                            @if ($spb->suratPengirimanBarangDetail->count() > 0)
                                                <button class="btn btn-info btn-sm" data-bs-toggle="modal"
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
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>

                                                            <div class="modal-body">

                                                                <div class="row mb-2">
                                                                    <div class="col-6">
                                                                        <strong>Tgl Kirim:</strong>
                                                                        {{ \Carbon\Carbon::parse($spb->tanggal_terima)->format('d-m-Y') }}
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <strong>Penerima:</strong>
                                                                        {{ $spb->nama_penerima ?? '-' }}
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <strong>Pengirim:</strong>
                                                                        {{ $spb->nama_pengirim ?? '-' }}
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <strong>Keadaan:</strong>
                                                                        {{ ucfirst($spb->keadaan ?? '-') }}
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
                                                                                <td>{{ $detail->pesananPembelianDetail->nama_barang }}
                                                                                </td>
                                                                                <td>{{ $detail->jumlah_dikirim }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>

                                                            </div>

                                                            <div class="modal-footer">
                                                                <button class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Tutup</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">Tidak ada detail</span>
                                            @endif

                                            <a href="{{ route('administrasi.spb.edit', ['id' => $spb->id]) }}"
                                                class="btn btn-secondary btn-sm" title="Edit SPB">
                                                <i class="bi bi-pencil text-white"></i>
                                            </a>
                                            <form id="generatePdfForm"
                                                action="{{ route('administrasi.spb.generatePdf', $spb->id) }}"
                                                method="get">
                                                <button class="btn btn-warning btn-sm generatePdfBtn" type="submit">
                                                    <span class="btn-text">
                                                        <i class="bi bi-file-earmark-pdf text-white"></i>
                                                    </span>
                                                    <span class="spinner-border spinner-border-sm d-none"></span>
                                                </button>
                                            </form>
                                            {{-- Hapus --}}
                                            <form action="{{ route('administrasi.spb.destroy', $spb->id) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm delete-btn"
                                                    title="Hapus SPB">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
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

            $('#generatePdfForm').on('submit', function(e) {
                let $btn = $(this).find('.generatePdfBtn');
                $btn.prop('disabled', true);
                $btn.find('.btn-text').addClass('d-none');
                $btn.find('.spinner-border').removeClass('d-none');
                setTimeout(() => {
                    $btn.prop('disabled', false);
                    $btn.find('.btn-text').removeClass('d-none');
                    $btn.find('.spinner-border').addClass('d-none');
                }, 3000);
            })

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
