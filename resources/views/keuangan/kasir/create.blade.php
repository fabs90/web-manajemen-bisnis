@extends('layouts.partial.layouts')
@section('page-title', 'Kasir | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Kasir Penjualan')
@section('section-row')

<style>
    .kasir-card { border-radius: 14px; }
    .total-box {
        background: #222;
        color: #0f0;
        font-size: 32px;
        font-weight: bold;
        padding: 15px;
        border-radius: 12px;
        text-align: right;
    }
    #kembalian {
        font-size: 24px;
        font-weight: bold;
        text-align: right;
    }
    #bayar {
        font-size: 22px;
        font-weight: bold;
        text-align: right;
        border: 2px solid #007bff;
    }
    .btn-action {
        font-size: 20px;
        font-weight: bold;
        padding: 14px;
        border-radius: 10px;
    }
    .keranjang-table td, .keranjang-table th {
        font-size: 18px;
        vertical-align: middle;
    }
    .status-kurang {
        color: #b30000 !important;
    }
    .status-cukup {
        color: #008000 !important;
    }
</style>

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
                <div class="col-lg-7">
                    <label><strong>Pilih Barang</strong></label>
                    <select class="form-select form-select-lg mb-3" id="select-barang">
                        <option disabled selected>-- Pilih barang --</option>
                        @foreach ($barang as $item)
                        <option value="{{ $item->id }}"
                            data-nama="{{ $item->nama }}"
                            data-harga="{{ $item->harga_jual_per_unit }}">
                            {{ $item->nama }} - Rp {{ number_format($item->harga_jual_per_unit, 0, ',', '.') }}
                        </option>
                        @endforeach
                    </select>
                    <label><strong>Qty</strong></label>
                    <input type="number" id="qty" class="form-control form-control-lg mb-3"
                        value="1" min="1">

                    <button type="button" id="btn-tambah" class="btn btn-success w-100 btn-action mb-3">
                        ➕ Tambah ke Keranjang
                    </button>

                    <table class="table keranjang-table table-bordered table-striped" id="keranjang-table">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Barang</th>
                                <th width="80px">Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                                <th width="50px">Hapus</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                {{-- 🔹 Area Pembayaran --}}
                <div class="col-lg-5">

                    <label><strong>Jenis Pembayaran</strong></label>
                    <select name="jenis_pembayaran_id" id="jenis_pembayaran_id" class="form-select form-select-lg mb-3" required>
                        <option value="" disabled selected>-- Pilih Jenis Pembayaran --</option>
                        @foreach ($jenisPembayaran as $jp)
                            <option value="{{ $jp->id }}" data-nama="{{ strtolower($jp->nama) }}">{{ $jp->nama }}</option>
                        @endforeach
                    </select>

                    <div id="qris-container" class="mt-2 mb-3 text-center d-none">
                        <label class="fw-bold d-block mb-1">Pindai QRIS untuk Pembayaran</label>
                        @if(auth()->user()->qris_image)
                            <img src="{{ asset('storage/' . auth()->user()->qris_image) }}" alt="QRIS" class="img-fluid border p-2" style="max-height: 250px;">
                        @else
                            <div class="alert alert-warning py-2 small">
                                <i class="fas fa-exclamation-circle me-1"></i> QRIS belum diatur. <a href="{{ route('qris.index') }}" class="fw-bold">Atur di sini</a>.
                            </div>
                        @endif
                    </div>

                    <label><strong>Total Bayar</strong></label>
                    <div class="total-box mb-3" id="grand-total">Rp 0</div>
                    <input type="hidden" name="grand_total" id="grand-total-value">
                    <label class="fw-bold">Uang Dibayar</label>
                    <input type="text" name="uang_bayar" id="bayar"
                        class="form-control rupiah mb-3" placeholder="Masukkan uang">

                    <label class="fw-bold">Kembalian</label>
                    <input type="text" id="kembalian"
                        class="form-control mb-3" readonly>
                    <input type="hidden" name="uang_kembalian" id="kembalian-value">


                    <button type="submit" class="btn btn-primary w-100 btn-action" id="btn-simpan" disabled>
                        💾 Simpan Transaksi
                    </button>

                </div>
            </div>

        </form>
    </div>
</div>
@endsection
@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {

    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Sukses!',
            text: "{{ session('success') }}",
            toast: true,
            position: 'top-end',
            timer: 2800,
            showConfirmButton: false
        });
    @endif

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: "{{ session('error') }}",
            toast: true,
            position: 'top-end',
            timer: 3500,
            showConfirmButton: false
        });
    @endif

    // Init AutoNumeric untuk semua input rupiah
    AutoNumeric.multiple('.rupiah', {
        digitGroupSeparator: '.',
        decimalCharacter: ',',
        decimalPlaces: 0,
        currencySymbol: 'Rp ',
        currencySymbolPlacement: 'p',
        minimumValue: '0',
        unformatOnSubmit: true
    });

    // Khusus ambil nilai Bayar tanpa format
    const bayarAN = AutoNumeric.getAutoNumericElement('#bayar');

    const keranjangTable = document.querySelector('#keranjang-table tbody');
    const selectBarang = document.getElementById('select-barang');
    const qtyInput = document.getElementById('qty');

    const grandTotalEl = document.getElementById('grand-total');
    const inputKembalian = document.getElementById('kembalian');
    const btnSimpan = document.getElementById('btn-simpan');

    document.getElementById('btn-tambah').addEventListener('click', () => {
        const opt = selectBarang.selectedOptions[0];
        if (!opt) return;

        const nama = opt.dataset.nama;
        const harga = parseFloat(opt.dataset.harga);
        const qty = parseInt(qtyInput.value) || 1;
        const subtotal = harga * qty;

        const row = `
            <tr>
                <td>
                    ${nama}
                    <input type="hidden" name="id_barang_terjual[]" value="${opt.value}">
                </td>
                <td><input class="form-control qty" name="jumlah_barang_dijual[]" type="number" min="1" value="${qty}"></td>
                <td>Rp ${harga.toLocaleString('id-ID')}</td>
                <td class="subtotal" data-sub="${subtotal}">
                    Rp ${subtotal.toLocaleString('id-ID')}
                </td>
                <td><button type="button" class="btn btn-danger btn-sm hapus">X</button></td>
            </tr>
        `;
        keranjangTable.insertAdjacentHTML('beforeend', row);
        hitungTotal();
    });

    // Update Qty
    keranjangTable.addEventListener('input', function (e) {
        if (!e.target.classList.contains('qty')) return;

        const row = e.target.closest('tr');
        const harga = parseFloat(row.querySelector('td:nth-child(3)').innerText.replace(/[Rp .]/g, ''));
        const qty = parseInt(e.target.value) || 1;
        const subtotal = harga * qty;

        const subtotalEl = row.querySelector('.subtotal');
        subtotalEl.dataset.sub = subtotal;
        subtotalEl.innerText = "Rp " + subtotal.toLocaleString('id-ID');

        hitungTotal();
    });

    // Hapus barang
    keranjangTable.addEventListener('click', function (e) {
        if (e.target.classList.contains('hapus')) {
            e.target.closest('tr').remove();
            hitungTotal();
        }
    });

    // Hitung ulang bila bayar diketik
    document.getElementById('bayar').addEventListener('input', hitungKembalian);

    // Toggle QRIS Display
    const selectJenisPembayaran = document.getElementById('jenis_pembayaran_id');
    const qrisContainer = document.getElementById('qris-container');

    selectJenisPembayaran.addEventListener('change', function () {
        const selectedOption = this.selectedOptions[0];
        const namaPembayaran = selectedOption.dataset.nama || '';

        if (namaPembayaran === 'qris') {
            qrisContainer.classList.remove('d-none');
        } else {
            qrisContainer.classList.add('d-none');
        }
    });

    function hitungTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(el => {
            total += parseFloat(el.dataset.sub);
        });
        grandTotalEl.innerText = "Rp " + total.toLocaleString('id-ID');
        document.getElementById('grand-total-value').value = total;
        hitungKembalian();
    }


    function hitungKembalian() {
        const total = parseFloat(grandTotalEl.innerText.replace(/[Rp .]/g, '')) || 0;
        const bayar = parseFloat(bayarAN.getNumber()) || 0;
        const selisih = bayar - total;

        if (selisih < 0) {
            inputKembalian.value = "Kurang (-) Rp " + Math.abs(selisih).toLocaleString('id-ID');
            document.getElementById('kembalian-value').value = selisih; // angka minusnya
            btnSimpan.disabled = true;
        } else {
            inputKembalian.value = "Rp " + selisih.toLocaleString('id-ID');
            document.getElementById('kembalian-value').value = selisih; // angka murni
            btnSimpan.disabled = !(total > 0);
        }
    }


});

</script>
@endpush
