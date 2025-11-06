@extends('layouts.partial.layouts')
@section('page-title', 'Tambah Debitur/Kreditur | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Form Tambah Debitur/Kreditur')
@section('section-row')
    <p>
        Silakan isi form dibawah untuk menambahkan data debitur/kreditur.
    </p>
    <div class="border rounded p-3 ">
        <form action="{{ route('debitur-kreditur.store') }}" method="post">
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
                <label for="nama" class="form-label">Nama Debitur/Kreditur<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama" required
                    value="{{ old('nama') }}">
            </div>
            <div class="mb-3">
                <label for="kontak" class="form-label">Kontak Debitur/Kreditur<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="kontak" name="kontak" placeholder="Kontak" required
                    value="{{ old('kontak') }}">
            </div>
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat Debitur/Kreditur<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="alamat" name="alamat" placeholder="Alamat" required
                    value="{{ old('alamat') }}">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Debitur/Kreditur<span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required
                    value="{{ old('email') }}">
            </div>
            <div class="mb-3">
                <label for="jenis" class="form-label">Jenis (Debitur/Kreditur)<span class="text-danger">*</span></label>
                <select class="form-select" id="jenis" name="jenis" required>
                    <option value="debitur">Debitur</option>
                    <option value="kreditur">Kreditur</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

@endsection
@push('script')
    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sukses!',
                text: '{{ session('success') }}',
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
