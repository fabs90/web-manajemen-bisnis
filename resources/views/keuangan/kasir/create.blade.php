@extends('layouts.partial.layouts')
@section('page-title', 'Kasir | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Kasir Penjualan')
@section('section-row')
    <!-- Loading Overlay -->
    <div id="page-loader">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h5 class="mt-3 fw-bold text-secondary">Memuat Kasir...</h5>
    </div>

    <div class="card shadow-sm kasir-card">
        <div class="card-body">
            {{-- Tombol Back --}}
            <div class="mb-3">
                <a href="{{ route('keuangan.kasir.index') }}" class="btn btn-secondary btn-sm">
                    ⬅ Kembali
                </a>
            </div>

            <form action="{{ route('keuangan.kasir.store') }}" method="POST">
                @csrf

                <div class="row">
                    {{-- 🔹 Area Barang --}}
                    <div class="col-lg-8">
                        <div class="row align-items-start mb-3">
                            <div class="col-md-7">
                                <label><strong>Pilih Barang</strong></label>
                                <select class="form-select mb-1" id="select-barang" onchange="updateStokInfo(this)">
                                    <option disabled selected>-- Pilih barang --</option>
                                    @foreach ($barang as $item)
                                        <option value="{{ $item->id }}" data-nama="{{ $item->nama }}"
                                            data-stok="{{ $item->latestKartuGudang ? number_format($item->latestKartuGudang->saldo_persatuan, 0, '.', '') : '0' }}"
                                            data-harga="{{ $item->harga_jual_per_unit }}">
                                            {{ $item->nama }} - Rp
                                            {{ number_format($item->harga_jual_per_unit, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="stok-info" class="text-muted fw-bold">
                                    Stok tersedia: <span id="stok-value" class="text-primary">-</span>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label><strong>Jumlah</strong></label>
                                <input type="number" id="qty" class="form-control mb-1" value="1"
                                    min="1">
                            </div>

                            <div class="col-md-3">
                                <label class="d-none d-md-block">&nbsp;</label>
                                <button type="button" id="btn-tambah" class="btn btn-success w-100 btn-action mb-1">
                                    <i class="bi bi-plus-circle"></i> Tambah
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table keranjang-table table-bordered table-striped" id="keranjang-table">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th style="min-width: 150px;">Barang</th>
                                        <th style="min-width: 80px;">Qty</th>
                                        <th style="min-width: 120px;">Harga</th>
                                        <th style="min-width: 120px;">Subtotal</th>
                                        <th style="min-width: 60px;">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- 🔹 Area Pembayaran --}}
                    <div class="col-lg-4">

                        <label><strong>Jenis Pembayaran</strong></label>
                        <select name="jenis_pembayaran_id" id="jenis_pembayaran_id" class="form-select mb-3" required>
                            <option value="" disabled selected>-- Pilih Jenis Pembayaran --</option>
                            @if (!$jenisPembayaran || $jenisPembayaran->isEmpty())
                                <option value="1" data-nama="tunai">Tunai</option>
                                <option value="2" data-nama="transfer_bank">Transfer Bank</option>
                                <option value="3" data-nama="qris">QRIS</option>
                            @else
                                @foreach ($jenisPembayaran as $jp)
                                    <option value="{{ $jp->id }}" data-nama="{{ strtolower($jp->nama) }}">
                                        {{ $jp->nama }}</option>
                                @endforeach
                            @endif
                        </select>

                        <div id="qris-container" class="mt-2 mb-3 text-center d-none">
                            <label class="fw-bold d-block mb-1">Pindai QRIS untuk Pembayaran</label>
                            @if (auth()->user()->qris_image && storage_resolve_path(auth()->user()->qris_image))
                                <img src="{{ route('qris.image', ['v' => auth()->user()->updated_at?->timestamp ?? time()]) }}"
                                    alt="QRIS" class="img-fluid border p-2" style="max-height: 250px;">
                            @else
                                <div class="alert alert-warning py-2 small">
                                    <i class="fas fa-exclamation-circle me-1"></i> QRIS belum diatur. <a
                                        href="{{ route('qris.index') }}" class="fw-bold">Atur di sini</a>.
                                </div>
                            @endif
                        </div>

                        <label><strong>Pilih Paket Diskon</strong> <span class="text-muted">(Opsional)</span></label>
                        <select name="paket_diskon_id" id="paket_diskon_id" class="form-select mb-3">
                            <option value="">-- Tanpa Diskon --</option>
                            @foreach ($paketDiskons as $pd)
                                <option value="{{ $pd->id }}" data-jenis="{{ $pd->jenis_diskon }}"
                                    data-nilai="{{ $pd->nilai_diskon }}" data-minimal="{{ $pd->minimal_pembelian }}"
                                    data-barang="{{ $pd->barang_id }}">
                                    {{ $pd->nama_paket }}
                                    ({{ $pd->jenis_diskon == 'persentase' ? round($pd->nilai_diskon) . '%' : 'Rp ' . number_format($pd->nilai_diskon, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>

                        <label><strong>Total Diskon</strong></label>
                        <div class="total-box mb-3" id="diskon-box" style="color: #ff9800; font-size: 20px;">- Rp 0</div>
                        <input type="hidden" name="diskon_total" id="diskon-total-value" value="0">

                        <label><strong>Total Bayar</strong></label>
                        <div class="total-box mb-3" id="grand-total">Rp 0</div>
                        <input type="hidden" name="grand_total" id="grand-total-value">
                        <label class="fw-bold">Uang Dibayar</label>
                        <input type="text" name="uang_bayar" id="bayar" class="form-control rupiah mb-1"
                            placeholder="Masukkan uang" autocomplete="off">
                        <small class="text-muted d-none mb-3 d-block" id="info-bayar-otomatis">Otomatis lunas sesuai total bayar</small>

                        <label class="fw-bold mt-2">Kembalian</label>
                        <input type="text" id="kembalian" class="form-control mb-3" readonly>
                        <input type="hidden" name="uang_kembalian" id="kembalian-value">


                        <button type="submit" class="btn btn-primary w-100 btn-action" id="btn-simpan" disabled>
                            💾 Simpan Transaksi
                        </button>

                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- Kasir Metadata untuk script eksternal kasir.js --}}
    <div id="kasir-metadata" class="d-none"
        data-success="{{ session('success') ?? '' }}"
        data-error="{{ session('error') ?? '' }}"
        data-printer-enabled="{{ auth()->user()?->is_printer_enabled ? '1' : '0' }}"
        data-printer-url="{{ asset('dist/assets/pos-printer.js') }}">
        @if (session('receipt'))
            <script type="application/json" id="kasir-receipt-json">@json(session('receipt'))</script>
        @endif
    </div>
@endsection

