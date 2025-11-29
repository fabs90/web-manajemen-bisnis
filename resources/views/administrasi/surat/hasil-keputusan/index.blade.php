@extends('layouts.partial.layouts')

@section('page-title', 'List Hasil Keputusan Rapat | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

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
                <strong>DATA HASIL KEPUTUSAN RAPAT</strong>
            </div>
            <div class="card-body">
                {{-- Action Buttons --}}
                <div class="d-flex justify-content-between gap-2 mt-3 mb-2">
                    <a href="{{ route('faktur.create') }}" class="btn btn-success">
                        <i class="bi bi-file-earmark-plus"></i> Tambah Faktur Penjualan
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
                                <th>Nomor PSB</th>
                                <th>Tanggal</th>
                                <th>Jenis Pengiriman</th>
                                <th>Bagian Penjualan</th>
                                <th width="13%">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($faktur as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>

                                    <td>{{ $item->kode_faktur }}</td>
                                    <td>{{ $item->nama_pembeli }}</td>
                                    <td>{{ $item->nomor_pesanan ?? '-' }}</td>
                                    <td>{{ $item->nomor_psb ?? '-' }}</td>

                                    <td class="text-center">
                                        {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') : '-' }}
                                    </td>

                                    <td>{{ $item->jenis_pengiriman ?? '-' }}</td>
                                    <td>{{ $item->nama_bagian_penjualan ?? '-' }}</td>

                                    <td class="text-center">
                                        {{-- Download PDF --}}
                                        <a href="{{ route('faktur.generatePdf', $item->id) }}"
                                            class="btn btn-warning btn-sm"
                                            data-bs-toggle="tooltip"
                                            title="Unduh PDF Faktur"
                                            target="_blank">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
                                        {{-- Delete --}}
                                        <form action="{{route('faktur.destroy', $item->id)}}"
                                            method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm delete-btn" type="submit">
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
            $('#surat-undangan-rapat-table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    emptyTable: "Belum ada Faktur Penjualan⛵"
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
