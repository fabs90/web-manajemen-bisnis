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
                    <label class="form-check-label" for="lain_lain">Lain-lain</label>
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
                <label for="jumlah_manual" class="form-label">Jumlah Pengeluaran</label>
                <input type="number" id="jumlah_manual" name="jumlah_manual" class="form-control" min="0" value="0">
            </div>

            {{-- Jumlah Pengeluaran Total --}}
            <div class="mb-3">
                <label for="jumlah_pengeluaran" class="form-label">Jumlah Pengeluaran Total (Otomatis)</label>
                <input type="number" name="jumlah" id="jumlah_pengeluaran" class="form-control" readonly required>
            </div>

            {{-- Potongan, Biaya Lain, Bunga --}}
            <div class="mb-3">
                <label for="potongan_pembelian" class="form-label">Potongan</label>
                <input type="number" name="potongan_pembelian" id="potongan_pembelian" class="form-control" value="0" min="0">
            </div>
            <div class="mb-3">
                <label for="biaya_lain" class="form-label">Biaya Lain</label>
                <input type="number" name="biaya_lain" id="biaya_lain" class="form-control" value="0" min="0">
            </div>
            <div class="mb-3">
                <label for="bunga_bank" class="form-label">Bunga Bank</label>
                <input type="number" name="bunga_bank" id="bunga_bank" class="form-control" value="0" min="0">
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">Simpan Pengeluaran</button>
            </div>
        </form>
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
    const bungaBankInput = document.getElementById('bunga_bank');

    let barangIndex = 0;

    const barangTemplate = `
        <div class="row mb-2 align-items-end" data-index="{index}">
            <div class="col-md-5">
                <select name="barang_dibeli[{index}]" class="form-control barang-select" required>
                    <option value="" disabled selected>-- Pilih Barang --</option>
                    @foreach($barang as $item)
                        <option value="{{ $item->id }}" data-harga="{{ $item->harga_jual_per_unit }}">{{ $item->nama }} - Rp {{ number_format($item->harga_jual_per_unit,0,',','.') }} - Saldo: {{ $item->getSaldoAkhir() }}</option>
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

    const hutangAktifSection = document.getElementById('hutang-aktif-section');

    function toggleKeperluanSection() {
        const selectedKeperluan = document.querySelector('input[name="jenis_keperluan"]:checked')?.value;

        barangSection.classList.add('d-none');
        barangList.innerHTML = '';
        jumlahManualContainer.classList.add('d-none');
        jumlahManualInput.value = 0;
        hutangAktifSection.classList.add('d-none'); // sembunyikan default

        if (selectedKeperluan === 'membeli_barang') {
            barangSection.classList.remove('d-none');
            tambahBarang();
        } else if (selectedKeperluan === 'membayar_hutang') {
            jumlahManualContainer.classList.remove('d-none');
            hutangAktifSection.classList.remove('d-none'); // tampilkan hutang aktif
        } else if (selectedKeperluan === 'lain_lain') {
            jumlahManualContainer.classList.remove('d-none');
        }

        hitungTotal();
    }


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

        hitungSubtotal();

        select.addEventListener('change', hitungSubtotal);
        jumlahInput.addEventListener('input', hitungSubtotal);

        row.querySelector('.hapus-barang').addEventListener('click', () => {
            row.remove();
            hitungTotal();
        });
    }

    function hitungTotal() {
        let total = 0;

        document.querySelectorAll('.subtotal-input').forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        if (barangSection.classList.contains('d-none')) {
            total += parseFloat(jumlahManualInput.value) || 0;
        }

        total += parseFloat(potonganInput.value) || 0;
        total += parseFloat(biayaLainInput.value) || 0;
        total += parseFloat(bungaBankInput.value) || 0;

        jumlahPengeluaran.value = total;
    }

    // Event listeners
    jenisPengeluaran.addEventListener('change', toggleKrediturSection);
    jenisKeperluanRadios.forEach(radio => radio.addEventListener('change', toggleKeperluanSection));
    tambahBarangBtn.addEventListener('click', tambahBarang);

    [jumlahManualInput, potonganInput, biayaLainInput, bungaBankInput].forEach(el => {
        el.addEventListener('input', hitungTotal);
    });

    // hitung total sebelum submit untuk memastikan jumlah_pengeluaran terisi
    form.addEventListener('submit', function() {
        hitungTotal();
    });

    toggleKrediturSection();
    toggleKeperluanSection();
});

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
</script>
@endpush
