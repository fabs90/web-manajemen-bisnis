@extends('layouts.partial.layouts')
@section('page-title', 'Tambah Pengeluaran | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'Pengeluaran')
@section('section-row')
<div class="card shadow-sm">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger text-center mx-auto w-75">
                <h6 class="fw-bold mb-2">Terjadi Kesalahan:</h6>
                <ul class="mb-0 text-start d-inline-block">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    </ul>
            </div>
        @endif

        <form action="{{ route('keuangan.pengeluaran.store') }}" method="POST" id="pengeluaran-form">
            @csrf

            {{-- Uraian Pengeluaran --}}
            <div class="mb-3">
                <label for="uraian_pengeluaran" class="form-label">Uraian Pengeluaran<span class="text-danger">*</span></label>
                <input type="text" name="uraian_pengeluaran" id="uraian_pengeluaran"
                    class="form-control @error('uraian_pengeluaran') is-invalid @enderror"
                    value="{{ old('uraian_pengeluaran') }}" required>
                @error('uraian_pengeluaran')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tanggal --}}
            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal Transaksi<span class="text-danger">*</span></label>
                <input type="date" name="tanggal" id="tanggal"
                    class="form-control @error('tanggal') is-invalid @enderror"
                    value="{{ old('tanggal', date('Y-m-d')) }}" required>
                @error('tanggal')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Jenis Pengeluaran --}}
            <div class="mb-3">
                <label class="form-label">Jenis Pengeluaran<span class="text-danger">*</span></label>
                <select name="jenis_pengeluaran" id="jenis_pengeluaran"
                    class="form-select @error('jenis_pengeluaran') is-invalid @enderror" required>
                    <option value="" disabled {{ old('jenis_pengeluaran') ? '' : 'selected' }}>-- Pilih Jenis --</option>
                    <option value="tunai" {{ old('jenis_pengeluaran') == 'tunai' ? 'selected' : '' }}>Tunai</option>
                    <option value="kredit" {{ old('jenis_pengeluaran') == 'kredit' ? 'selected' : '' }}>Kredit</option>
                </select>
                @error('jenis_pengeluaran')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Jenis Keperluan --}}
            <div class="mb-3">
                <label class="form-label">Jenis Keperluan<span class="text-danger">*</span></label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jenis_keperluan" id="membeli_barang"
                        value="membeli_barang" {{ old('jenis_keperluan') == 'membeli_barang' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="membeli_barang">Membeli Barang / Menambah Hutang</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jenis_keperluan" id="membayar_hutang"
                        value="membayar_hutang" {{ old('jenis_keperluan') == 'membayar_hutang' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="membayar_hutang">Membayar Hutang</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jenis_keperluan" id="lain_lain"
                        value="lain_lain" {{ old('jenis_keperluan') == 'lain_lain' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="lain_lain">Lain-lain (Diluar Kas Kecil)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jenis_keperluan" id="kas_kecil"
                        value="kas_kecil" {{ old('jenis_keperluan') == 'kas_kecil' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="kas_kecil">Pengisian Kas Kecil</label>
                </div>
            </div>

            {{-- Kreditur --}}
            <div id="kreditur-section" class="d-none mb-3">
                <hr>
                <h5 class="mb-3">Detail Kreditur</h5>
                <select name="nama_kreditur" id="nama_kreditur" class="form-select" disabled>
                    <option value="" disabled selected>-- Pilih Kreditur --</option>
                    @isset($kreditur)
                        @foreach ($kreditur as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('nama_kreditur') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->nama }}
                            </option>
                        @endforeach
                    @endisset
                </select>
            </div>

            {{-- Hutang Aktif --}}
            <div id="hutang-aktif-section" class="d-none mb-3">
                <label for="hutang_id" class="form-label">Pilih Hutang Aktif</label>
                <select name="hutang_id" id="hutang_id" class="form-select">
                    <option value="" disabled selected>-- Pilih Hutang --</option>
                    @foreach($listHutang as $hutang)
                        <option value="{{ $hutang->id }}">
                            {{ $hutang->uraian }} - {{ $hutang->pelanggan->nama ?? '-' }} - Rp {{ number_format($hutang->saldo,0,',','.') }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Barang Dibeli --}}
            <div id="barang-section" class="d-none mb-3">
                <label class="form-label">Barang Dibeli</label>
                <div id="barang-list"></div>
                <button type="button" class="btn btn-secondary btn-sm mt-2" id="tambah-barang">
                    <i class="bi bi-plus"></i> Tambah Barang
                </button>
            </div>

            {{-- Jumlah Manual --}}
            <div id="jumlah_manual_container" class="mb-3 d-none">
                <label for="jumlah_manual" class="form-label">Isi Jumlah Pengeluaran Disini</label>
                <input type="text" id="jumlah_manual" name="jumlah_manual" class="form-control" value="0">
            </div>

            {{-- Jumlah Pengeluaran Total --}}
            <div class="mb-3">
                <label for="jumlah_pengeluaran" class="form-label">Total Pengeluaran (Otomatis)</label>
                <input type="number" name="jumlah" id="jumlah_pengeluaran" class="form-control" readonly required>
            </div>

            {{-- Potongan, Biaya Lain, Bunga --}}
            <div class="mb-3">
                <label for="potongan_pembelian" class="form-label">Potongan</label>
                <input type="text" name="potongan_pembelian" id="potongan_pembelian" class="form-control" value="0">
            </div>
            <div class="mb-3">
                <label for="biaya_lain" class="form-label">Biaya Lain</label>
                <input type="text" name="biaya_lain" id="biaya_lain" class="form-control" value="0">
            </div>
            <div class="mb-3">
                <label for="admin_bank" class="form-label">Admin Bank</label>
                <input type="text" name="admin_bank" id="admin_bank" class="form-control" value="0">
            </div>

            <div class="mt-4 text-end">
            <a href="{{ route('keuangan.pengeluaran.list') }}" class="btn btn-secondary">
             Kembali
            </a>
                <button type="submit" class="btn btn-primary">Simpan Pengeluaran</button>
            </div>
        </form>
        <div id="loading-overlay"
            style="position: fixed; top:0; left:0; width:100%; height:100%;
                   background: rgba(255,255,255,0.7); z-index:9999;
                   display:none; justify-content:center; align-items:center;">
            <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

    </div>
</div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function() {

    const form = document.getElementById('pengeluaran-form');
    const jenisPengeluaran = document.getElementById('jenis_pengeluaran');
    const krediturSection = document.getElementById('kreditur-section');
    const namaKreditur = document.getElementById('nama_kreditur');

    const jenisKeperluanRadios = document.querySelectorAll('input[name="jenis_keperluan"]');
    const barangSection = document.getElementById('barang-section');
    const barangList = document.getElementById('barang-list');
    const tambahBarangBtn = document.getElementById('tambah-barang');

    const jumlahManualContainer = document.getElementById('jumlah_manual_container');
    const jumlahManualInput = document.getElementById('jumlah_manual');
    const jumlahPengeluaran = document.getElementById('jumlah_pengeluaran');

    const potonganInput = document.getElementById('potongan_pembelian');
    const biayaLainInput = document.getElementById('biaya_lain');
    const bungaBankInput = document.getElementById('admin_bank');

    const hutangAktifSection = document.getElementById('hutang-aktif-section');

    let barangIndex = 0;

    const barangTemplate = `
        <div class="row mb-2 align-items-end" data-index="{index}">
            <div class="col-md-5">
                <select name="barang_dibeli[{index}]" class="form-control barang-select" required>
                    <option value="" disabled selected>-- Pilih Barang --</option>
                    @foreach($barang as $item)
                        <option value="{{ $item->id }}" data-harga="{{ $item->harga_jual_per_unit }}">
                            {{ $item->nama }} - Rp {{ number_format($item->harga_jual_per_unit,0,',','.') }} - Saldo: {{ $item->getSaldoAkhir() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="jumlah_barang_dibeli[{index}]" class="form-control jumlah-input" min="1" value="1" required>
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control subtotal-input" readonly value="0">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm hapus-barang">x</button>
            </div>
        </div>
    `;

    // ======== Fungsi Utilitas ========
    function parseRupiah(value) {
        if (!value) return 0;
        return parseFloat(value.toString().replace(/\./g, '').replace(/,/g, '.')) || 0;
    }

    function formatRupiah(angka) {
        if (isNaN(angka)) return '0';
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // ======== Fungsi Section ========
    function toggleKrediturSection() {
        if (jenisPengeluaran.value === 'kredit') {
            krediturSection.classList.remove('d-none');
            namaKreditur.removeAttribute('disabled');
            namaKreditur.setAttribute('required', 'required');
        } else {
            krediturSection.classList.add('d-none');
            namaKreditur.setAttribute('disabled', 'disabled');
            namaKreditur.removeAttribute('required');
        }
    }

    function toggleKeperluanSection() {
        const selectedKeperluan = document.querySelector('input[name="jenis_keperluan"]:checked')?.value;

        barangSection.classList.add('d-none');
        hutangAktifSection.classList.add('d-none');
        jumlahManualContainer.classList.add('d-none');

        barangList.innerHTML = '';
        jumlahManualInput.value = 0;

        if (selectedKeperluan === 'membeli_barang') {
            barangSection.classList.remove('d-none');
            tambahBarang();
        }
        else if (selectedKeperluan === 'membayar_hutang') {
            hutangAktifSection.classList.remove('d-none');
            jumlahManualContainer.classList.remove('d-none');
        }
        else if (selectedKeperluan === 'lain_lain') {
            jumlahManualContainer.classList.remove('d-none');
        }
        else if (selectedKeperluan === 'kas_kecil') {
            jumlahManualContainer.classList.remove('d-none');
        }

        hitungTotal();
    }

    // ======== Fungsi Barang ========
    function tambahBarang() {
        const html = barangTemplate.replace(/{index}/g, barangIndex);
        barangList.insertAdjacentHTML('beforeend', html);
        const row = barangList.lastElementChild;
        attachBarangEvents(row);
        barangIndex++;
    }

    function attachBarangEvents(row) {
        const select = row.querySelector('.barang-select');
        const jumlahInput = row.querySelector('.jumlah-input');
        const subtotalInput = row.querySelector('.subtotal-input');

        function hitungSubtotal() {
            const harga = parseFloat(select.selectedOptions[0]?.dataset.harga || 0);
            const jumlah = parseInt(jumlahInput.value) || 1;
            subtotalInput.value = harga * jumlah;
            hitungTotal();
        }

        select.addEventListener('change', hitungSubtotal);
        jumlahInput.addEventListener('input', hitungSubtotal);
        row.querySelector('.hapus-barang').addEventListener('click', () => {
            row.remove();
            hitungTotal();
        });

        hitungSubtotal(); // inisialisasi awal
    }

    // ======== Fungsi Hitung ========
    function hitungTotal() {
        let total = 0;

        // Total dari barang
        document.querySelectorAll('.subtotal-input').forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        // Kalau pakai input manual (bukan membeli barang)
        if (barangSection.classList.contains('d-none')) {
            total += parseRupiah(jumlahManualInput.value);
        }

        // Hitung tambahan & pengurang
        total -= parseRupiah(potonganInput.value);
        total += parseRupiah(biayaLainInput.value);
        total += parseRupiah(bungaBankInput.value);

        jumlahPengeluaran.value = total;
    }

    // ======== Event Listeners ========
    jenisPengeluaran.addEventListener('change', toggleKrediturSection);
    jenisKeperluanRadios.forEach(radio => radio.addEventListener('change', toggleKeperluanSection));
    tambahBarangBtn.addEventListener('click', tambahBarang);

    // Format otomatis input rupiah
    const rupiahInputs = [jumlahManualInput, potonganInput, biayaLainInput, bungaBankInput];
    rupiahInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            let angka = parseRupiah(e.target.value);
            e.target.value = formatRupiah(angka);
            hitungTotal();
        });
    });

    // Pastikan data rupiah dikirim ke server sebagai angka murni
    form.addEventListener('submit', function() {
        hitungTotal();
        rupiahInputs.forEach(input => {
            input.value = parseRupiah(input.value); // ubah ke angka tanpa titik
        });
    });

    // ======== Inisialisasi awal ========
    toggleKrediturSection();
    toggleKeperluanSection();

    // ======== Notifikasi SweetAlert ========
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Sukses!',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 2500,
            toast: true,
            position: 'top-end',
        });
    @endif

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: "{{ session('error') }}",
            showConfirmButton: false,
            timer: 5000,
            toast: true,
            position: 'top-end',
        });
    @endif

    // ======== Loading Setelah Submit ========
    const loadingOverlay = document.getElementById('loading-overlay');

    form.addEventListener('submit', function(e) {
        loadingOverlay.style.display = 'flex';

        // disable semua button untuk mencegah double submit
        form.querySelectorAll('button').forEach(btn => btn.disabled = true);
    });


});
</script>
@endpush
