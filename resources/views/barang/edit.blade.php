@extends('layouts.partial.layouts')
@section('page-title', 'Edit Barang')
@section('section-heading', 'Form Edit Barang')
@section('section-row')
    <p>
        Silakan isi form dibawah untuk edit data barang <b>{{ $barang->nama }}</b>.
    </p>
    <div class="border rounded p-3 ">
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
                <label for="nama" class="form-label">Nama Barang</label>
                <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama" required
                    value="{{ $barang->nama }}" autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="jumlah_max" class="form-label">Jumlah Stok Maksimum per-Kemasan</label>
                <input type="number" class="form-control" id="jumlah_max" name="jumlah_max" placeholder="Jumlah Max"
                    required value="{{ $barang->jumlah_max }}" autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="jumlah_min" class="form-label">Jumlah Stok Minimum per-Kemasan</label>
                <input type="number" class="form-control" id="jumlah_min" name="jumlah_min" placeholder="Jumlah Min"
                    required value="{{ $barang->jumlah_min }}" autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="jumlah_unit_per_kemasan" class="form-label">Jumlah Unit per-Kemasan</label>
                <input type="number" class="form-control " id="jumlah_unit_per_kemasan" name="jumlah_unit_per_kemasan"
                    placeholder="Jumlah Unit Per Kemasan" required value="{{ $barang->jumlah_unit_per_kemasan }}"
                    autocomplete="off">

            </div>
            <div class="mb-3">
                <label for="harga_beli_per_kemas" class="form-label">Harga Beli per-Kemas</label>
                <input type="text" class="form-control rupiah" id="harga_beli_per_kemas" name="harga_beli_per_kemas"
                    placeholder="Rp 0" required value="{{ $barang->harga_beli_per_kemas }}" autocomplete="off">

            </div>
            <div class="mb-3">
                <label for="harga_beli_per_unit" class="form-label">Harga Beli per-Unit</label>
                <input type="text" class="form-control rupiah " id="harga_beli_per_unit" name="harga_beli_per_unit"
                    placeholder="Rp 0" required value="{{ $barang->harga_beli_per_unit }}" autocomplete="off">

            </div>
            <div class="mb-3">
                <label for="harga_jual_per_unit" class="form-label">Harga Jual per-Unit</label>
                <input type="text" class="form-control rupiah" id="harga_jual_per_unit" name="harga_jual_per_unit"
                    placeholder="Rp 0" required value="{{ $barang->harga_jual_per_unit }}" autocomplete="off">
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
