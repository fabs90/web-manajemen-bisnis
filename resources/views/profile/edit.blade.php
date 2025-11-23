@extends('layouts.partial.layouts')

@section('page-title', 'Edit Profil')
@section('section-heading', 'Profil Pengguna')

@section('section-row')
<div class="container">

    {{-- ERROR --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- SUCCESS MESSAGE --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header fw-bold">Informasi Akun</div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                        value="{{ old('email', auth()->user()->email) }}">
                </div>

            </div>
        </div>


        <div class="card mb-4">
            <div class="card-header fw-bold">Informasi Perusahaan / Identitas</div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', auth()->user()->alamat) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nomor Telepon</label>
                    <input type="text" name="nomor_telepon" class="form-control"
                           value="{{ old('nomor_telepon', auth()->user()->nomor_telepon) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">Logo Perusahaan</label>

                    @if (auth()->user()->logo_perusahaan)
                        <img src="{{ asset('storage/' . auth()->user()->logo_perusahaan) }}"
                             alt="Logo" height="80" class="mb-2 d-block">
                    @endif

                    <input type="file" name="logo_perusahaan" class="form-control">
                    <small class="text-muted">Kosongkan jika tidak ingin mengubah gambar.</small>
                </div>

            </div>
        </div>


        <div class="card mb-4">
            <div class="card-header fw-bold">Ganti Password (Opsional)</div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                </div>

                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>

            </div>
        </div>

        <button class="btn btn-primary w-100" type="submit">
            Simpan Perubahan
        </button>

    </form>
</div>
@endsection
