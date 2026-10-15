@extends('layouts.partial.layouts')
@section('page-title', 'Edit Tangkapan | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Form Edit Hasil Tangkapan')
@section('section-row')
    <p>
        Silakan isi form di bawah untuk mengubah data hasil tangkapan <b>{{ $barang->nama }}</b>.
    </p>
    <div class="border rounded p-3">
        <form action="{{ route('barang.update', $barang->id) }}" method="post">
            @method('PUT')
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
                <label for="kode_barang" class="form-label">Kode Barang (Tidak bisa diubah)</label>
                <input type="text" class="form-control" id="kode_barang" name="kode_barang" placeholder="Kode Barang"
                    required value="{{ $barang->kode_barang }}" autocomplete="off" readonly>
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Hasil Tangkapan <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama" name="nama" placeholder="Contoh: Ikan Tongkol, Cumi-cumi" required
                    value="{{ $barang->nama }}" autocomplete="off">
            </div>

            <div class="mb-3">
                @php
                    $currentSatuan = old('satuan', $barang->satuan ?: 'Kg');
                    $isCustomSatuan = !in_array($currentSatuan, ['Kg', 'Ember', 'Ekor']);
                @endphp
                <label for="satuan_select" class="form-label">Satuan / Kemasan Jual <span class="text-danger">*</span></label>
                <select class="form-select" id="satuan_select" name="satuan_select" required>
                    <option value="Kg" {{ $currentSatuan === 'Kg' ? 'selected' : '' }}>Kilogram (Kg)</option>
                    <option value="Ember" {{ $currentSatuan === 'Ember' ? 'selected' : '' }}>Ember / Bucket</option>
                    <option value="Ekor" {{ $currentSatuan === 'Ekor' ? 'selected' : '' }}>Ekor / Piece</option>
                    <option value="custom" {{ $isCustomSatuan ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>
            <div class="mb-3 {{ $isCustomSatuan ? '' : 'd-none' }}" id="custom_satuan_wrapper">
                <label for="custom_satuan" class="form-label">Tulis Satuan Lainnya <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="custom_satuan" placeholder="Contoh: Keranjang, Box, Pack" value="{{ $isCustomSatuan ? $currentSatuan : '' }}">
            </div>
            <input type="hidden" name="satuan" id="satuan_hidden" value="{{ $currentSatuan }}">

            {{-- Nilai default tersembunyi karena nelayan tidak memerlukan stok min/max, kemasan, atau harga beli --}}
            <input type="hidden" name="jumlah_max" value="0">
            <input type="hidden" name="jumlah_min" value="0">
            <input type="hidden" name="jumlah_unit_per_kemasan" value="1">
            <input type="hidden" name="harga_beli_per_kemas" value="0">
            <input type="hidden" name="harga_beli_per_unit" value="0">

            <div class="mb-3">
                <label for="harga_jual_per_unit" class="form-label" id="label_harga_jual">Harga Jual per-{{ $currentSatuan }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control rupiah" id="harga_jual_per_unit" name="harga_jual_per_unit"
                    placeholder="Rp 0" required value="{{ $barang->harga_jual_per_unit }}" autocomplete="off" aria-describedby="hjKgHelp">
                <div id="hjKgHelp" class="form-text">Harga jual ke pembeli/tengkulak untuk setiap 1 satuan (<span id="text_satuan_help">{{ $currentSatuan }}</span>).</div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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
