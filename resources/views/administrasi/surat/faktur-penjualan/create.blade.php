@extends('layouts.partial.layouts')

@section('page-title', 'Input Faktur Penjualan | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-row')
<div class="container mt-4">
    {{-- Alert Error --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Gagal!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <form action="{{route('administrasi.faktur-penjualan.store')}}" method="POST">
        @csrf

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white fw-bold">
                Input Faktur Penjualan
            </div>

            <div class="card-body mt-3">
                {{-- DATA PENERIMA --}}
                <h6 class="fw-bold">Data Penerima</h6>
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
                        <label class="form-label">Alamat Pembeli</label>
                        <input type="text" name="alamat_pembeli" id="alamatPembeli"
                               class="form-control" readonly required>
                    </div>
                </div>
                {{-- DATA SURAT --}}
                <h6 class="fw-bold mt-4">Informasi Surat</h6>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Kode Faktur</label>
                        <input type="text" name="kode_faktur" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nomor Pesanan</label>
                        <input type="text" name="nomor_pesanan" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nomor SPB</label>
                        <input type="text" name="nomor_spb" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Jenis Pengiriman</label>
                        <input type="text" name="jenis_pengiriman" class="form-control" placeholder="Via apa?" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nama Bagian Penjualan</label>
                        <input type="text" name="nama_bagian_penjualan" class="form-control" required>
                    </div>
                </div>

                {{-- DETAIL BARANG DINAMIS --}}
                <h6 class="fw-bold">Detail Barang</h6>

                <table class="table table-bordered align-middle text-center" id="table-detail">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Jumlah Dipesan</th>
                            <th width="15%">Jumlah Dikirim</th>
                            <th>Nama Barang</th>
                            <th width="15%">Harga</th>
                            <th width="15%">Total</th>
                            <th width="5%">#</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-detail">
                        <tr>
                            <td class="row-index text-center">1</td>
                            <td><input type="text" step="0.01" name="detail[0][jumlah_dipesan]" class="form-control jumlah-dipesan" required></td>
                            <td><input type="text" step="0.01" name="detail[0][jumlah_dikirim]" class="form-control jumlah-dikirim" required></td>
                            <td><input type="text" name="detail[0][nama_barang]" class="form-control" required></td>
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
                <a href="{{ route('administrasi.faktur-penjualan.index') }}" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    Simpan Faktur Penjualan
                </button>
            </div>

        </div>
    </form>
</div>
@endsection

@push('script')
<script>
let index = 1;

// Konfigurasi AutoNumeric
const autoNumOptions = {
    digitGroupSeparator: '.',
    decimalCharacter: ',',
    decimalPlaces: 0,
    unformatOnSubmit: true
};

// Fungsi aktifkan AutoNumeric pada row
function initAutoNumeric(row) {
    new AutoNumeric(row.querySelector('.jumlah-dipesan'), autoNumOptions);
    new AutoNumeric(row.querySelector('.jumlah-dikirim'), autoNumOptions);
    new AutoNumeric(row.querySelector('.harga'), autoNumOptions);
    new AutoNumeric(row.querySelector('.total'), autoNumOptions);
}

// Reset nomor urut dan update index name setelah hapus row
function resetRowIndex() {
    let rows = document.querySelectorAll("#tbody-detail tr");
    rows.forEach((row, i) => {
        row.querySelector(".row-index").textContent = i + 1;

        row.querySelectorAll("input").forEach(input => {
            let name = input.getAttribute("name");
            let field = name.substring(name.indexOf("][") + 2, name.lastIndexOf("]"));
            input.setAttribute("name", `detail[${i}][${field}]`);
        });
    });

    index = rows.length ? rows.length : 1;
}

// Inisialisasi row pertama
initAutoNumeric(document.querySelector('#tbody-detail tr'));

// Tambah row
document.getElementById('add-row').addEventListener('click', function () {
    let tbody = document.getElementById('tbody-detail');
    let row = document.createElement('tr');

    row.innerHTML = `
        <td class="row-index text-center">${index + 1}</td>
        <td><input type="text" name="detail[${index}][jumlah_dipesan]" class="form-control jumlah-dipesan" required></td>
        <td><input type="text" name="detail[${index}][jumlah_dikirim]" class="form-control jumlah-dikirim" required></td>
        <td><input type="text" name="detail[${index}][nama_barang]" class="form-control" required></td>
        <td><input type="text" name="detail[${index}][harga]" class="form-control harga" required></td>
        <td><input type="text" name="detail[${index}][total]" class="form-control total" readonly required></td>
        <td><button type="button" class="btn btn-danger btn-sm remove-row">&times;</button></td>
    `;

    tbody.appendChild(row);
    initAutoNumeric(row);
    index++;
});

// Hitung total otomatis
document.addEventListener('input', function (event) {
    if (event.target.classList.contains('harga') ||
        event.target.classList.contains('jumlah-dikirim')) {

        let row = event.target.closest('tr');
        let harga = AutoNumeric.getNumber(row.querySelector('.harga')) || 0;
        let qty = AutoNumeric.getNumber(row.querySelector('.jumlah-dikirim')) || 0;

        AutoNumeric.set(row.querySelector('.total'), harga * qty);
    }
});

// Hapus row + reset index
document.addEventListener('click', function (event) {
    if (event.target.classList.contains('remove-row')) {
        event.target.closest('tr').remove();
        resetRowIndex();
    }
});

document.getElementById('pelangganSelect').addEventListener('change', function () {
    let alamat = this.options[this.selectedIndex].dataset.alamat ?? "";
    document.getElementById('alamatPembeli').value = alamat;
});
</script>
@endpush
