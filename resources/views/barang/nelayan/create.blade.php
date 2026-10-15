@extends('layouts.partial.layouts')
@section('page-title', 'Tambah Tangkapan | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Form Tambah Hasil Tangkapan')
@section('section-row')
    <p>
        Silakan isi form di bawah untuk menambahkan data hasil tangkapan.
    </p>
    <div class="border rounded p-3">
        <form action="{{ route('barang.store') }}" method="post">
            @csrf
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="mb-3">
                <label for="kode_barang" class="form-label">Kode Barang <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="kode_barang" name="kode_barang" placeholder="Contoh: IKN-001"
                    required value="{{ old('kode_barang') }}" autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Hasil Tangkapan <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama" name="nama" placeholder="Contoh: Ikan Tongkol, Cumi-cumi" required
                    value="{{ old('nama') }}" autocomplete="off">
            </div>

            <div class="mb-3">
                <label for="satuan_select" class="form-label">Satuan / Kemasan Jual <span class="text-danger">*</span></label>
                <select class="form-select" id="satuan_select" name="satuan_select" required>
                    <option value="Kg" {{ old('satuan', 'Kg') === 'Kg' ? 'selected' : '' }}>Kilogram (Kg)</option>
                    <option value="Ember" {{ old('satuan') === 'Ember' ? 'selected' : '' }}>Ember / Bucket</option>
                    <option value="Ekor" {{ old('satuan') === 'Ekor' ? 'selected' : '' }}>Ekor / Piece</option>
                    <option value="custom" {{ !in_array(old('satuan', 'Kg'), ['Kg', 'Ember', 'Ekor']) && old('satuan') ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>
            <div class="mb-3 {{ !in_array(old('satuan', 'Kg'), ['Kg', 'Ember', 'Ekor']) && old('satuan') ? '' : 'd-none' }}" id="custom_satuan_wrapper">
                <label for="custom_satuan" class="form-label">Tulis Satuan Lainnya <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="custom_satuan" placeholder="Contoh: Keranjang, Box, Pack" value="{{ !in_array(old('satuan', 'Kg'), ['Kg', 'Ember', 'Ekor']) ? old('satuan') : '' }}">
            </div>
            <input type="hidden" name="satuan" id="satuan_hidden" value="{{ old('satuan', 'Kg') }}">

            {{-- Nilai default tersembunyi karena nelayan tidak memerlukan stok min/max, kemasan, atau harga beli --}}
            <input type="hidden" name="jumlah_max" value="0">
            <input type="hidden" name="jumlah_min" value="0">
            <input type="hidden" name="jumlah_unit_per_kemasan" value="1">
            <input type="hidden" name="harga_beli_per_kemas" value="0">
            <input type="hidden" name="harga_beli_per_unit" value="0">

            <div class="mb-3">
                <label for="harga_jual_per_unit" class="form-label" id="label_harga_jual">Harga Jual per-Kg <span class="text-danger">*</span></label>
                <input type="text" class="form-control rupiah" id="harga_jual_per_unit" name="harga_jual_per_unit"
                    placeholder="Rp 0" required value="{{ old('harga_jual_per_unit') }}" autocomplete="off" aria-describedby="hjKgHelp">
                <div id="hjKgHelp" class="form-text">Harga jual ke pembeli/tengkulak untuk setiap 1 satuan (<span id="text_satuan_help">Kg</span>).</div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Tangkapan</button>
            <a href="{{ route('barang.index') }}" class="btn btn-secondary ms-2">Batal</a>
        </form>
    </div>

@endsection
@push('script')
    <script>
        const options = {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 0,
            unformatOnSubmit: true,
            currencySymbol: 'Rp ',
            currencySymbolPlacement: 'p',
            minimumValue: 0,
        };

        function initRupiahFields() {
            document.querySelectorAll('.rupiah').forEach(el => {
                if (!el.hasAttribute('data-autonumeric-initialized')) {
                    new AutoNumeric(el, options);
                    el.setAttribute('data-autonumeric-initialized', 'true');
                }
            });
        }

        initRupiahFields();

        const satuanSelect = document.getElementById('satuan_select');
        const customSatuanWrapper = document.getElementById('custom_satuan_wrapper');
        const customSatuanInput = document.getElementById('custom_satuan');
        const satuanHidden = document.getElementById('satuan_hidden');
        const labelHargaJual = document.getElementById('label_harga_jual');
        const textSatuanHelp = document.getElementById('text_satuan_help');

        function updateSatuanUI() {
            let selectedVal = satuanSelect.value;
            let displaySatuan = selectedVal;

            if (selectedVal === 'custom') {
                customSatuanWrapper.classList.remove('d-none');
                displaySatuan = customSatuanInput.value.trim() || 'Satuan';
                satuanHidden.value = customSatuanInput.value.trim() || 'Unit';
            } else {
                customSatuanWrapper.classList.add('d-none');
                satuanHidden.value = selectedVal;
            }

            labelHargaJual.innerHTML = `Harga Jual per-${displaySatuan} <span class="text-danger">*</span>`;
            textSatuanHelp.textContent = displaySatuan;
        }

        satuanSelect.addEventListener('change', updateSatuanUI);
        customSatuanInput.addEventListener('input', updateSatuanUI);
        updateSatuanUI();

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sukses!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 2500,
                toast: true,
                position: 'top-end',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            });
        @endif
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: '{{ session('error') }}',
                showConfirmButton: false,
                timer: 3000,
                toast: true,
                position: 'top-end',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            });
        @endif
    </script>
@endpush
