@extends('layouts.partial.layouts')
@section('page-title', 'Edit Surat Pengiriman Barang (SPB) | TRANSDIGITAL')

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
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Surat Pengiriman Barang (SPB)</h5>
            </div>

            <div class="card-body py-4">
                <form action="{{ route('administrasi.spb.update', ['id' => $dataSpb->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Pesanan Pembelian (SPP)</label>
                            <input type="hidden" name="spp_id" value="{{ $dataSpb->pesanan_penjualan_id }}">
                            @php
                                $pp = $dataSpb->pesananPenjualan;
                                $pelangganNama = $pp->pelanggan->nama ?? $pp->supplier->nama ?? '-';
                                $pelangganAlamat = $pp->pelanggan->alamat ?? $pp->supplier->alamat ?? '-';
                                $nomorPesanan = $pp->nomor_pesanan_penjualan ?? $pp->nomor_pesanan_pembelian ?? '-';
                            @endphp
                            <input type="text" class="form-control bg-light"
                                value="{{ $nomorPesanan }} – {{ $pelangganNama }}" readonly>
                            <small class="text-muted">SPP tidak dapat diubah setelah SPB dibuat.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                Nomor SPB
                            </label>
                            <input type="text" name="nomor_pengiriman_barang"
                                value="{{ $dataSpb->nomor_pengiriman_barang }}" class="form-control bg-light" required
                                readonly>
                        </div>
                    </div>

                    <div class="alert alert-info mb-4">
                        <strong>Kepada&nbsp;&nbsp;:</strong> {{ $pelangganNama }}<br>
                        <strong>Alamat&nbsp;&nbsp;&nbsp;:</strong> {{ $pelangganAlamat }}<br>
                        <strong>No. Pesanan:</strong> {{ $nomorPesanan }}
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Tanggal Pengiriman <span class="text-danger">*</span>
                            </label>
                            @php
                                $tglKirim = $pp->tanggal_kirim_pesanan_penjualan ?? $pp->tanggal_pesanan_penjualan;
                                $tglKirimFormatted = $tglKirim ? \Carbon\Carbon::parse($tglKirim)->format('Y-m-d') : '';
                            @endphp
                            <input type="date" name="tanggal_pengiriman"
                                value="{{ $tglKirimFormatted }}"
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
                                    $currentStatus = old('status_pengiriman', $dataSpb->status_pengiriman);
                                @endphp
                                @foreach ($statusList as $val => $label)
                                    <option value="{{ $val }}" {{ $currentStatus === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status_pengiriman')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Jenis Pengiriman <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="jenis_pengiriman"
                                value="{{ old('jenis_pengiriman', $dataSpb->jenis_pengiriman) }}"
                                class="form-control @error('jenis_pengiriman') is-invalid @enderror"
                                placeholder="Contoh: Darat (JNT), Kargo" required>
                            @error('jenis_pengiriman')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Pengirim / Supir</label>
                            <input type="text" name="nama_pengirim"
                                value="{{ old('nama_pengirim', $dataSpb->nama_pengirim) }}" class="form-control mb-2">

                            <label class="form-label fw-bold small">Tanda Tangan Pengirim (Opsional)</label>
                            <input type="file" name="ttd_pengirim"
                                class="form-control @error('ttd_pengirim') is-invalid @enderror" accept="image/*">
                            @if($dataSpb->ttd_pengirim)
                                <div class="mt-2 mb-1">
                                    <img src="{{ Storage::url($dataSpb->ttd_pengirim) }}" alt="TTD Pengirim" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                                <small class="text-success">Sudah ada tanda tangan. Unggah lagi untuk mengganti.</small>
                            @endif
                        </div>
                    </div>

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
                                @forelse ($dataSpb->suratPengirimanBarangDetail as $i => $detail)
                                    @php
                                        $sppDetail = $detail->pesananPenjualanDetail ?? $detail->pesananPembelianDetail;
                                        $kuantitasDipesan = $sppDetail->kuantitas ?? 0;
                                        $satuan = $sppDetail->satuan ?? '';
                                        $jumlahDikirim = old("items.{$i}.jumlah_dikirim", $detail->jumlah_dikirim);
                                        $isSesuai = (int) $jumlahDikirim === (int) $kuantitasDipesan;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $i + 1 }}</td>
                                        <td>
                                            {{ $sppDetail->nama_barang ?? '-' }}
                                            <input type="hidden" name="items[{{ $i }}][spp_detail_id]"
                                                value="{{ $detail->spp_detail_id ?? $sppDetail->id }}">
                                            <input type="hidden" name="items[{{ $i }}][spb_detail_id]"
                                                value="{{ $detail->id }}">
                                        </td>
                                        <td class="text-center">{{ $kuantitasDipesan }} {{ $satuan }}</td>
                                        <td class="text-center">
                                            <input type="number"
                                                class="form-control form-control-sm text-center jumlah-dikirim"
                                                name="items[{{ $i }}][jumlah_dikirim]" min="0"
                                                value="{{ $jumlahDikirim }}" {{ $isSesuai ? 'readonly' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input checkbox-sesuai"
                                                {{ $isSesuai ? 'checked' : '' }} data-index="{{ $i }}"
                                                data-kuantitas="{{ $kuantitasDipesan }}">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted fst-italic">
                                            Tidak ada detail barang.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div id="section-konfirmasi" class="card mb-4" style="display:none;">
                        <div class="card-header" id="konfirmasi-header">
                            <h6 class="mb-0" id="konfirmasi-judul"></h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Tanggal Terima</label>
                                    <input type="date" name="tanggal_terima" id="tanggal_terima"
                                        value="{{ old('tanggal_terima', $dataSpb->tanggal_terima) }}"
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
                                        @foreach (['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat'] as $val => $label)
                                            <option value="{{ $val }}"
                                                {{ old('keadaan', $dataSpb->keadaan) === $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('keadaan')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Nama Penerima</label>
                                    <input type="text" name="nama_penerima" id="nama_penerima"
                                        value="{{ old('nama_penerima', $dataSpb->nama_penerima) }}"
                                        class="form-control mb-2 @error('nama_penerima') is-invalid @enderror">
                                    @error('nama_penerima')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                    <label class="form-label fw-bold small">Tanda Tangan Penerima (Opsional)</label>
                                    <input type="file" name="ttd_penerima" id="ttd_penerima"
                                        class="form-control @error('ttd_penerima') is-invalid @enderror" accept="image/*">
                                    @if($dataSpb->ttd_penerima)
                                        <div class="mt-2 mb-1">
                                            <img src="{{ Storage::url($dataSpb->ttd_penerima) }}" alt="TTD Penerima" class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                        <small class="text-success">Sudah ada tanda tangan. Unggah lagi untuk mengganti.</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            Keterangan <span class="text-muted fw-normal">(Opsional)</span>
                        </label>
                        <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $dataSpb->keterangan) }}</textarea>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('administrasi.spb.index') }}" class="btn btn-secondary me-2">
                            Kembali
                        </a>
                        <button type="submit" class="btn btn-primary px-5 text-white fw-bold">
                            Update SPB
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
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

        const statusEl = document.getElementById('status_pengiriman');
        const sectionEl = document.getElementById('section-konfirmasi');
        const headerEl = document.getElementById('konfirmasi-header');
        const judulEl = document.getElementById('konfirmasi-judul');
        const tglTerima = document.getElementById('tanggal_terima');
        const keadaanEl = document.getElementById('keadaan');
        const namaPenerimaEl = document.getElementById('nama_penerima');

        function toggleKonfirmasi() {
            const status = statusEl.value;
            const butuhKonfirmasi = STATUS_KONFIRMASI.includes(status);

            sectionEl.style.display = butuhKonfirmasi ? 'block' : 'none';

            tglTerima.required = butuhKonfirmasi;
            keadaanEl.required = butuhKonfirmasi;
            namaPenerimaEl.required = butuhKonfirmasi;

            if (butuhKonfirmasi) {
                const cfg = konfirmasiConfig[status];
                sectionEl.className = `card mb-4 ${cfg.border}`;
                headerEl.className = `card-header ${cfg.header}`;
                judulEl.textContent = cfg.judul;
            }
        }

        statusEl.addEventListener('change', toggleKonfirmasi);
        toggleKonfirmasi();

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
    </script>
@endsection
