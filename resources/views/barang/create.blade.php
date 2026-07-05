@extends('layouts.partial.layouts')
@section('page-title', 'Tambah Barang | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Form Tambah Barang')
@section('section-row')
    <p>
        Silakan isi form dibawah untuk menambahkan data barang.
    </p>
    <div class="border rounded p-3 ">
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
                <input type="text" class="form-control" id="kode_barang" name="kode_barang" placeholder="Kode Barang"
                    required value="{{ old('kode_barang') }}" autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama" required
                    value="{{ old('nama') }}" autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="jumlah_max" class="form-label">Jumlah Stok Maksimum per-Kemasan <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="jumlah_max" name="jumlah_max" placeholder="Jumlah Max"
                    required value="{{ old('jumlah_max') }}" autocomplete="off" aria-describedby="maxHelp">
                <div id="maxHelp" class="form-text">Batas tertinggi jumlah stok barang dalam bentuk kemasan utuh (misal: dus/box/lusin) yang ideal disimpan di gudang.</div>
            </div>
            <div class="mb-3">
                <label for="jumlah_min" class="form-label">Jumlah Stok Minimum per-Kemasan <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="jumlah_min" name="jumlah_min" placeholder="Jumlah Min"
                    required value="{{ old('jumlah_min') }}" autocomplete="off" aria-describedby="minHelp">
                <div id="minHelp" class="form-text">Batas terendah jumlah kemasan utuh sebelum Anda harus melakukan pembelian ulang (restock).</div>
            </div>
            <div class="mb-3">
                <label for="jumlah_unit_per_kemasan" class="form-label">Jumlah Unit per-Kemasan <span class="text-danger">*</span></label>
                <input type="text" class="form-control " id="jumlah_unit_per_kemasan" name="jumlah_unit_per_kemasan" placeholder="Jumlah Unit Per Kemasan"
                    required value="{{ old('jumlah_unit_per_kemasan') }}" autocomplete="off" aria-describedby="unitHelp">
                <div id="unitHelp" class="form-text">Berapa banyak satuan (pcs) di dalam 1 kemasan? Contoh: Jika 1 dus berisi 24 botol, isi dengan angka <strong>24</strong>.</div>
            </div>
            <div class="mb-3">
                <label for="harga_beli_per_kemas" class="form-label">Harga Beli per-Kemas <span class="text-danger">*</span></label>
                <input type="text" class="form-control rupiah" id="harga_beli_per_kemas" name="harga_beli_per_kemas"
                    placeholder="Rp 0" required value="{{ old('harga_beli_per_kemas') }}" autocomplete="off" aria-describedby="hbKemasHelp">
                <div id="hbKemasHelp" class="form-text">Modal belanja Anda untuk 1 kemasan utuh (misal harga 1 dus).</div>
            </div>
            <div class="mb-3">
                <label for="harga_beli_per_unit" class="form-label">Harga Beli per-Unit <span class="text-danger">*</span></label>
                <input type="text" class="form-control rupiah " id="harga_beli_per_unit" name="harga_beli_per_unit"
                    placeholder="Rp 0" required value="{{ old('harga_beli_per_unit') }}" autocomplete="off" aria-describedby="hbUnitHelp">
                <div id="hbUnitHelp" class="form-text">Modal harga per-satuan/pcs (Diperoleh dari: Harga Beli per-Kemas dibagi Jumlah Unit).</div>
            </div>
            <div class="mb-3">
                <label for="harga_jual_per_unit" class="form-label">Harga Jual per-Unit <span class="text-danger">*</span></label>
                <input type="text" class="form-control rupiah" id="harga_jual_per_unit" name="harga_jual_per_unit"
                    placeholder="Rp 0" required value="{{ old('harga_jual_per_unit') }}" autocomplete="off" aria-describedby="hjUnitHelp">
                <div id="hjUnitHelp" class="form-text">Harga jual ke pelanggan untuk setiap 1 satuan barang (pcs).</div>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
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
