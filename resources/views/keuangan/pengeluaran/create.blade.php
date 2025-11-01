@extends('layouts.partial.layouts')
@section('page-title', 'Input Pengeluaran')

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

        <form action="{{ route('keuangan.pengeluaran.store') }}" method="POST">
            @csrf

            {{-- Uraian Pengeluaran --}}
            <div class="mb-3">
                <label for="uraian_pengeluaran" class="form-label">Uraian Pengeluaran<span class="text-danger">*</span></label>
                <input type="text" name="uraian_pengeluaran" id="uraian_pengeluaran" class="form-control @error('uraian_pengeluaran') is-invalid @enderror" value="{{ old('uraian_pengeluaran') }}" required>
                @error('uraian_pengeluaran')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Jumlah Pengeluaran --}}
            <div class="mb-3">
                <label for="jumlah" class="form-label">Jumlah Pengeluaran<span class="text-danger">*</span></label>
                <input type="text" name="jumlah" id="jumlah" class="form-control rupiah @error('jumlah') is-invalid @enderror" value="{{ old('jumlah') }}" required>
                @error('jumlah')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tanggal --}}
            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal Transaksi<span class="text-danger">*</span></label>
                <input type="date" name="tanggal" id="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                @error('tanggal')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Jenis Pengeluaran --}}
            <div class="mb-3">
                <label class="form-label">Jenis Pengeluaran<span class="text-danger">*</span></label>
                <select name="jenis_pengeluaran" id="jenis_pengeluaran" class="form-select @error('jenis_pengeluaran') is-invalid @enderror" required>
                    <option value="" disabled {{ old('jenis_pengeluaran') == null ? 'selected' : '' }}>-- Pilih Jenis Pengeluaran --</option>
                    <option value="tunai" {{ old('jenis_pengeluaran') == 'tunai' ? 'selected' : '' }}>Tunai</option>
                    <option value="kredit" {{ old('jenis_pengeluaran') == 'kredit' ? 'selected' : '' }}>Kredit</option>
                </select>
                @error('jenis_pengeluaran')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Kreditur --}}
            <div id="kreditur-section" class="d-none">
                <hr>
                <h5 class="mb-3">Detail Kreditur</h5>
                <div class="mb-3">
                    <label for="nama_kreditur" class="form-label">Nama Kreditur<span class="text-danger">*</span></label>
                    <select name="nama_kreditur" id="nama_kreditur" class="form-control @error('nama_kreditur') is-invalid @enderror" disabled>
                        <option value="" disabled {{ old('nama_kreditur') == null ? 'selected' : '' }}>-- Pilih Kreditur --</option>
                        @isset($kreditur)
                            @foreach ($kreditur as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('nama_kreditur') == $supplier->id ? 'selected' : '' }}>{{ $supplier->nama }}</option>
                            @endforeach
                        @endisset
                    </select>
                    @error('nama_kreditur')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Jenis Keperluan --}}
            <div class="mb-3">
                <label class="form-label">Jenis Keperluan<span class="text-danger">*</span></label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jenis_keperluan" id="membeli_barang" value="membeli_barang" {{ old('jenis_keperluan') == 'membeli_barang' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="membeli_barang">Membeli Barang / Menambah Hutang</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jenis_keperluan" id="membayar_hutang" value="membayar_hutang" {{ old('jenis_keperluan') == 'membayar_hutang' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="membayar_hutang">Membayar Hutang</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jenis_keperluan" id="lain_lain" value="lain_lain" {{ old('jenis_keperluan') == 'lain_lain' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="lain_lain">Lain-lain</label>
                </div>
                @error('jenis_keperluan')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- Hutang Section --}}
            <div id="hutang-section" class="d-none">
                <div class="mb-3">
                    <label for="hutang_id" class="form-label">Pilih Hutang<span class="text-danger">*</span></label>
                    <select name="hutang_id" id="hutang_id" class="form-control @error('hutang_id') is-invalid @enderror">
                        <option value="" disabled {{ old('hutang_id') == null ? 'selected' : '' }}>-- Pilih Hutang --</option>
                        @isset($listHutang)
                            @foreach ($listHutang as $hutang)
                                <option value="{{ $hutang->id }}" {{ old('hutang_id') == $hutang->id ? 'selected' : '' }}>
                                    {{ $hutang->uraian }} - Rp {{ number_format($hutang->saldo, 0, ',', '.') }} - {{ $hutang->pelanggan->nama }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                    @error('hutang_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    {{-- Checkbox Barang Baru --}}
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="ada_barang_baru" {{ old('ada_barang_baru') ? 'checked' : '' }}>
                        <label class="form-check-label" for="ada_barang_baru">Apakah ada barang baru yang dibeli?</label>
                    </div>
                </div>
            </div>

            {{-- Barang Section --}}
            <div id="barang-section" class="d-none ms-3 mb-3">
                <div class="mb-3">
                    <label for="barang_dibeli" class="form-label">Barang Dibeli<span class="text-danger">*</span></label>
                    <select name="barang_dibeli" id="barang_dibeli" class="form-control @error('barang_dibeli') is-invalid @enderror">
                        <option value="" disabled {{ old('barang_dibeli') == null ? 'selected' : '' }}>-- Pilih Barang --</option>
                        @isset($barang)
                            @foreach ($barang as $item)
                                <option value="{{ $item->id }}" {{ old('barang_dibeli') == $item->id ? 'selected' : '' }}>{{ $item->nama }}</option>
                            @endforeach
                        @endisset
                    </select>
                    @error('barang_dibeli')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="jumlah_barang_dibeli" class="form-label">Jumlah Barang<span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_barang_dibeli" id="jumlah_barang_dibeli" class="form-control @error('jumlah_barang_dibeli') is-invalid @enderror" value="{{ old('jumlah_barang_dibeli') }}">
                    @error('jumlah_barang_dibeli')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Potongan & Biaya Lain --}}
            <div class="mb-3">
                <label for="potongan_pembelian" class="form-label">Potongan Pembelian</label>
                <input type="text" name="potongan_pembelian" id="potongan_pembelian" class="form-control rupiah @error('potongan_pembelian') is-invalid @enderror" value="{{ old('potongan_pembelian') }}">
                @error('potongan_pembelian')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="biaya_lain" class="form-label">Biaya Lain</label>
                <input type="text" name="biaya_lain" id="biaya_lain" class="form-control rupiah @error('biaya_lain') is-invalid @enderror" value="{{ old('biaya_lain') }}">
                @error('biaya_lain')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Bunga Bank --}}
            <div class="mb-3">
                <label for="bunga_bank" class="form-label">Bunga Bank</label>
                <input type="text" name="bunga_bank" id="bunga_bank" class="form-control rupiah @error('bunga_bank') is-invalid @enderror" value="{{ old('bunga_bank') }}">
                @error('bunga_bank')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">Simpan Pengeluaran</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const jenisPengeluaranSelect = document.getElementById('jenis_pengeluaran');
    const krediturSection = document.getElementById('kreditur-section');
    const namaKrediturInput = document.getElementById('nama_kreditur');
    const jenisKeperluanRadios = document.querySelectorAll('input[name="jenis_keperluan"]');
    const barangSection = document.getElementById('barang-section');
    const barangSelect = document.getElementById('barang_dibeli');
    const jumlahBarangDibeli = document.getElementById('jumlah_barang_dibeli');
    const hutangSection = document.getElementById('hutang-section');
    const hutangSelect = document.getElementById('hutang_id');
    const adaBarangBaruCheckbox = document.getElementById('ada_barang_baru');

    function toggleKrediturSection() {
        if (jenisPengeluaranSelect.value === 'kredit') {
            krediturSection.classList.remove('d-none');
            namaKrediturInput.removeAttribute('disabled');
            namaKrediturInput.setAttribute('required', 'required');
        } else {
            krediturSection.classList.add('d-none');
            namaKrediturInput.setAttribute('disabled', 'disabled');
            namaKrediturInput.removeAttribute('required');
        }
    }

    function toggleKeperluanSection() {
        const selectedKeperluan = document.querySelector('input[name="jenis_keperluan"]:checked')?.value;

        [barangSection, hutangSection].forEach(el => el.classList.add('d-none'));
        [barangSelect, jumlahBarangDibeli, hutangSelect].forEach(el => {
            el.setAttribute('disabled', 'disabled');
            el.removeAttribute('required');
        });

        if (selectedKeperluan === 'membeli_barang') {
            barangSection.classList.remove('d-none');
            barangSelect.removeAttribute('disabled'); barangSelect.setAttribute('required','required');
            jumlahBarangDibeli.removeAttribute('disabled'); jumlahBarangDibeli.setAttribute('required','required');
        }
        if (selectedKeperluan === 'membayar_hutang') {
            hutangSection.classList.remove('d-none');
            hutangSelect.removeAttribute('disabled'); hutangSelect.setAttribute('required','required');
            if (adaBarangBaruCheckbox.checked) {
                barangSection.classList.remove('d-none');
                barangSelect.removeAttribute('disabled'); barangSelect.setAttribute('required','required');
                jumlahBarangDibeli.removeAttribute('disabled'); jumlahBarangDibeli.setAttribute('required','required');
            }
        }
    }

    jenisPengeluaranSelect.addEventListener('change', toggleKrediturSection);
    jenisKeperluanRadios.forEach(radio => radio.addEventListener('change', toggleKeperluanSection));
    adaBarangBaruCheckbox.addEventListener('change', toggleKeperluanSection);

    toggleKrediturSection();
    toggleKeperluanSection();

    // AutoNumeric untuk rupiah
    const options = { digitGroupSeparator: '.', decimalCharacter: ',', decimalPlaces: 0, unformatOnSubmit: true, minimumValue: '0' };
    document.querySelectorAll('.rupiah').forEach(el => new AutoNumeric(el, options));

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
