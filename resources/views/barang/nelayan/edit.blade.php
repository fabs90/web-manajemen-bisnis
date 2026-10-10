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

            {{-- Nilai default tersembunyi karena nelayan tidak memerlukan stok min/max, kemasan, atau harga beli --}}
            <input type="hidden" name="jumlah_max" value="0">
            <input type="hidden" name="jumlah_min" value="0">
            <input type="hidden" name="jumlah_unit_per_kemasan" value="1">
            <input type="hidden" name="harga_beli_per_kemas" value="0">
            <input type="hidden" name="harga_beli_per_unit" value="0">

            <div class="mb-3">
                <label for="harga_jual_per_unit" class="form-label">Harga Jual per-Kg <span class="text-danger">*</span></label>
                <input type="text" class="form-control rupiah" id="harga_jual_per_unit" name="harga_jual_per_unit"
                    placeholder="Rp 0" required value="{{ $barang->harga_jual_per_unit }}" autocomplete="off" aria-describedby="hjKgHelp">
                <div id="hjKgHelp" class="form-text">Harga jual ke pembeli/tengkulak untuk setiap 1 kilogram (kg).</div>
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
