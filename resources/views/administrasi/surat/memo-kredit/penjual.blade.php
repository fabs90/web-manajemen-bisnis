@extends('layouts.partial.layouts')

@section('page-title', 'Memo Kredit dari Penjual | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-row')
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
            <strong>MEMO KREDIT DARI PENJUAL (RETUR PEMBELIAN)</strong>
            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalSelectSPP">
                <i class="bi bi-plus-circle"></i> Tambah Memo Kredit
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="memo-kredit-penjual-table">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nomor Retur</th>
                            <th>Tanggal</th>
                            <th>Nomor SPP</th>
                            <th>Supplier</th>
                            <th>Alasan Pengembalian</th>
                            <th>Total Pengembalian</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($returPembelian as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nomor_retur }}</td>
                                <td>
                                    <span class="badge bg-primary px-3 py-2">
                                        {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary px-3 py-2">
                                        {{ $item->pesananPembelian->nomor_pesanan_pembelian ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    {{ $item->pesananPembelian->supplier->nama ?? '-' }}
                                </td>
                                <td>
                                    {{ $item->alasan_pengembalian ?? '-' }}
                                </td>
                                <td>
                                    Rp {{ number_format($item->total, 0, ',', '.') }}
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('administrasi.memo-kredit.generatePdf-penjual', ['returId' => $item->id]) }}"
                                        class="btn btn-success btn-sm" title="Generate PDF">
                                        <i class="bi bi-file-pdf text-white"></i>
                                    </a>

                                    <form
                                        action="{{ route('administrasi.memo-kredit.destroy-penjual', ['returId' => $item->id]) }}"
                                        method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm delete-btn" type="submit" title="Hapus">
                                            <span class="delete-text"><i class="bi bi-trash"></i></span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
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

    <!-- Modal Select SPP -->
    <div class="modal fade" id="modalSelectSPP" tabindex="-1" aria-labelledby="modalSelectSPPLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSelectSPPLabel">Pilih Surat Pesanan Pembelian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="spp-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Nomor SPP</th>
                                    <th>Tanggal</th>
                                    <th>Supplier</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($suratPesananPembelian as $spp)
                                    <tr>
                                        <td>{{ $spp->nomor_pesanan_pembelian }}</td>
                                        <td>{{ $spp->tanggal_pesanan_pembelian ? \Carbon\Carbon::parse($spp->tanggal_pesanan_pembelian)->format('d-m-Y') : '-' }}
                                        </td>
                                        <td>
                                            {{ $spp->supplier?->nama ?? '-' }}
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('administrasi.memo-kredit.create-penjual', ['sppId' => $spp->id]) }}"
                                                class="btn btn-primary btn-sm">
                                                Pilih
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            $('#memo-kredit-penjual-table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    emptyTable: "Belum ada Data Memo Kredit (Retur Pembelian) 📪"
                }
            });

            $('#spp-table').DataTable({
                responsive: true,
                pageLength: 5
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
