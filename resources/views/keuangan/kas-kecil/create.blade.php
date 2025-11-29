@extends('layouts.partial.layouts')
@section('page-title', 'Form Pengisian Kas Kecil | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Form Pengisian Kas Kecil')

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

        <form action="{{route('keuangan.pengeluaran-kas-kecil.store')}}" method="POST">
            @csrf
            {{-- Uraian Pengisian Kas Kecil --}}
            <div class="mb-3">
                <label for="uraian" class="form-label">Uraian Pengisian <span class="text-danger">*</span></label>
                <input type="text" name="uraian" id="uraian" class="form-control @error('uraian') is-invalid @enderror" placeholder="Pengisian kas kecil dari kas besar" value="{{ old('uraian') }}" required>
                @error('uraian') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Jumlah Pengisian --}}
            <div class="mb-3">
                <label for="jumlah" class="form-label">Jumlah Pengisian <span class="text-danger">*</span></label>
                <input type="text" name="jumlah" id="jumlah" class="form-control rupiah @error('jumlah') is-invalid @enderror" placeholder="Masukkan jumlah" value="{{ old('jumlah') }}" required>
                @error('jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Tanggal Pengisian --}}
            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal Pengisian <span class="text-danger">*</span></label>
                <input type="date" name="tanggal" id="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mt-4 text-end">
                <a href="{{ route('keuangan.pengeluaran-kas-kecil.index') }}" class="btn btn-secondary me-2">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan Pengisian</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {

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
            toast: true,
            position: 'top-end',
            timer: 2800,
            showConfirmButton: false
        });
    @endif

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: "{{ session('error') }}",
            toast: true,
            position: 'top-end',
            timer: 3500,
            showConfirmButton: false
        });
    @endif
});
</script>
@endpush
