@extends('layouts.partial.layouts')
@section('page-title', 'Buat Surat Pengiriman Barang (SPB)')

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
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Buat Surat Pengiriman Barang (SPB)</h5>
        </div>

        <div class="card-body py-4">
            <form action="{{ route('administrasi.spb.store') }}" method="POST">
                @csrf

                <!-- Pilih SPP & Nomor SPB -->
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Pilih Pesanan Pembelian (SPP) <span class="text-danger">*</span></label>
                        <select name="spp_id" id="spp_id" class="form-select @error('spp_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Nomor Pesanan --</option>
                            @foreach($dataSpp as $spp)
                                @php
                                    $detailsJson = $spp->pesananPembelianDetail->map(fn($d) => [
                                        'id' => $d->id,
                                        'nama_barang' => $d->nama_barang,
                                        'kuantitas' => $d->kuantitas,
                                        'satuan' => $d->satuan ?? ''
                                    ])->toJson();
                                @endphp
                                <option value="{{ $spp->id }}"
                                    data-pelanggan="{{ $spp->pelanggan->nama }}"
                                    data-alamat="{{ $spp->pelanggan->alamat ?? '-' }}"
                                    data-nomor="{{ $spp->nomor_pesanan_pembelian }}"
                                    data-details="{{ $detailsJson }}">
                                    {{ $spp->nomor_pesanan_pembelian }} - {{ $spp->pelanggan->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('spp_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nomor SPB <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_pengiriman_barang" class="form-control @error('nomor_pengiriman_barang') is-invalid @enderror" required>
                        @error('nomor_pengiriman_barang') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <!-- Info Pelanggan Otomatis -->
                <div class="alert alert-info mb-4">
                    <strong>Kepada:</strong> <span id="kepada">-</span><br>
                    <strong>Alamat:</strong> <span id="alamat">-</span><br>
                    <strong>Nomor Pesanan:</strong> <span id="nomor_pesanan">-</span>
                </div>

                <!-- Tanggal Pengiriman & Diterima -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tanggal Pengiriman <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_pengiriman" class="form-control" value="{{ old('tanggal_pengiriman', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tanggal Diterima <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_diterima" class="form-control" value="{{ old('tanggal_diterima', date('Y-m-d')) }}" required>
                    </div>
                </div>

                <!-- Jenis Pengiriman -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Jenis Pengiriman <span class="text-danger">*</span></label>
                        <input type="text" name="jenis_pengiriman" class="form-control" value="{{ old('jenis_pengiriman') }}" placeholder="Darat(JNT),Kargo" required>
                        @error('jenis_pengiriman') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nama Pengirim / Supir</label>
                        <input type="text" name="nama_pengirim" class="form-control" value="{{ old('nama_pengirim') }}">
                    </div>
                </div>


                <!-- Tabel Barang (Read-only) -->
                <h6 class="fw-bold text-primary mb-3">Barang yang Dikirim (Sesuai Pesanan)</h6>
                <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm">
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
                        <tr><td colspan="5" class="text-center text-muted">Pilih SPP terlebih dahulu...</td></tr>
                    </tbody>
                </table>

                </div>

                <!-- Input Wajib -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Keadaan Barang <span class="text-danger">*</span></label>
                        <select name="keadaan" class="form-select" required>
                            <option value="baik" {{ old('keadaan') == 'baik' ? 'selected' : '' }}>Baik</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nama Penerima <span class="text-danger">*</span></label>
                        <input type="text" name="nama_penerima" class="form-control" value="{{ old('nama_penerima') }}" required>
                    </div>

                </div>

                <div class="mt-3">
                    <label class="form-label">Keterangan (Opsional)</label>
                    <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan') }}</textarea>
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('administrasi.spb.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary px-5">Simpan SPB</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.getElementById('spp_id').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (!this.value) {
        document.getElementById('kepada').textContent = '-';
        document.getElementById('alamat').textContent = '-';
        document.getElementById('nomor_pesanan').textContent = '-';
        document.getElementById('tabel-barang').innerHTML = `
            <tr><td colspan="5" class="text-center text-muted">Pilih SPP terlebih dahulu...</td></tr>`;
        return;
    }

    document.getElementById('kepada').textContent = opt.dataset.pelanggan;
    document.getElementById('alamat').textContent = opt.dataset.alamat;
    document.getElementById('nomor_pesanan').textContent = opt.dataset.nomor;

    const details = JSON.parse(opt.dataset.details || '[]');
    let rows = '';

    details.forEach((item, i) => {
        rows += `
            <tr>
                <td class="text-center">${i+1}</td>
                <td>
                    ${item.nama_barang}
                    <input type="hidden" name="items[${i}][spp_detail_id]" value="${item.id}">
                </td>
                <td class="text-center">${item.kuantitas} ${item.satuan}</td>
                <td class="text-center">
                    <input type="number"
                        class="form-control text-center jumlah-dikirim"
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

    // Tambahkan event untuk checkbox
    document.querySelectorAll('.checkbox-sesuai').forEach(chk => {
        chk.addEventListener('change', function () {
            const idx = this.dataset.index;
            const qty = this.dataset.kuantitas;
            const input = document.querySelector(`input[name="items[${idx}][jumlah_dikirim]"]`);

            if (this.checked) {
                input.value = qty;
                input.setAttribute('readonly', true);
            } else {
                input.removeAttribute('readonly');
            }
        });

        chk.dispatchEvent(new Event('change')); // Trigger default state
    });
});
</script>

@endsection
