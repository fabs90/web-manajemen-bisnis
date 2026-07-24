@extends('layouts.partial.layouts')

@section('page-title', 'List Faktur Penjualan | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')

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
                <strong>DATA FAKTUR PENJUALAN</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="faktur-penjualan-table">
                        <thead class="table-light text-center">
                            <tr>
                                <th width="5%">No</th>
                                <th>Tanggal Faktur</th>
                                <th>Nomor Pesanan</th>
                                <th>Nomor Faktur</th>
                                <th>Nomor SPB</th>
                                <th>Nama Pembeli</th>
                                <th width="13%">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($fakturPenjualan as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">
                                        {{ $item->tanggal_faktur ? \Carbon\Carbon::parse($item->tanggal_faktur)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td>
                                        {{ $item->suratPengirimanBarang->pesananPenjualan->nomor_pesanan_penjualan ?? ($item->suratPengirimanBarang->pesananPembelian->nomor_pesanan_pembelian ?? '-') }}
                                    </td>
                                    <td>{{ $item->kode_faktur }}</td>
                                    <td>{{ $item->suratPengirimanBarang->nomor_pengiriman_barang ?? '-' }}</td>
                                    <td>
                                        {{ $item->suratPengirimanBarang->pesananPenjualan->pelanggan->nama ?? ($item->suratPengirimanBarang->pesananPembelian->supplier->nama ?? ($item->suratPengirimanBarang->pesananPembelian->pelanggan->nama ?? '-')) }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-info btn-sm text-white"
                                                data-bs-toggle="modal"
                                                data-bs-target="#detailFakturModal-{{ $item->id }}"
                                                title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            {{-- Modal Detail Faktur --}}
                                            <div class="modal fade" id="detailFakturModal-{{ $item->id }}"
                                                tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg text-start">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-primary text-white">
                                                            <h5 class="modal-title">Detail Faktur: {{ $item->kode_faktur }}
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <h6>Barang yang Terjual</h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-bordered text-center">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Nama Barang</th>
                                                                            <th>Kuantitas</th>
                                                                            <th>Harga Satuan</th>
                                                                            <th>Total</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @php
                                                                            $total = 0;
                                                                            $details = [];
                                                                            if ($item->suratPengirimanBarang) {
                                                                                if (
                                                                                    $item->suratPengirimanBarang
                                                                                        ->pesananPenjualan
                                                                                ) {
                                                                                    $details =
                                                                                        $item->suratPengirimanBarang
                                                                                            ->pesananPenjualan->details;
                                                                                } elseif (
                                                                                    $item->suratPengirimanBarang
                                                                                        ->pesananPembelian
                                                                                ) {
                                                                                    $details =
                                                                                        $item->suratPengirimanBarang
                                                                                            ->pesananPembelian
                                                                                            ->pesananPembelianDetail;
                                                                                }
                                                                            }
                                                                        @endphp
                                                                        @forelse($details as $detail)
                                                                            @php
                                                                                $harga = $detail->harga ?? 0;
                                                                                $subtotal =
                                                                                    $detail->total ??
                                                                                    $harga * $detail->kuantitas;
                                                                                $total += $subtotal;
                                                                            @endphp
                                                                            <tr>
                                                                                <td>{{ $detail->nama_barang }}</td>
                                                                                <td>{{ $detail->kuantitas }}</td>
                                                                                <td>Rp
                                                                                    {{ number_format($harga, 0, ',', '.') }}
                                                                                </td>
                                                                                <td>Rp
                                                                                    {{ number_format($subtotal, 0, ',', '.') }}
                                                                                </td>
                                                                            </tr>
                                                                        @empty
                                                                            <tr>
                                                                                <td colspan="4" class="text-center">Tidak
                                                                                    ada data barang.</td>
                                                                            </tr>
                                                                        @endforelse
                                                                    </tbody>
                                                                    <tfoot>
                                                                        <tr>
                                                                            <th colspan="3" class="text-end">Total</th>
                                                                            <th>Rp {{ number_format($total, 0, ',', '.') }}
                                                                            </th>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </div>

                                                            <h6 class="mt-4">Unduh Dokumen</h6>
                                                            <div class="d-flex gap-2 flex-wrap">
                                                                @if ($item->suratPengirimanBarang)
                                                                    @if ($item->suratPengirimanBarang->spp_id)
                                                                        <a href="{{ route('administrasi.spp.generatePdf', $item->suratPengirimanBarang->spp_id) }}"
                                                                            class="btn btn-outline-warning btn-sm"
                                                                            target="_blank">
                                                                            <i class="bi bi-file-earmark-pdf"></i> Surat
                                                                            Pesanan Pembelian
                                                                        </a>
                                                                    @elseif($item->suratPengirimanBarang->pesanan_penjualan_id)
                                                                        <a href="{{ route('administrasi.spb.spp-pelanggan.generatePdf', $item->suratPengirimanBarang->pesanan_penjualan_id) }}"
                                                                            class="btn btn-outline-warning btn-sm"
                                                                            target="_blank">
                                                                            <i class="bi bi-file-earmark-pdf"></i> Surat
                                                                            Pesanan Pembelian
                                                                        </a>
                                                                    @endif

                                                                    <a href="{{ route('administrasi.spb.generatePdf', $item->suratPengirimanBarang->id) }}"
                                                                        class="btn btn-outline-success btn-sm"
                                                                        target="_blank">
                                                                        <i class="bi bi-file-earmark-pdf"></i> Surat
                                                                        Pengiriman Barang
                                                                    </a>
                                                                @endif

                                                                <a href="{{ route('administrasi.faktur-penjualan.generatePdf', $item->id) }}"
                                                                    class="btn btn-outline-primary btn-sm" target="_blank">
                                                                    <i class="bi bi-file-earmark-pdf"></i> Surat Faktur
                                                                    Penjualan
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Delete --}}
                                            <form action="{{ route('administrasi.faktur-penjualan.destroy', $item->id) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm delete-btn" type="submit"
                                                    title="Hapus">
                                                    <span class="delete-text"><i class="bi bi-trash"></i></span>
                                                    <span class="spinner-border spinner-border-sm d-none"
                                                        role="status"></span>
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
            $('#faktur-penjualan-table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    emptyTable: "Belum ada Data Faktur Penjualan📪"
                }
            });

            $('.delete-form').on('submit', function(e) {
                e.preventDefault();
                let form = this;

                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let btn = $(form).find('.delete-btn');
                        btn.prop('disabled', true);
                        btn.find('.delete-text').addClass('d-none');
                        btn.find('.spinner-border').removeClass('d-none');
                        form.submit();
                    }
                });

            });
        });
    </script>
@endpush
