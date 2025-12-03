@extends('layouts.partial.layouts')
@section('page-title', 'Buat Faktur Penjualan')

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
        <h5 class="mb-0">Buat Faktur Penjualan</h5>
    </div>

    <div class="card-body py-4">
        <form action="{{ route('administrasi.faktur-penjualan.store') }}" method="POST">
            @csrf

            <!-- Pilih SPB -->
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Pilih Surat Pengiriman Barang (SPB) <span class="text-danger">*</span></label>
                    <select name="spb_id" id="spb_id"
                            class="form-select @error('spb_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Nomor SPB --</option>
                        @foreach($dataSpb as $spb)
                            @php
                                $detailsJson = $spb->suratPengirimanBarangDetail->map(fn($d) => [
                                    'id' => $d->id,
                                    'nama_barang' => $d->pesananPembelianDetail->nama_barang,
                                    'jumlah_dipesan' => $d->pesananPembelianDetail->kuantitas,
                                    'jumlah_dikirim' => $d->jumlah_dikirim,
                                    'harga' => $d->pesananPembelianDetail->harga
                                ])->toJson();
                            @endphp
                            <option value="{{ $spb->id }}"
                                data-pelanggan="{{ $spb->pesananPembelian->pelanggan->nama }}"
                                data-alamat="{{ $spb->pesananPembelian->pelanggan->alamat ?? '-' }}"
                                data-nomor="{{ $spb->nomor_pengiriman_barang }}"
                                  data-nama_pengirim="{{ $spb->nama_pengirim }}"
                                data-details="{{ $detailsJson }}">
                                {{ $spb->nomor_pengiriman_barang }} - {{ $spb->pesananPembelian->pelanggan->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('spb_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <!-- Nomor Faktur -->
                <div class="col-md-4">
                    <label class="form-label fw-bold">Kode Faktur <span class="text-danger">*</span></label>
                    <input type="text" name="kode_faktur"
                           class="form-control @error('kode_faktur') is-invalid @enderror"
                           value="{{ old('kode_faktur') }}" required>
                           @error('kode_faktur') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            <!-- Informasi Pelanggan -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-person-lines-fill me-1"></i> Informasi Pelanggan
                    </h6>

                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <div class="small text-muted">Kepada</div>
                            <div class="fw-semibold" id="kepada">-</div>
                        </div>

                        <div class="col-md-5 mb-2">
                            <div class="small text-muted">Alamat</div>
                            <div class="fw-semibold" id="alamat">-</div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="small text-muted">Nomor SPB</div>
                            <div class="fw-semibold" id="nomor_spb">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tanggal Faktur -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tanggal Faktur <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_faktur" class="form-control"
                           value="{{ old('tanggal_faktur', date('Y-m-d')) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Bagian Penjualan <span class="text-danger">*</span></label>
                    <input type="text" name="bagian_penjualan" class="form-control"
                           value="{{ old('bagian_penjualan') }}" required>
                </div>

            </div>

            <!-- Tabel Barang -->
            <h6 class="fw-bold text-primary mb-3">Detail Barang Faktur</h6>

            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm">
                    <thead class="table-light text-center">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Dipesan</th>
                            <th>Dikirim</th>
                            <th>Harga</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="tabel-faktur">
                        <tr><td colspan="6" class="text-center text-muted">Pilih SPB terlebih dahulu...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Total -->
            <div class="text-end mb-4">
                <h5>Total Faktur: <strong>Rp <span id="grand_total">0</span></strong></h5>
            </div>

            <div class="mt-4 text-end">
                <a href="{{ route('administrasi.faktur-penjualan.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary px-5">Simpan Faktur</button>
            </div>
        </form>
    </div>
</div>
</div>

<script>
document.getElementById('spb_id').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];

    if (!this.value) {
        document.getElementById('kepada').textContent = '-';
        document.getElementById('alamat').textContent = '-';
        document.getElementById('nomor_spb').textContent = '-';
        document.getElementById('tabel-faktur').innerHTML =
            `<tr><td colspan="6" class="text-center text-muted">Pilih SPB terlebih dahulu...</td></tr>`;
        return;
    }

    document.getElementById('kepada').textContent = opt.dataset.pelanggan;
    document.getElementById('alamat').textContent = opt.dataset.alamat;
    document.getElementById('nomor_spb').textContent = opt.dataset.nomor;
    document.querySelector('input[name="bagian_penjualan"]').value = opt.dataset.nama_pengirim || '';

    const details = JSON.parse(opt.dataset.details || '[]');
    let rows = '';
    let grandTotal = 0;

    details.forEach((item, i) => {
        const total = item.harga * item.jumlah_dikirim;
        grandTotal += total;

        rows += `
            <tr>
                <td class="text-center">${i+1}</td>
                <td>${item.nama_barang}</td>
                <td class="text-center">${item.jumlah_dipesan}</td>
                <td class="text-center">${item.jumlah_dikirim}</td>
                <td class="text-end">Rp ${item.harga.toLocaleString()}</td>
                <td class="text-end">Rp ${total.toLocaleString()}</td>

                <input type="hidden" name="items[${i}][spb_detail_id]" value="${item.id}">
                <input type="hidden" name="items[${i}][harga]" value="${item.harga}">
                <input type="hidden" name="items[${i}][total]" value="${total}">
            </tr>
        `;
    });

    document.getElementById('tabel-faktur').innerHTML = rows;
    document.getElementById('grand_total').textContent = grandTotal.toLocaleString();
});
</script>

@endsection
