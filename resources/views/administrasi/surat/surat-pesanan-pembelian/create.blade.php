@extends('layouts.partial.layouts')

@section('page-title', 'Input Surat Pesanan Pembelian | Digitrans')
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
    <form action="{{route('administrasi.spp.store')}}" method="POST">
        @csrf
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white fw-bold">
                Input Surat Pesanan Pembelian (SPP)
            </div>

            <div class="card-body mt-3">

                {{-- DATA PELANGGAN --}}
                <h6 class="fw-bold">Data Pelanggan</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Pilih Pelanggan</label>
                        <select name="pelanggan_id" id="pelangganSelect" class="form-select" required>
                            <option value="#">-- Pilih Pelanggan --</option>
                            @foreach ($pelanggan as $p)
                                <option value="{{ $p->id }}" data-alamat="{{ $p->alamat }}">
                                    {{ $p->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Alamat Pelanggan</label>
                        <input type="text" id="alamatPelanggan" class="form-control" readonly>
                    </div>
                </div>

                {{-- DATA SPP --}}
                <h6 class="fw-bold mt-4">Informasi SPP</h6>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Nomor SPP</label>
                        <input type="text" name="nomor_pesanan_pembelian" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal SPP</label>
                        <input type="date" name="tanggal_pesanan_pembelian" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rencana Tanggal Kirim</label>
                        <input type="date" name="tanggal_kirim_pesanan_pembelian" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Nama Bagian Pembelian</label>
                        <input type="text" name="nama_bagian_pembelian" class="form-control" required>
                    </div>
                </div>

                {{-- DETAIL BARANG --}}
                <h6 class="fw-bold">Detail Barang</h6>

                <table class="table table-bordered align-middle text-center" id="table-detail">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="30%">Nama Barang</th>
                            <th width="20%">Kuantitas</th>
                            <th width="20%">Harga</th>
                            <th width="35%">Total</th>
                            <th width="5%">#</th>
                        </tr>
                    </thead>

                    <tbody id="tbody-detail">
                        <tr>
                            <td class="row-index text-center">1</td>
                            <td><input type="text" name="detail[0][nama_barang]" class="form-control" required></td>
                            <td><input type="text" name="detail[0][kuantitas]" class="form-control qty" required></td>
                            <td><input type="text" name="detail[0][harga]" class="form-control harga" required></td>
                            <td><input type="text" name="detail[0][total]" class="form-control total" readonly></td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-row">&times;</button></td>
                        </tr>
                    </tbody>
                </table>

                <button type="button" class="btn btn-success btn-sm" id="add-row">
                    + Tambah Barang
                </button>

            </div>

            <div class="card-footer text-end">
                <a href="{{ route('administrasi.spp.index') }}" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    Simpan SPP
                </button>
            </div>

        </div>
    </form>
</div>
@endsection

@push('script')
<script>

// Fungsi format angka menjadi format rupiah (tanpa Rp)
function formatRupiah(angka) {
    let numberString = angka.replace(/[^,\d]/g, '').toString();
    let split = numberString.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/g);

    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
    return rupiah;
}

// Fungsi ambil angka numerik dari input rupiah
function getNumericValue(value) {
    return parseInt(value.replace(/\./g, '').replace(/,/g, '')) || 0;
}

let index = 1;

// Tambah baris
document.getElementById('add-row').addEventListener('click', function () {
    let tbody = document.getElementById('tbody-detail');
    let row = document.createElement('tr');

    row.innerHTML = `
        <td class="row-index text-center">${index + 1}</td>
        <td><input type="text" name="detail[${index}][nama_barang]" class="form-control" required></td>
        <td><input type="text" name="detail[${index}][kuantitas]" class="form-control qty" required></td>
        <td><input type="text" name="detail[${index}][harga]" class="form-control harga" required></td>
        <td><input type="text" name="detail[${index}][total]" class="form-control total" readonly required></td>
        <td><button type="button" class="btn btn-danger btn-sm remove-row">&times;</button></td>
    `;

    tbody.appendChild(row);
    index++;
});

// Format & Hitung Total
document.addEventListener('input', function (e) {
    if (e.target.classList.contains('harga') || e.target.classList.contains('qty')) {
        let val = e.target.value;
        e.target.value = formatRupiah(val);

        let row = e.target.closest('tr');
        let harga = getNumericValue(row.querySelector('.harga').value);
        let qty = getNumericValue(row.querySelector('.qty').value);

        let total = harga * qty;
        row.querySelector('.total').value = total ? formatRupiah(total.toString()) : '';
    }
});

// Hapus baris
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove();
    }
});

// Auto set alamat dari pelanggan
document.getElementById('pelangganSelect').addEventListener('change', function () {
    let alamat = this.options[this.selectedIndex].dataset.alamat ?? "";
    document.getElementById('alamatPelanggan').value = alamat;
});

</script>
@endpush
