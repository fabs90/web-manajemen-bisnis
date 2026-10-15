@extends('layouts.partial.layouts')
@section('page-title', 'Kartu Gudang Nelayan | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Daftar Hasil Tangkapan & Kartu Gudang')
@section('section-row')

    <div class="alert alert-success d-flex align-items-center mb-4 shadow-sm" role="alert">
        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
        <div>
            <h5 class="alert-heading mb-1">Total Nilai Persediaan Tangkapan: <strong>Rp
                    {{ number_format($totalNilaiPersediaan ?? 0, 0, ',', '.') }}</strong></h5>
            <small>
                Nilai ini didapatkan dari akumulasi seluruh persediaan hasil tangkapan nelayan. Rumus per tangkapan: <br>
                <em>(Saldo Terakhir di Kartu Gudang) × (Harga Jual Per Satuan Tangkapan)</em>
            </small>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-water me-2"></i>Ringkasan Stok Ikan & Hasil Tangkapan
                </h5>
                <small class="text-muted">Klik tombol "Detail" pada baris tangkapan untuk melihat riwayat mutasi stok
                    ikan.</small>
            </div>
            <div>
                <a href="{{ route('barang.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Hasil Tangkapan
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-striped" id="tableBarangKartuGudang">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Kode Tangkapan</th>
                            <th>Nama Hasil Tangkapan</th>
                            <th>Satuan Kemasan</th>
                            <th>Stok Tersedia</th>
                            <th>Harga Jual (per Satuan)</th>
                            <th>Nilai Potensi Jual</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($barang as $item)
                            @php
                                $saldoUnit = $item->saldo_akhir ?? 0;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td><span class="badge bg-secondary font-monospace">{{ $item->kode_barang }}</span></td>
                                <td class="fw-bold">{{ $item->nama }}</td>
                                <td>
                                    <span class="badge bg-light-info text-info">
                                        {{ $item->satuan_label === 'Kg' ? 'Kilogram (Kg)' : $item->satuan_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary">{{ number_format($saldoUnit, 0, ',', '.') }}</span>
                                    <small class="text-muted">{{ $item->satuan_label }}</small>
                                </td>
                                <td>Rp {{ number_format($item->harga_jual_per_unit, 0, ',', '.') }}</td>
                                <td>
                                    <strong class="text-success">
                                        Rp {{ number_format($item->nilai_persediaan, 0, ',', '.') }}
                                    </strong>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('kartu-gudang.detail', $item->id) }}" class="btn btn-primary"
                                            title="Lihat Detail Riwayat Kartu Gudang">
                                            <i class="bi bi-eye me-1"></i> Detail
                                        </a>
                                        <a href="{{ route('kartu-gudang.create', ['barang_id' => $item->id]) }}"
                                            class="btn btn-outline-success" title="Tambah Transaksi Stok">
                                            <i class="bi bi-plus-lg"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <em>Belum ada hasil tangkapan yang terdaftar. Silakan tambahkan hasil tangkapan terlebih
                                        dahulu.</em>
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
            $('#tableBarangKartuGudang').DataTable({
                searching: true,
                paging: true,
                pageLength: 10,
                info: true,
                ordering: true,
                responsive: true,
                language: {
                    search: "Cari Tangkapan:",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    zeroRecords: "Tidak ditemukan hasil tangkapan yang cocok",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ tangkapan",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 tangkapan",
                    infoFiltered: "(disaring dari _MAX_ total tangkapan)",
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
