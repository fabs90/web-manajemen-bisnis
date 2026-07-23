@extends('layouts.partial.layouts')
@section('page-title', 'Penerimaan Kas Perusahaan | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'Penerimaan Kas Perusahaan ')
@section('section-row')
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Daftar Penerimaan Kas Perusahaan</h5>
        <a href="{{ route('keuangan.pendapatan.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Penerimaan Kas
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle" id="allDatasTable">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tanggal</th>
                    <th>Uraian</th>
                    <th>Piutang Dagang</th>
                    <th>Penjualan Tunai</th>
                    <th>Potongan Penjualan</th>
                    <th>Lain-lain</th>
                    <th>Masuk ke Kas Besar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($allDatas as $data)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($data->tanggal)->format('d-m-Y') }}</td>
                        <td>{{ $data->uraian }}</td>
                        <td>Rp {{ number_format($data->piutang_dagang ?? 0, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($data->penjualan_tunai ?? 0, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($data->potongan_penjualan ?? 0, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($data->lain_lain ?? 0, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($data->uang_diterima ?? 0, 0, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('keuangan.pendapatan.show', $data->id) }}"
                                class="btn btn-info btn-sm text-white">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            <form id="deleteForm-{{ $data->id }}"
                                action="{{ route('keuangan.pendapatan.destroy', $data->id) }}" method="POST"
                                class="d-inline delete-btn">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger btn-sm"
                                    onclick="confirmDelete({{ $data->id }})">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" class="text-end pe-3">Total Kas Masuk</td>
                    <td><b>Rp {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}</b></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <h5 class="mt-2 mb-3">Semua Data Piutang</h5>
    @forelse ($dataPiutang as $pelangganId => $items)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-secondary text-white fw-bold">
                {{ $items->first()->pelanggan->nama ?? 'Tidak diketahui' }}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle piutang-datatable">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Uraian</th>
                                <th>Debit</th>
                                <th>Kredit</th>
                                <th>Saldo</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                    <td>{{ $item->uraian }}</td>
                                    <td>Rp {{ number_format($item->debit ?? 0, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item->kredit ?? 0, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item->saldo ?? 0, 0, ',', '.') }}</td>
                                    <td>
                                        @if (isset($item->faktur) && $item->faktur)
                                            <button type="button" class="btn btn-info btn-sm text-white me-1"
                                                data-bs-toggle="modal" data-bs-target="#detailPiutangModal-{{ $item->id }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            
                                            {{-- Modal Detail Piutang --}}
                                            <div class="modal fade" id="detailPiutangModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Detail Transaksi: {{ $item->uraian }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <h6>Barang yang Terjual (Modal Saja)</h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-bordered">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Nama Barang</th>
                                                                            <th>Kuantitas</th>
                                                                            <th>Harga Modal</th>
                                                                            <th>Total Modal</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @php
                                                                            $totalModal = 0;
                                                                            $details = [];
                                                                            if ($item->faktur->suratPengirimanBarang) {
                                                                                if ($item->faktur->suratPengirimanBarang->pesananPenjualan) {
                                                                                    $details = $item->faktur->suratPengirimanBarang->pesananPenjualan->details;
                                                                                } elseif ($item->faktur->suratPengirimanBarang->pesananPembelian) {
                                                                                    $details = $item->faktur->suratPengirimanBarang->pesananPembelian->pesananPembelianDetail;
                                                                                }
                                                                            }
                                                                        @endphp
                                                                        @forelse($details as $detail)
                                                                            @php
                                                                                $hargaBeli = $detail->barang ? $detail->barang->harga_beli_per_unit : 0;
                                                                                $subtotal = $hargaBeli * $detail->kuantitas;
                                                                                $totalModal += $subtotal;
                                                                            @endphp
                                                                            <tr>
                                                                                <td>{{ $detail->nama_barang }}</td>
                                                                                <td>{{ $detail->kuantitas }}</td>
                                                                                <td>Rp {{ number_format($hargaBeli, 0, ',', '.') }}</td>
                                                                                <td>Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                                                            </tr>
                                                                        @empty
                                                                            <tr>
                                                                                <td colspan="4" class="text-center">Tidak ada data barang.</td>
                                                                            </tr>
                                                                        @endforelse
                                                                    </tbody>
                                                                    <tfoot>
                                                                        <tr>
                                                                            <th colspan="3" class="text-end">Total</th>
                                                                            <th>Rp {{ number_format($totalModal, 0, ',', '.') }}</th>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </div>

                                                            <h6 class="mt-4">Unduh Dokumen</h6>
                                                            <div class="d-flex gap-2 flex-wrap">
                                                                @if($item->faktur->suratPengirimanBarang)
                                                                    @if($item->faktur->suratPengirimanBarang->spp_id)
                                                                        <a href="{{ route('administrasi.spp.generatePdf', $item->faktur->suratPengirimanBarang->spp_id) }}" class="btn btn-outline-warning btn-sm" target="_blank">
                                                                            <i class="bi bi-file-earmark-pdf"></i> Surat Pesanan Pembelian
                                                                        </a>
                                                                    @elseif($item->faktur->suratPengirimanBarang->pesanan_penjualan_id)
                                                                        <a href="{{ route('administrasi.spb.spp-pelanggan.generatePdf', $item->faktur->suratPengirimanBarang->pesanan_penjualan_id) }}" class="btn btn-outline-warning btn-sm" target="_blank">
                                                                            <i class="bi bi-file-earmark-pdf"></i> Surat Pesanan Pembelian
                                                                        </a>
                                                                    @endif
                                                                    
                                                                    <a href="{{ route('administrasi.spb.generatePdf', $item->faktur->suratPengirimanBarang->id) }}" class="btn btn-outline-success btn-sm" target="_blank">
                                                                        <i class="bi bi-file-earmark-pdf"></i> Surat Pengiriman Barang
                                                                    </a>
                                                                @endif
                                                                <a href="{{ route('administrasi.faktur-penjualan.generatePdf', $item->faktur->id) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                                                                    <i class="bi bi-file-earmark-pdf"></i> Surat Faktur Penjualan
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <form id="deletePiutang-{{ $item->id }}"
                                            action="{{ route('keuangan.piutang.destroy', $item->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm delete-btn">
                                                <span class="delete-text"><i class="bi bi-trash"></i></span>
                                                <span class="spinner-border spinner-border-sm d-none"></span>
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
    @empty
        <div class="alert alert-secondary text-center">
            <em>Tidak ada data piutang.</em>
        </div>
    @endforelse

@endsection
@push('script')
    <script>
        $(document).ready(function() {
            $('#allDatasTable').DataTable({
                paging: true,
                pageLength: 10,
                ordering: true,
                responsive: false,
                info: false,
                language: {
                    emptyTable: "Tidak ada data untuk ditampilkan",
                    search: "Cari:"
                }
            });

            $('.kas-kecil-table').DataTable({
                paging: true,
                pageLength: 10,
                ordering: true,
                responsive: true,
                info: false,
                language: {
                    emptyTable: "Tidak ada data kas kecil untuk ditampilkan",
                    search: "Cari:"
                }
            });

            $('.piutang-datatable').DataTable({
                paging: true,
                pageLength: 10,
                ordering: false, // Dimatikan agar urutan saldo per pelanggan tetap logis
                responsive: true,
                info: true,
                language: {
                    emptyTable: "Tidak ada data untuk ditampilkan",
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                    infoFiltered: "(disaring dari _MAX_ total entri)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            $('.delete-btn').on('click', function() {
                let form = $(this).closest('form');

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

                        let btn = form.find('.delete-btn');
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
