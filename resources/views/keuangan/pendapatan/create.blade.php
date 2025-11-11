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
                        <option value="piutang" {{ old('jenis_pendapatan') == 'piutang' ? 'selected' : '' }}>Piutang (Menambah/Membuat Baru)
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

                <div id="jumlah_piutang_container" class="mb-3 d-none">
                    <label for="jumlah_piutang" class="form-label">Jumlah Piutang Non-barang (Opsional)<span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="jumlah_piutang" id="jumlah_piutang" min="1" placeholder="Masukkan jumlah piutang">
                    <small class="text-muted">Akan ditambahkan ke total piutang.</small>
                </div>

                <div id="jumlah_kredit_container" class="mb-3 d-none">
                    <label for="jumlah_kredit" class="form-label">Jumlah Kredit</label>
                    <input type="number" class="form-control" name="jumlah_kredit" id="jumlah_kredit" min="1" placeholder="Masukkan jumlah kredit/pelunasan" >
                </div>


                {{-- Barang Terjual --}}
                <div class="mb-3">
                    <label class="form-label">Barang Terjual</label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="barang_terjual_check"
                            name="barang_terjual_check" value="1"
                            {{ old('barang_terjual_check') ? 'checked' : '' }}>
                        <label class="form-check-label" for="barang_terjual_check">Tidak ada barang terjual</label>
                    </div>
                    <div id="barang-section" class="d-none">
                        <div id="barang-list">
                            <!-- Barang rows will be added here dynamically -->
                        </div>
                        <button type="button" id="tambah-barang" class="btn btn-secondary btn-sm mt-2">
                            <i class="bi bi-plus"></i> Tambah Barang
                        </button>
                    </div>
                </div>

                {{-- Jumlah Penjualan --}}
                <div class="mb-3">
                    <label for="jumlah_penjualan" class="form-label">Jumlah Penjualan (Otomatis)</label>
                    <input type="number" id="jumlah_penjualan" name="jumlah" class="form-control" value="0" readonly required>
                    @error('jumlah')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!--Lain lain -->
                <div class="mb-3">
                    <label for="biaya_lain" class="form-label">Lain-lain</label>
                    <input type="text" name="biaya_lain" id="biaya_lain"
                        class="form-control rupiah @error('biaya_lain') is-invalid @enderror"
                        value="{{ old('biaya_lain') }}" placeholder="Biaya Lain (Opsional)">
                    @error('biaya_lain')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!--Bunga bank-->
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
    const jumlahPiutangContainer = document.getElementById('jumlah_piutang_container');
    const jumlahKreditContainer = document.getElementById('jumlah_kredit_container');

    const barangCheck = document.getElementById('barang_terjual_check');
    const barangSection = document.getElementById('barang-section');
    const barangList = document.getElementById('barang-list');
    const tambahBarangBtn = document.getElementById('tambah-barang');
    const jumlahPenjualan = document.getElementById('jumlah_penjualan');

    const piutangOptions = Array.from(document.querySelectorAll('#piutang_aktif option.piutang-option'));
    const hutangOptions = Array.from(document.querySelectorAll('#hutang_aktif option.hutang-option'));

    let barangIndex = 0;

    const barangTemplate = `
        <div class="barang-row row mb-2 align-items-end" data-index="{index}">
            <div class="col-md-4">
                <label class="form-label">Barang</label>
                <select name="barang_terjual[{index}]" class="form-control barang-select" required>
                    <option value="" disabled selected>-- Pilih Barang --</option>
                    @foreach ($barang as $item)
                                        <option value="{{ $item->id }}" data-harga="{{ $item->harga_jual_per_unit }}">
                                            {{ $item->nama }}
                                            - Rp {{ number_format($item->harga_jual_per_unit, 0, ',', '.') }}
                                            - Saldo: {{ $item->getSaldoAkhir() }}
                                        </option>
                                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Jumlah</label>
                <input type="number" name="jumlah_barang_dijual[{index}]" class="form-control jumlah-input" min="1" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Potongan</label>
                <input type="number" name="potongan_pembelian[{index}]" class="form-control potongan-input" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label">Subtotal</label>
                <input type="number" class="form-control subtotal-input" readonly>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm hapus-barang">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;

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
        jumlahPiutangContainer.classList.add('d-none');
        jumlahKreditContainer.classList.add('d-none');
        piutangSelect.setAttribute('disabled', 'disabled');
        hutangSelect.setAttribute('disabled', 'disabled');

        if (jenis === 'piutang' || jenis === 'kredit' || jenis === 'pelunasan_piutang') {
            debiturCheckContainer.classList.remove('d-none');
        }

        if (adaDebitur && (jenis === 'piutang' || jenis === 'kredit' || jenis === 'pelunasan_piutang')) {
            debiturSection.classList.remove('d-none');
            namaPelanggan.removeAttribute('disabled');
            namaPelanggan.setAttribute('required', 'required');

            resetSelect(piutangSelect, '-- Pilih Piutang Aktif --');
            resetSelect(hutangSelect, '-- Pilih Hutang Aktif --');

            if (namaPelanggan.value) {
                showRelatedSelect(jenis, namaPelanggan.value);
            }
        }

        if (jenis === 'piutang') {
            jumlahPiutangContainer.classList.remove('d-none');
        } else if (jenis === 'kredit') {
            jumlahKreditContainer.classList.remove('d-none');
        }

        hitungTotal();
    }

    function resetSelect(select, placeholder) {
        select.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
        select.setAttribute('disabled', 'disabled');
    }

    function showRelatedSelect(jenis, debiturId) {
        if (jenis === 'piutang' || jenis === 'pelunasan_piutang') {
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

    function toggleBarangSection() {
        if (barangCheck.checked) {
            barangSection.classList.add('d-none');
            barangList.innerHTML = '';
            jumlahPenjualan.value = 0;
        } else {
            barangSection.classList.remove('d-none');
            if (barangList.children.length === 0) {
                tambahBarang();
            }
        }
    }

    function tambahBarang() {
        const rowHtml = barangTemplate.replace(/{index}/g, barangIndex);
        barangList.insertAdjacentHTML('beforeend', rowHtml);
        const newRow = barangList.lastElementChild;
        attachRowEvents(newRow);
        barangIndex++;
    }

    function attachRowEvents(row) {
        const select = row.querySelector('.barang-select');
        const jumlahInput = row.querySelector('.jumlah-input');
        const potonganInput = row.querySelector('.potongan-input');
        const subtotalInput = row.querySelector('.subtotal-input');
        const hapusBtn = row.querySelector('.hapus-barang');

        function hitungSubtotal() {
            const selectedOption = select.options[select.selectedIndex];
            const harga = selectedOption ? parseFloat(selectedOption.getAttribute('data-harga')) || 0 : 0;
            const qty = parseInt(jumlahInput.value) || 0;
            const potongan = parseFloat(potonganInput.value) || 0;
            const subtotal = Math.max((harga * qty) - potongan, 0);
            subtotalInput.value = subtotal;
            hitungTotal();
        }

        select.addEventListener('change', hitungSubtotal);
        jumlahInput.addEventListener('input', hitungSubtotal);
        potonganInput.addEventListener('input', hitungSubtotal);
        hapusBtn.addEventListener('click', function() {
            row.remove();
            hitungTotal();
        });
    }

    function hitungTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal-input').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        jumlahPenjualan.value = total;

    }

    // Saat piutang aktif dipilih
    piutangSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const nominal = selectedOption ? parseFloat(selectedOption.getAttribute('data-nominal')) || 0 : 0;
        if (nominal > 0) {
            document.getElementById('jumlah_piutang').value = nominal;
        }
    });

    // Saat hutang aktif dipilih
    hutangSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const nominal = selectedOption ? parseFloat(selectedOption.getAttribute('data-nominal')) || 0 : 0;
        if (nominal > 0) {
            document.getElementById('jumlah_kredit').value = nominal;
        }
    });

    jenisSelect.addEventListener('change', updateForm);
    adaDebiturCheck.addEventListener('change', updateForm);
    namaPelanggan.addEventListener('change', function() {
        const jenis = jenisSelect.value;
        if (this.value && (jenis === 'piutang' || jenis === 'kredit' || jenis === 'pelunasan_piutang')) {
            showRelatedSelect(jenis, this.value);
        } else {
            piutangContainer.classList.add('d-none');
            hutangContainer.classList.add('d-none');
        }
    });

    barangCheck.addEventListener('change', toggleBarangSection);
    tambahBarangBtn.addEventListener('click', tambahBarang);

    updateForm();
    toggleBarangSection();

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
