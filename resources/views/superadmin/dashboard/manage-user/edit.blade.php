@extends('layouts.superadmin-partial.layouts')

@section('page-title', 'Edit User | TRANSDIGITAL')
@section('section-heading', 'Edit User')
@section('section-row')

<div class="row">
    <div class="col-md-8 col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Form Edit Pengguna</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.user-management.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group mb-3">
                        <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="nomor_telepon">Nomor Telepon</label>
                        <input type="text" class="form-control @error('nomor_telepon') is-invalid @enderror" id="nomor_telepon" name="nomor_telepon" value="{{ old('nomor_telepon', $user->nomor_telepon) }}">
                        @error('nomor_telepon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="alamat">Alamat</label>
                        <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3">{{ old('alamat', $user->alamat) }}</textarea>
                        @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="role">Role <span class="text-danger">*</span></label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="ukm" {{ old('role', $user->role) == 'ukm' ? 'selected' : '' }}>UKM</option>
                            <option value="nelayan" {{ old('role', $user->role) == 'nelayan' ? 'selected' : '' }}>Nelayan</option>
                            <option value="koperasi" {{ old('role', $user->role) == 'koperasi' ? 'selected' : '' }}>Koperasi</option>
                            <option value="superadmin" {{ old('role', $user->role) == 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="is_verified">Status Verifikasi <span class="text-danger">*</span></label>
                        <select class="form-select @error('is_verified') is-invalid @enderror" id="is_verified" name="is_verified" required>
                            <option value="1" {{ old('is_verified', $user->is_verified) == 1 ? 'selected' : '' }}>Verified (Ya)</option>
                            <option value="0" {{ old('is_verified', $user->is_verified) == 0 ? 'selected' : '' }}>Unverified (Tidak)</option>
                        </select>
                        @error('is_verified')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('superadmin.user-management.show', $user->id) }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
