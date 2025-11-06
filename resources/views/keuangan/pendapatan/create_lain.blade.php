@extends('layouts.partial.layouts')
@section('page-title', 'Tambah Pendapatan Lain-Lain | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'Pendapatan Lain-Lain')
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

        <form action="{{ route('keuangan.pendapatan.store_lain') }}" method="POST">
            @csrf

            {{-- Uraian Pendapatan --}}
            <div class="mb-3">
                <label for="uraian_pendapatan" class="form-label">Uraian Pendapatan Lain-Lain<span class="text-danger">*</span></label>
                <input type="text" name="uraian_pendapatan" id="uraian_pendapatan" class="form-control @error('uraian_pendapatan') is-invalid @enderror" value="{{ old('uraian_pendapatan') }}" placeholder="Contoh: Bunga bank, denda, hadiah" required>
                @error('uraian_pendapatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Jumlah (wajib) --}}
            <div class="mb-3">
                <label for="jumlah" class="form-label">Jumlah Pendapatan Lain-Lain <span class="text-danger">*</span></label>
                <input type="text" name="jumlah" id="jumlah" class="form-control rupiah @error('jumlah') is-invalid @enderror" value="{{ old('jumlah') }}" placeholder="Masukkan jumlah" required>
                @error('jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Tanggal --}}
            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
                <input type="date" name="tanggal" id="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Potongan, Bunga Bank, dll (opsional) --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="potongan_pembelian" class="form-label">Potongan</label>
                    <input type="text" name="potongan_pembelian" id="potongan_pembelian" class="form-control rupiah @error('potongan_pembelian') is-invalid @enderror" value="{{ old('potongan_pembelian') }}" placeholder="Opsional">
                    @error('potongan_pembelian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="bunga_bank" class="form-label">Bunga Bank</label>
                    <input type="text" name="bunga_bank" id="bunga_bank" class="form-control rupiah @error('bunga_bank') is-invalid @enderror" value="{{ old('bunga_bank') }}" placeholder="Opsional">
                    @error('bunga_bank') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4 text-end">
                <a href="{{ route('keuangan.pendapatan.list') }}" class="btn btn-secondary me-2">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan Pendapatan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // AutoNumeric untuk semua input rupiah
    new AutoNumeric.multiple('.rupiah', {
        digitGroupSeparator: '.',
        decimalCharacter: ',',
        decimalPlaces: 0,
        unformatOnSubmit: true,
        currencySymbol: 'Rp ',
        minimumValue: '0'
    });

    // SweetAlert feedback
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Sukses!',
            text: "{{ session('success') }}",
            toast: true,
            position: 'top-end',
            timer: 3000,
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
            timer: 4000,
            showConfirmButton: false
        });
    @endif
});
</script>
@endpush
