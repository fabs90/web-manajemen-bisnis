@extends('layouts.partial.layouts')
@section('page-title', 'Paket Diskon Penjualan | TRANSDIGITAL')
@section('section-heading', 'Kelola Paket Diskon Penjualan')
@section('section-row')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Sukses!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Daftar Paket Diskon</h5>
        <a href="{{ route('keuangan.paket-diskon.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Paket Diskon
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nama Paket</th>
                    <th>Target Diskon</th>
                    <th>Minimal Pembelian</th>
                    <th>Tipe & Nilai</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($paketDiskons as $diskon)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-bold">{{ $diskon->nama_paket }}</td>
                        <td>
                            @if ($diskon->barang_id)
                                <span class="badge bg-info text-dark">Khusus Produk: {{ $diskon->barang->nama }}</span>
                            @else
                                <span class="badge bg-secondary">Diskon Global (Transaksi)</span>
                            @endif
                        </td>
                        <td>
                            @if ($diskon->minimal_pembelian > 0)
                                Rp {{ number_format($diskon->minimal_pembelian, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if ($diskon->jenis_diskon === 'persentase')
                                <span class="badge bg-primary">{{ number_format($diskon->nilai_diskon, 0) }}%</span>
                            @else
                                <span class="badge bg-success">Rp {{ number_format($diskon->nilai_diskon, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($diskon->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('keuangan.paket-diskon.edit', $diskon->id) }}" class="btn btn-warning btn-sm text-white">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form action="{{ route('keuangan.paket-diskon.destroy', $diskon->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus paket diskon ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada paket diskon yang dibuat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
