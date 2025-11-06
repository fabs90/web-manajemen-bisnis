@extends('layouts.partial.layouts')
@section('page-title', 'Tambah Pendapatan | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'Pendapatan')
@section('section-row')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('keuangan.pendapatan.create_lain') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Data Pendapatan Lain-Lain
        </a>
    </div>

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

            <form action="{{ route('keuangan.pendapatan.store') }}" method="POST">
                @csrf

                {{-- Uraian Pendapatan --}}
                <div class="mb-3">
                    <label for="uraian_pendapatan" class="form-label">Uraian Pendapatan<span
                            class="text-danger">*</span></label>
                    <input type="text" name="uraian_pendapatan" id="uraian_pendapatan"
                        class="form-control @error('uraian_pendapatan') is-invalid @enderror"
                        value="{{ old('uraian_pendapatan') }}" placeholder="Uraian Pendapatan" required>
                    @error('uraian_pendapatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Jumlah --}}
                <div class="mb-3">
                    <label for="jumlah" class="form-label">Jumlah Penjualan<span class="text-danger">*</span></label>
                    <input type="text" name="jumlah" id="jumlah"
                        class="form-control rupiah @error('jumlah') is-invalid @enderror" value="{{ old('jumlah') }}"
                        placeholder="Jumlah Penjualan" required>
                    @error('jumlah')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tanggal --}}
                <div class="mb-3">
                    <label for="tanggal" class="form-label">Tanggal Transaksi<span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" id="tanggal"
                        class="form-control @error('tanggal') is-invalid @enderror"
                        value="{{ old('tanggal', date('Y-m-d')) }}">
                    @error('tanggal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Jenis Pendapatan --}}
                <div class="mb-3">
                    <label class="form-label">Jenis Pendapatan<span class="text-danger">*</span></label>
                    <select name="jenis_pendapatan" id="jenis_pendapatan"
                        class="form-select @error('jenis_pendapatan') is-invalid @enderror" required>
                        <option value="" disabled {{ old('jenis_pendapatan') ? '' : 'selected' }}>-- Pilih Jenis --
                        </option>
                        <option value="tunai" {{ old('jenis_pendapatan') == 'tunai' ? 'selected' : '' }}>Tunai</option>
                        <option value="piutang" {{ old('jenis_pendapatan') == 'piutang' ? 'selected' : '' }}>Piutang
                        </option>
                        <option value="kredit" {{ old('jenis_pendapatan') == 'kredit' ? 'selected' : '' }}>Kredit
                            (Pelunasan)</option>
                    </select>
                    @error('jenis_pendapatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Checkbox: Apakah ada debitur? --}}
                <div id="debitur-check-container" class="mb-3 d-none">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="ada_debitur_check" name="ada_debitur_check"
                            value="1" {{ old('ada_debitur_check') ? 'checked' : '' }}>
                        <label class="form-check-label" for="ada_debitur_check">
                            Ada debitur (piutang / pelunasan)
                        </label>
                    </div>
                </div>

                {{-- Section Debitur --}}
                <div id="debitur-section" class="d-none">
                    <hr>
                    <h5 class="mb-3">Detail Debitur</h5>

                    <div class="mb-3">
                        <label for="nama_pelanggan" class="form-label">Nama Debitur<span
                                class="text-danger">*</span></label>
                        <select name="nama_pelanggan" id="nama_pelanggan"
                            class="form-control @error('nama_pelanggan') is-invalid @enderror" disabled>
                            <option value="" disabled {{ old('nama_pelanggan') ? '' : 'selected' }}>-- Pilih Debitur
                                --</option>
                            @foreach ($debitur as $pelanggan)
                                <option value="{{ $pelanggan->id }}"
                                    {{ old('nama_pelanggan') == $pelanggan->id ? 'selected' : '' }}>{{ $pelanggan->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('nama_pelanggan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Piutang Aktif --}}
                    <div id="piutang_aktif_container" class="mb-3 d-none">
                        <label for="piutang_aktif" class="form-label">Piutang Aktif (untuk ditambah)</label>
                        <select name="piutang_aktif" id="piutang_aktif" class="form-control" disabled>
                            <option value="" disabled selected>-- Pilih Piutang Aktif --</option>
                            @foreach ($listPiutang as $piutang)
                                <option value="{{ $piutang->kode }}" data-debitur="{{ $piutang->pelanggan_id }}"
                                    class="piutang-option">
                                    {{ $piutang->pelanggan->nama }} - Rp {{ number_format($piutang->saldo, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Hutang Aktif --}}
                    <div id="hutang_aktif_container" class="mb-3 d-none">
                        <label for="hutang_aktif" class="form-label">Hutang Aktif (untuk dilunasi)</label>
                        <select name="hutang_aktif" id="hutang_aktif" class="form-control" disabled>
                            <option value="" disabled selected>-- Pilih Hutang Aktif --</option>
                            @foreach ($listPiutang as $piutang)
                                <option value="{{ $piutang->kode }}" data-debitur="{{ $piutang->pelanggan_id }}"
                                    class="hutang-option">
                                    {{ $piutang->pelanggan->nama }} -
                                    Rp {{ number_format($piutang->saldo, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Barang Terjual --}}
                <div class="mb-3">
                    <label for="barang_terjual" class="form-label">Barang Terjual</label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="barang_terjual_check"
                            name="barang_terjual_check" value="1"
                            {{ old('barang_terjual_check') ? 'checked' : '' }}>
                        <label class="form-check-label" for="barang_terjual_check">Tidak ada barang terjual</label>
                    </div>
                    <select name="barang_terjual" id="barang_terjual"
                        class="form-control @error('barang_terjual') is-invalid @enderror">
                        <option value="" disabled {{ old('barang_terjual') ? '' : 'selected' }}>-- Pilih Barang --
                        </option>
                        @foreach ($barang as $item)
                            <option value="{{ $item->id }}"
                                {{ old('barang_terjual') == $item->id ? 'selected' : '' }}>{{ $item->nama }} -
                                Rp {{ number_format($item->harga_jual_per_unit, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                    @error('barang_terjual')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="jumlah_barang_dijual" class="form-label">Jumlah Barang (satuan)</label>
                    <input type="number" name="jumlah_barang_dijual" id="jumlah_barang_dijual"
                        class="form-control @error('jumlah_barang_dijual') is-invalid @enderror"
                        value="{{ old('jumlah_barang_dijual') }}">
                    @error('jumlah_barang_dijual')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Biaya Tambahan --}}
                <div class="mb-3">
                    <label for="potongan_pembelian" class="form-label">Potongan</label>
                    <input type="text" name="potongan_pembelian" id="potongan_pembelian"
                        class="form-control rupiah @error('potongan_pembelian') is-invalid @enderror"
                        value="{{ old('potongan_pembelian') }}" placeholder="Biaya Tambahan (Opsional)">
                    @error('potongan_pembelian')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="biaya_lain" class="form-label">Lain-lain</label>
                    <input type="text" name="biaya_lain" id="biaya_lain"
                        class="form-control rupiah @error('biaya_lain') is-invalid @enderror"
                        value="{{ old('biaya_lain') }}" placeholder="Biaya Lain (Opsional)">
                    @error('biaya_lain')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="bunga_bank" class="form-label">Bunga Bank</label>
                    <input type="text" name="bunga_bank" id="bunga_bank"
                        class="form-control rupiah @error('bunga_bank') is-invalid @enderror"
                        value="{{ old('bunga_bank') }}" placeholder="Bunga Bank (Opsional)">
                    @error('bunga_bank')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const jenisSelect = document.getElementById('jenis_pendapatan');
            const debiturCheckContainer = document.getElementById('debitur-check-container');
            const adaDebiturCheck = document.getElementById('ada_debitur_check');
            const debiturSection = document.getElementById('debitur-section');
            const namaPelanggan = document.getElementById('nama_pelanggan');
            const piutangContainer = document.getElementById('piutang_aktif_container');
            const hutangContainer = document.getElementById('hutang_aktif_container');
            const piutangSelect = document.getElementById('piutang_aktif');
            const hutangSelect = document.getElementById('hutang_aktif');

            const barangCheck = document.getElementById('barang_terjual_check');
            const barangSelect = document.getElementById('barang_terjual');
            const jumlahBarang = document.getElementById('jumlah_barang_dijual');

            const piutangOptions = Array.from(document.querySelectorAll('#piutang_aktif option.piutang-option'));
            const hutangOptions = Array.from(document.querySelectorAll('#hutang_aktif option.hutang-option'));

            function updateForm() {
                const jenis = jenisSelect.value;
                const adaDebitur = adaDebiturCheck.checked;

                // Reset semua
                debiturCheckContainer.classList.add('d-none');
                debiturSection.classList.add('d-none');
                namaPelanggan.setAttribute('disabled', 'disabled');
                namaPelanggan.removeAttribute('required');
                piutangContainer.classList.add('d-none');
                hutangContainer.classList.add('d-none');
                piutangSelect.setAttribute('disabled', 'disabled');
                hutangSelect.setAttribute('disabled', 'disabled');

                // Tampilkan checkbox jika jenis = piutang / kredit
                if (jenis === 'piutang' || jenis === 'kredit') {
                    debiturCheckContainer.classList.remove('d-none');
                }

                // Jika ada debitur
                if (adaDebitur && (jenis === 'piutang' || jenis === 'kredit')) {
                    debiturSection.classList.remove('d-none');
                    namaPelanggan.removeAttribute('disabled');
                    namaPelanggan.setAttribute('required', 'required');

                    resetSelect(piutangSelect, '-- Pilih Piutang Aktif --');
                    resetSelect(hutangSelect, '-- Pilih Hutang Aktif --');

                    if (namaPelanggan.value) {
                        showRelatedSelect(jenis, namaPelanggan.value);
                    }
                }
            }

            function resetSelect(select, placeholder) {
                select.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
                select.setAttribute('disabled', 'disabled');
            }

            function showRelatedSelect(jenis, debiturId) {
                if (jenis === 'piutang') {
                    piutangContainer.classList.remove('d-none');
                    filterAndPopulate(piutangSelect, piutangOptions, debiturId, '-- Pilih Piutang Aktif --');
                } else if (jenis === 'kredit') {
                    hutangContainer.classList.remove('d-none');
                    filterAndPopulate(hutangSelect, hutangOptions, debiturId, '-- Pilih Hutang Aktif --');
                }
            }

            function filterAndPopulate(select, options, debiturId, placeholder) {
                select.innerHTML = '';
                const matches = options.filter(opt => opt.getAttribute('data-debitur') === debiturId);
                if (matches.length === 0) {
                    select.innerHTML = `<option value="" disabled selected>-- Tidak ada data aktif --</option>`;
                    select.setAttribute('disabled', 'disabled');
                    return;
                }
                const placeholderOpt = document.createElement('option');
                placeholderOpt.value = '';
                placeholderOpt.text = placeholder;
                placeholderOpt.disabled = true;
                placeholderOpt.selected = true;
                select.appendChild(placeholderOpt);
                matches.forEach(opt => select.appendChild(opt.cloneNode(true)));
                select.removeAttribute('disabled');
            }

            jenisSelect.addEventListener('change', updateForm);
            adaDebiturCheck.addEventListener('change', updateForm);

            namaPelanggan.addEventListener('change', function() {
                const jenis = jenisSelect.value;
                if (this.value && (jenis === 'piutang' || jenis === 'kredit')) {
                    showRelatedSelect(jenis, this.value);
                } else {
                    piutangContainer.classList.add('d-none');
                    hutangContainer.classList.add('d-none');
                }
            });

            // Barang terjual
            barangCheck.addEventListener('change', function() {
                if (this.checked) {
                    barangSelect.setAttribute('disabled', 'disabled');
                    jumlahBarang.setAttribute('disabled', 'disabled');
                    barangSelect.value = '';
                    jumlahBarang.value = '';
                } else {
                    barangSelect.removeAttribute('disabled');
                    jumlahBarang.removeAttribute('disabled');
                }
            });

            updateForm();

            if (barangCheck.checked) {
                barangSelect.setAttribute('disabled', 'disabled');
                jumlahBarang.setAttribute('disabled', 'disabled');
            }

            // AutoNumeric
            new AutoNumeric.multiple('.rupiah', {
                digitGroupSeparator: '.',
                decimalCharacter: ',',
                decimalPlaces: 0,
                unformatOnSubmit: true,
                currencySymbol: 'Rp ',
                minimumValue: '0'
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
        });
    </script>
@endpush
