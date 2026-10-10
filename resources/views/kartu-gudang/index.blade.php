@extends('layouts.partial.layouts')
@section('page-title', 'Kartu Gudang | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Daftar Barang & Kartu Gudang')
@section('section-row')

    <div class="alert alert-success d-flex align-items-center mb-4 shadow-sm" role="alert">
        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
        <div>
            <h5 class="alert-heading mb-1">Total Nilai Persediaan Barang Dagang Akhir: <strong>Rp
                    {{ number_format($totalNilaiPersediaan ?? 0, 0, ',', '.') }}</strong></h5>
            <small>
                Nilai ini didapatkan dari akumulasi seluruh barang dagang. Rumus per barang: <br>
                <em>(Saldo Persatuan Terakhir di Kartu Gudang) × (Harga Beli Per Unit Barang)</em>
            </small>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-box-seam me-2"></i>
                    {{ auth()->user()?->role === 'nelayan' ? 'Ringkasan Stok Ikan' : ' Ringkasan Stok Gudang' }}
                </h5>
                <small class="text-muted">Klik tombol "Detail" pada baris barang untuk melihat riwayat mutasi
                    {{ auth()->user()?->role === 'nelayan' ? 'stok ikan' : ' kartu gudang' }}.
                </small>
            </div>
            <div>
                <a href="{{ route('barang.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>
                    {{ auth()->user()->role != 'nelayan' ? 'Tambah Master Barang' : 'Tambah Hasil Tangkapan' }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-striped" id="tableBarangKartuGudang">
                    @if (auth()->user()->role === 'nelayan')
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Stok Ikan Per-(Kg)</th>
                                <th>Harga Jual Per-(Kg)</th>
                                <th>Nilai Jual</th>
                                <th class="text-center" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($barang as $item)
                                @php
                                    $saldoUnit = $item->saldo_akhir ?? 0;
                                    $saldoKemasFormatted = $item->formatSaldoPerkemasan($saldoUnit);
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td><span class="badge bg-secondary font-monospace">{{ $item->kode_barang }}</span></td>
                                    <td class="fw-bold">{{ $item->nama }}</td>
                                    <td>
                                        <span
                                            class="fw-bold text-primary">{{ number_format($saldoUnit, 0, ',', '.') }}</span>
                                        <small class="text-muted">(Kg)</small>
                                    </td>
                                    <td>Rp {{ number_format($item->harga_jual_per_unit, 0, ',', '.') }}</td>
                                    <td>
                                        <strong class="text-success">
                                            Rp
                                            {{ number_format($item->nilai_persediaan, 0, ',', '.') }}
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
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        <em>Belum ada barang yang terdaftar. Silakan tambahkan barang terlebih dahulu.</em>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    @else
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Isi / Kemas</th>
                                <th>Batas Stok</th>
                                <th>Saldo Unit</th>
                                <th>Saldo Kemasan</th>
                                <th>Harga Beli / Unit</th>
                                <th>Nilai Persediaan</th>
                                <th class="text-center" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($barang as $item)
                                @php
                                    $saldoUnit = $item->saldo_akhir ?? 0;
                                    $saldoKemasFormatted = $item->formatSaldoPerkemasan($saldoUnit);
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td><span class="badge bg-secondary font-monospace">{{ $item->kode_barang }}</span>
                                    </td>
                                    <td class="fw-bold">{{ $item->nama }}</td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-body border">
                                            1 kemas = {{ $item->jumlah_unit_per_kemasan }} unit
                                        </span>
                                    </td>
                                    <td>
                                        <small class="d-block text-muted">Min: <strong
                                                class="text-danger">{{ $item->jumlah_min }}</strong></small>
                                        <small class="d-block text-muted">Max: <strong
                                                class="text-success">{{ $item->jumlah_max }}</strong></small>
                                    </td>
                                    <td>
                                        <span
                                            class="fw-bold text-primary">{{ number_format($saldoUnit, 0, ',', '.') }}</span>
                                        <small class="text-muted">unit</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-white px-2 py-1">
                                            <i class="bi bi-boxes me-1"></i>{{ $saldoKemasFormatted }}
                                        </span>
                                    </td>
                                    <td>Rp {{ number_format($item->harga_beli_per_unit, 0, ',', '.') }}</td>
                                    <td>
                                        <strong class="text-success">Rp
                                            {{ number_format($item->nilai_persediaan, 0, ',', '.') }}</strong>
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
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        <em>Belum ada barang yang terdaftar. Silakan tambahkan barang terlebih dahulu.</em>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    @endif
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
                    search: "Cari Barang:",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    zeroRecords: "Tidak ditemukan barang yang cocok",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ barang",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 barang",
                    infoFiltered: "(disaring dari _MAX_ total barang)",
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
