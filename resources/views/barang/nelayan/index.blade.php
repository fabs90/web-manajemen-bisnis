@extends('layouts.partial.layouts')
@section('page-title', 'Daftar Tangkapan | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Daftar Tangkapan Nelayan')
@section('section-row')
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-water me-2"></i>Daftar Tangkapan Nelayan</h5>
            <a href="{{ route('barang.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Tambah Tangkapan Nelayan
            </a>
        </div>
        <div class="card-body">
            @if ($barang->isEmpty())
                <div class="alert alert-primary mb-0">
                    Belum ada data hasil tangkapan.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped w-100" id="table-barang">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th>Kode</th>
                                <th>Nama Hasil Tangkapan</th>
                                <th>Satuan Jual</th>
                                <th>Harga Jual (per-Kg)</th>
                                <th style="width: 15%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($barang as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><span class="badge bg-light-primary text-primary">{{ $item->kode_barang }}</span>
                                    </td>
                                    <td><strong>{{ $item->nama }}</strong></td>
                                    <td><span class="badge bg-light-info text-info">Kilogram (Kg)</span></td>
                                    <td>Rp {{ number_format($item->harga_jual_per_unit, 0, ',', '.') }}</td>
                                    <td>
                                        <a href="{{ route('barang.show', $item->id) }}" class="btn btn-sm btn-warning"
                                            title="Edit Data">
                                            <i class="bi bi-pencil-square text-white"></i>
                                        </a>
                                        <form action="{{ route('barang.destroy', $item->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus hasil tangkapan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" title="Hapus Data">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">
                                        <em>Tidak ada data hasil tangkapan.</em>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

@endsection
@push('script')
    <script>
        $(document).ready(function() {
            $('#table-barang').DataTable({
                searching: true,
                paging: true,
                responsive: true
            });
        });

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sukses!',
                text: '{{ session('success') }}',
                showConfirmButton: true,
                timer: 2500,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            });
        @endif
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: '{{ session('error') }}',
                showConfirmButton: false,
                timer: 3000,
                toast: true,
                position: 'top-end',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            });
        @endif
    </script>
@endpush
