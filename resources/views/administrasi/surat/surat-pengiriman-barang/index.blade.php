@extends('layouts.partial.layouts')

@section('page-title', 'Surat Pengiriman Barang | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

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
                {{-- Action Buttons --}}
                <div class="d-flex justify-content-end gap-2 mt-3 mb-2">
                    <a href="{{ route('administrasi.spb.create') }}" class="btn btn-success">
                        <i class="bi bi-file-earmark-plus"></i> Tambah Surat Pengiriman Barang (SPB)
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="faktur-penjualan-table">
                        <thead class="table-light text-center">
                            <tr>
                                <th width="5%">No</th>
                                <th>Kode Faktur</th>
                                <th>Nama Pembeli</th>
                                <th>Nomor Pesanan</th>
                                <th>Nomor SPB</th>
                                <th>Tanggal</th>
                                <th>Jenis Pengiriman</th>
                                <th>Bagian Penjualan</th>
                                <th width="13%">Cek Pengiriman Barang</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($dataFaktur as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>

                                    <td>{{ $item->kode_faktur }}</td>
                                    <td>{{ $item->nama_pembeli }}</td>
                                    <td>{{ $item->nomor_pesanan ?? '-' }}</td>
                                    <td>{{ $item->nomor_spb ?? '-' }}</td>

                                    <td class="text-center">
                                        {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') : '-' }}
                                    </td>

                                    <td>{{ $item->jenis_pengiriman ?? '-' }}</td>
                                    <td>{{ $item->nama_bagian_penjualan ?? '-' }}</td>

                                    <td class="text-center">
                                        {{-- Download PDF --}}
                                        @if($item->suratPengirimanBarang)
                                                <a href="{{ route('administrasi.spb.generatePdf', $item->suratPengirimanBarang->id) }}"
                                                   class="btn btn-warning btn-sm"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="Unduh PDF Surat Pengiriman Barang"
                                                   target="_blank">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>
                                            @else
                                                {{-- SPB belum dibuat → tombol disabled + tooltip penjelasan --}}
                                                <button class="btn btn-warning btn-sm" disabled
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="SPB belum dibuat">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </button>
                                            @endif

                                        {{-- Delete --}}
                                        <form action="{{ $item->suratPengirimanBarang ? route('administrasi.spb.destroy', $item->suratPengirimanBarang->id) : '#' }}"
                                              method="POST"
                                              class="d-inline delete-form"
                                              {{ $item->suratPengirimanBarang ? '' : 'style=display:none' }}>
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm delete-btn"
                                                    type="submit"
                                                    title="Hapus"
                                                    {{ $item->suratPengirimanBarang ? '' : 'disabled' }}>
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
