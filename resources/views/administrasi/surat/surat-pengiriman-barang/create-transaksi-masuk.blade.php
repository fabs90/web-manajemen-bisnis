@extends('layouts.partial.layouts')
@section('page-title', 'Input Surat Pengiriman Barang (SPB) ke Pelanggan | Digitrans')

@section('section-row')
    <div class="container mt-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <strong>Sukses!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Gagal!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Buat Surat Pengiriman Barang (SPB)</h5>
            </div>

            <div class="card-body py-4">
                <form action="{{ route('administrasi.spb.store') }}" method="POST">
                    @csrf

                    {{-- ── 1. Pilih SPP & Nomor SPB ── --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">
                                Pilih Pesanan Pembelian (SPP) <span class="text-danger">*</span>
                            </label>
                            <select name="spp_id" id="spp_id" class="form-select @error('spp_id') is-invalid @enderror"
                                required>
                                <option value="">-- Pilih Nomor Pesanan --</option>
                                @foreach ($dataSpp as $spp)
                                    @php
                                        $pelangganNama = $spp->pelanggan?->nama ?? 'Pelanggan tidak tersedia';
                                        $pelangganAlamat = $spp->pelanggan?->alamat ?? '-';
                                        $detailsJson = $spp->pesananPembelianDetail
                                            ->map(
                                                fn($d) => [
                                                    'id' => $d->id,
                                                    'nama_barang' => $d->nama_barang,
                                                    'kuantitas' => $d->kuantitas,
                                                    'satuan' => $d->satuan ?? '',
                                                ],
                                            )
                                            ->toJson();
                                    @endphp
                                    <option value="{{ $spp->id }}" data-pelanggan="{{ $pelangganNama }}"
                                        data-alamat="{{ $pelangganAlamat }}"
                                        data-nomor="{{ $spp->nomor_pesanan_pembelian }}"
                                        data-tanggal="{{ $spp->tanggal_kirim_pesanan_pembelian }}"
                                        data-details="{{ $detailsJson }}">
                                        {{ $spp->nomor_pesanan_pembelian }} – {{ $pelangganNama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('spp_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                Nomor SPB <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nomor_pengiriman_barang"
                                value="{{ old('nomor_pengiriman_barang') }}"
                                class="form-control @error('nomor_pengiriman_barang') is-invalid @enderror" required>
                            @error('nomor_pengiriman_barang')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    {{-- ── 2. Info Pelanggan (auto-fill) ── --}}
                    <div class="alert alert-info mb-4">
                        <strong>Kepada&nbsp;&nbsp;:</strong> <span id="kepada">-</span><br>
                        <strong>Alamat&nbsp;&nbsp;&nbsp;:</strong> <span id="alamat">-</span><br>
                        <strong>No. Pesanan:</strong> <span id="nomor_pesanan">-</span>
                    </div>

                    {{-- ── 3. Tanggal Pengiriman & Status ── --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Tanggal Pengiriman <span class="text-danger">*</span>
                            </label>
                            <input type="date" id="tanggal_pengiriman" name="tanggal_pengiriman"
                                value="{{ old('tanggal_pengiriman') }}"
                                class="form-control @error('tanggal_pengiriman') is-invalid @enderror" required>
                            @error('tanggal_pengiriman')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Status Pengiriman <span class="text-danger">*</span>
                            </label>
                            <select name="status_pengiriman" id="status_pengiriman"
                                class="form-select @error('status_pengiriman') is-invalid @enderror" required>
                                @php
                                    $statusList = [
                                        'diproses' => '🕐 Diproses',
                                        'dikirim' => '🚚 Dikirim',
                                        'diterima' => '✅ Diterima',
                                        'dibatalkan' => '❌ Dibatalkan',
                                        'dikembalikan' => '↩️ Dikembalikan',
                                    ];
                                @endphp
                                @foreach ($statusList as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('status_pengiriman', 'diproses') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status_pengiriman')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    {{-- ── 4. Jenis Pengiriman & Pengirim ── --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Jenis Pengiriman <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="jenis_pengiriman" value="{{ old('jenis_pengiriman') }}"
                                class="form-control @error('jenis_pengiriman') is-invalid @enderror"
                                placeholder="Contoh: Darat (JNT), Kargo" required>
                            @error('jenis_pengiriman')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Pengirim / Supir</label>
                            <input type="text" name="nama_pengirim" value="{{ old('nama_pengirim') }}"
                                class="form-control">
                        </div>
                    </div>

                    {{-- ── 5. Tabel Barang ── --}}
                    <h6 class="fw-bold text-primary mb-3">Barang yang Dikirim (Sesuai Pesanan)</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah Dipesan</th>
                                    <th width="180px">Jumlah Dikirim</th>
                                    <th width="90px">Sesuai</th>
                                </tr>
                            </thead>
                            <tbody id="tabel-barang">
                                <tr>
                                    <td colspan="5" class="text-center text-muted fst-italic">
                                        Pilih SPP terlebih dahulu...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- ── 6. Section Konfirmasi (muncul saat diterima / dikembalikan) ── --}}
                    <div id="section-konfirmasi" class="card mb-4" style="display:none;">
                        <div class="card-header" id="konfirmasi-header">
                            <h6 class="mb-0" id="konfirmasi-judul"></h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Tanggal Terima</label>
                                    <input type="date" name="tanggal_terima" id="tanggal_terima"
                                        value="{{ old('tanggal_terima') }}"
                                        class="form-control @error('tanggal_terima') is-invalid @enderror">
                                    @error('tanggal_terima')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Keadaan Barang</label>
                                    <select name="keadaan" id="keadaan"
                                        class="form-select @error('keadaan') is-invalid @enderror">
                                        <option value="">-- Pilih Keadaan --</option>
                                        <option value="baik" {{ old('keadaan') === 'baik' ? 'selected' : '' }}>
                                            Baik</option>
                                        <option value="rusak_ringan"
                                            {{ old('keadaan') === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                        <option value="rusak_berat"
                                            {{ old('keadaan') === 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                                    </select>
                                    @error('keadaan')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Nama Penerima</label>
                                    <input type="text" name="nama_penerima" id="nama_penerima"
                                        value="{{ old('nama_penerima') }}"
                                        class="form-control @error('nama_penerima') is-invalid @enderror">
                                    @error('nama_penerima')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── 7. Keterangan ── --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">Keterangan <span
                                class="text-muted fw-normal">(Opsional)</span></label>
                        <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan') }}</textarea>
                    </div>

                    {{-- ── 8. Action Button ── --}}
                    <div class="text-end">
                        <a href="{{ route('administrasi.spb.index') }}" class="btn btn-secondary me-2">
                            Kembali
                        </a>
                        <button type="submit" class="btn btn-primary px-5">
                            Simpan SPB
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        // ── Konstanta ────────────────────────────────────────────────────────
        const STATUS_KONFIRMASI = ['diterima', 'dikembalikan'];

        const konfirmasiConfig = {
            diterima: {
                judul: '✅ Konfirmasi Penerimaan Barang',
                border: 'border-success',
                header: 'bg-success text-white',
            },
            dikembalikan: {
                judul: '↩️ Detail Pengembalian Barang',
                border: 'border-warning',
                header: 'bg-warning text-dark',
            },
        };

        // ── Elemen ───────────────────────────────────────────────────────────
        const statusEl = document.getElementById('status_pengiriman');
        const sectionEl = document.getElementById('section-konfirmasi');
        const headerEl = document.getElementById('konfirmasi-header');
        const judulEl = document.getElementById('konfirmasi-judul');
        const tglTerima = document.getElementById('tanggal_terima');
        const keadaanEl = document.getElementById('keadaan');
        const namaPenerimaEl = document.getElementById('nama_penerima');

        // ── Toggle section konfirmasi ────────────────────────────────────────
        function toggleKonfirmasi() {
            const status = statusEl.value;
            const butuhKonfirmasi = STATUS_KONFIRMASI.includes(status);

            sectionEl.style.display = butuhKonfirmasi ? 'block' : 'none';

            // required dinamis
            tglTerima.required = butuhKonfirmasi;
            keadaanEl.required = butuhKonfirmasi;
            namaPenerimaEl.required = butuhKonfirmasi;

            if (butuhKonfirmasi) {
                const cfg = konfirmasiConfig[status];

                // Reset class border
                sectionEl.className = `card mb-4 ${cfg.border}`;
                headerEl.className = `card-header ${cfg.header}`;
                judulEl.textContent = cfg.judul;
            }
        }

        statusEl.addEventListener('change', toggleKonfirmasi);
        toggleKonfirmasi(); // jalankan saat load untuk handle old() value

        // ── SPP Change ───────────────────────────────────────────────────────
        document.getElementById('spp_id').addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];

            if (!this.value) {
                document.getElementById('kepada').textContent = '-';
                document.getElementById('alamat').textContent = '-';
                document.getElementById('nomor_pesanan').textContent = '-';
                document.getElementById('tanggal_pengiriman').value = '';
                document.getElementById('tabel-barang').innerHTML =
                    `<tr><td colspan="5" class="text-center text-muted fst-italic">Pilih SPP terlebih dahulu...</td></tr>`;
                return;
            }

            document.getElementById('kepada').textContent = opt.dataset.pelanggan;
            document.getElementById('alamat').textContent = opt.dataset.alamat;
            document.getElementById('nomor_pesanan').textContent = opt.dataset.nomor;
            document.getElementById('tanggal_pengiriman').value = opt.dataset.tanggal ?? '';

            const details = JSON.parse(opt.dataset.details || '[]');
            let rows = '';

            details.forEach((item, i) => {
                rows += `
                <tr>
                    <td class="text-center">${i + 1}</td>
                    <td>
                        ${item.nama_barang}
                        <input type="hidden" name="items[${i}][spp_detail_id]" value="${item.id}">
                    </td>
                    <td class="text-center">${item.kuantitas} ${item.satuan}</td>
                    <td class="text-center">
                        <input type="number"
                            class="form-control form-control-sm text-center jumlah-dikirim"
                            name="items[${i}][jumlah_dikirim]"
                            min="0"
                            value="${item.kuantitas}">
                    </td>
                    <td class="text-center">
                        <input type="checkbox"
                            class="form-check-input checkbox-sesuai"
                            checked
                            data-index="${i}"
                            data-kuantitas="${item.kuantitas}">
                    </td>
                </tr>`;
            });

            document.getElementById('tabel-barang').innerHTML = rows;

            document.querySelectorAll('.checkbox-sesuai').forEach(chk => {
                chk.addEventListener('change', function() {
                    const input = document.querySelector(
                        `input[name="items[${this.dataset.index}][jumlah_dikirim]"]`
                    );
                    if (this.checked) {
                        input.value = this.dataset.kuantitas;
                        input.setAttribute('readonly', true);
                    } else {
                        input.removeAttribute('readonly');
                    }
                });
                chk.dispatchEvent(new Event('change'));
            });
        });
    </script>
@endsection
