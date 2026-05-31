@extends('layouts.superadmin-partial.layouts')

@section('page-title', 'Detail User | Digitrans')
@section('section-heading', 'Detail User')
@section('section-row')

<div class="row">
    <div class="col-md-8 col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Informasi Pengguna</h4>
                <a href="{{ route('superadmin.user-management.edit', ['id' => $user->id]) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil-square"></i> Edit Data
                </a>
            </div>
            <div class="card-body">
                <table class="table table-borderless" id="">
                    <tbody>
                        <tr>
                            <th width="30%">Nama Lengkap</th>
                            <td>: {{ $user->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>: {{ $user->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Nomor Telepon</th>
                            <td>: {{ $user->nomor_telepon ?? $user->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>: {{ $user->alamat ?? $user->address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="align-top">Logo Perusahaan</th>
                            <td>
                                <div class="d-flex align-items-start gap-2">
                                    <span>: </span>
                                    @if(isset($user->logo_perusahaan) || isset($user->logo))
                                        <img src="{{ asset('storage/' . ($user->logo_perusahaan ?? $user->logo)) }}" alt="Logo Perusahaan" class="img-thumbnail" style="max-height: 100px; max-width: 150px; object-fit: contain;">
                                    @else
                                        <span class="text-muted fst-italic">Belum ada logo</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td>
                                @php
                                    $roleColor = match(strtolower($user->role ?? '')) {
                                        'superadmin' => 'bg-dark',
                                        'ukm' => 'bg-primary',
                                        'nelayan' => 'bg-info text-dark',
                                        'koperasi' => 'bg-warning text-dark',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                : <span class="badge {{ $roleColor }}">{{ ucfirst($user->role ?? '-') }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Status Verifikasi</th>
                            <td>
                                :
                                @if(isset($user->is_verified) && $user->is_verified == 1)
                                    <span class="badge bg-success">Verified</span>
                                @else
                                    <span class="badge bg-danger">Unverified</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Bergabung</th>
                            <td>: {{ isset($user->created_at) ? \Carbon\Carbon::parse($user->created_at)->translatedFormat('d F Y H:i') : '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <!-- Arahkan kembali ke daftar User -->
                <a href="{{ route('superadmin.user-management.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
