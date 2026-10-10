@extends('layouts.partial.layouts')
@section('page-title', 'Detail Stok - ' . $barang->nama . ' | TRANSDIGITAL')
@section('section-heading', 'Detail Stok: ' . $barang->nama)
@section('section-row')

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <a href="{{ route('kartu-gudang.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kartu Gudang
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('kartu-gudang.create', ['barang_id' => $barang->id]) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Tambah Stok Ikan
            </a>
            <a href="{{ route('barang.show', $barang->id) }}" class="btn btn-outline-info btn-sm">
                <i class="bi bi-pencil-square me-1"></i> Edit Master Ikan
            </a>
        </div>
    </div>

    <!-- Card Info Barang & Saldo Akhir -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-5 border-end-lg">
                    <div class="d-flex  align-items-center gap-3">
                        <div>
                            <span class="badge bg-secondary font-monospace mb-1">{{ $barang->kode_barang }}</span>
                            <h4 class="mb-0 fw-bold">{{ $barang->nama }}</h4>
                            <small class="text-muted d-block">
                                Satuan: <strong>Per-kilogram (Kg)</strong> &bull; 1 unit = 1 kilogram
                            </small>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row g-2 small text-muted">
                        <div class="col-12">
                            Harga Jual (per-Kg): <br><strong>Rp
                                {{ number_format($barang->harga_jual_per_unit, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 border h-100">
                                <small class="text-muted d-block fw-bold text-uppercase">Saldo Akhir (per-Kg)</small>
                                <div class="fs-3 fw-bold text-primary mt-1">
                                    {{ number_format($barang->saldo_akhir ?? 0, 0, ',', '.') }}
                                    <span class="fs-6 fw-normal text-muted">Kg</span>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    Total ikan per-kilogram tersedia di gudang
                                </small>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 border h-100">
                                <small class="text-muted d-block fw-bold text-uppercase">Konversi Satuan</small>
                                <div class="fs-4 fw-bold text-info mt-1">
                                    1 Unit = 1 Kg
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    Khusus ikan: 1 unit dicatat setara 1 kilogram
                                </small>
                            </div>
                        </div>

                        <div class="col-12">
                            <div
                                class="p-3 rounded-3 border bg-success-subtle text-success-emphasis d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <small class="fw-bold d-block text-uppercase">Total Potensi Nilai Jual Stok Ikan
                                        Ini</small>
                                    @if (($barang->harga_beli_per_unit ?? 0) > 0)
                                        <span class="small">{{ number_format($barang->saldo_akhir ?? 0, 0, ',', '.') }} kg
                                            × Rp
                                            {{ number_format($barang->harga_beli_per_unit, 0, ',', '.') }} / kg</span>
                                    @else
                                        <span class="small">{{ number_format($barang->saldo_akhir ?? 0, 0, ',', '.') }} kg
                                            × Rp
                                            {{ number_format($barang->harga_jual_per_unit, 0, ',', '.') }} / kg</span>
                                    @endif
                                </div>
                                <h4 class="mb-0 fw-bold">
                                    @if (($barang->harga_beli_per_unit ?? 0) > 0)
                                        Rp {{ number_format($barang->nilai_persediaan ?? 0, 0, ',', '.') }}
                                    @else
                                        Rp
                                        {{ number_format(($barang->saldo_akhir ?? 0) * $barang->harga_jual_per_unit, 0, ',', '.') }}
                                    @endif
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Tabel Riwayat Kartu Gudang -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Riwayat Kartu Gudang</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-striped" id="tableDetailKartuGudang">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Tanggal</th>
                            <th>Uraian / Keterangan</th>
                            <th class="text-end">Diterima (Kg)</th>
                            <th class="text-end">Dikeluarkan (Kg)</th>
                            <th class="text-end">Stok (Kg)</th>
                            <th class="text-center" style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($barang->kartuGudang as $kartu)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $kartu->tanggal ? $kartu->tanggal->format('d-m-Y') : '-' }}</td>
                                <td>{{ $kartu->uraian }}</td>
                                <td class="text-end">
                                    @if ($kartu->diterima > 0)
                                        <span
                                            class="text-success fw-bold">+{{ number_format($kartu->diterima, 0, ',', '.') }}
                                            kg</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if ($kartu->dikeluarkan > 0)
                                        <span
                                            class="text-danger fw-bold">-{{ number_format($kartu->dikeluarkan, 0, ',', '.') }}
                                            kg</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-primary">
                                    {{ number_format($kartu->saldo_persatuan, 0, ',', '.') }}
                                    <small class="text-muted fw-normal">kg</small>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('kartu-gudang.destroy', $kartu->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan kartu gudang ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Catatan">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <em>Belum ada mutasi kartu gudang untuk barang ini.</em>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            $('#tableDetailKartuGudang').DataTable({
                searching: true,
                paging: true,
                pageLength: 15,
                info: true,
                ordering: true,
                responsive: true,
                language: {
                    search: "Cari Riwayat:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Tidak ditemukan data yang cocok",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ transaksi",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 transaksi",
                    infoFiltered: "(disaring dari _MAX_ total)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                }
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
    </script>
@endpush
