@extends('layouts.partial.layouts')
@section('page-title', 'Kartu Gudang')
@section('section-heading', 'Form Kartu Gudang')
@section('section-row')
    <p>
        Silakan isi form dibawah untuk mengatur kartu gudang item: <b>{{ $barang->nama }}</b>.
    </p>
    <div class="border rounded p-3 ">
        <form action="{{ route('kartu-gudang.store', ['barang_id' => $barang->id]) }}" method="post">
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
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" placeholder="Tanggal" required
                    value="{{ old('tanggal') }}">
            </div>
            <div class="mb-3">
                <label for="uraian" class="form-label">Uraian</label>
                <input type="text" class="form-control" id="uraian" name="uraian" placeholder="Uraian" required
                    value="{{ old('uraian') }}">
            </div>
            <div class="mb-3">
                <label for="diterima" class="form-label">Diterima</label>
                <input type="number" class="form-control" id="diterima" name="diterima" placeholder="Jumlah Barang/Kemasan Diterima"
                    value="{{ old('diterima') }}">
            </div>
            <div class="mb-3">
                <label for="dikeluarkan" class="form-label">Dikeluarkan</label>
                <input type="number" class="form-control" id="dikeluarkan" name="dikeluarkan" placeholder="Jumlah Barang/Kemasan Dikeluarkan"
                    value="{{ old('dikeluarkan') ?? 0 }}">
            </div>
            <div class="mb-3">
                <label for="saldo_persatuan" class="form-label">Jumlah Stok Diterima per-Satuan</label>
                <input type="number" class="form-control" id="saldo_persatuan" name="saldo_persatuan"
                    placeholder="Jumlah Stok per-Satuan" required value="{{ old('saldo_persatuan') }}">
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
                text: '{ session('success') }',
                showConfirmButton: false,
                timer: 1800,
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
