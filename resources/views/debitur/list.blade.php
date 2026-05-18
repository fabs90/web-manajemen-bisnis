@extends('layouts.partial.layouts')

@section('page-title', 'Debitur & Kreditur | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Manajemen Debitur dan Kreditur')

@section('section-row')
    <div class="container-fluid">
        <!-- Summary and Action Button -->
        <div class="row mb-4">
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Daftar Pelanggan</h4>
                    <p class="text-muted small mb-0">Kelola data debitur (piutang) dan kreditur (hutang) perusahaan Anda.</p>
                </div>
                <button class="btn btn-primary shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#createFormCollapse" aria-expanded="false" aria-controls="createFormCollapse">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Data Baru
                </button>
            </div>
        </div>

        <!-- Collapsible Create Form -->
        <div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="createFormCollapse">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 small text-uppercase fw-bold"><i class="bi bi-pencil-square me-2"></i>Form Tambah Debitur/Kreditur</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('debitur-kreditur.store') }}" method="post" id="createForm">
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Periksa kembali inputan Anda:</h6>
                                        <ul class="mb-0 ps-3">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nama" class="form-label fw-bold small">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama pelanggan" required value="{{ old('nama') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="jenis" class="form-label fw-bold small">Jenis Keanggotaan <span class="text-danger">*</span></label>
                                <select class="form-select" id="jenis" name="jenis" required>
                                    <option value="debitur" {{ old('jenis') == 'debitur' ? 'selected' : '' }}>Debitur (Pelanggan Piutang)</option>
                                    <option value="kreditur" {{ old('jenis') == 'kreditur' ? 'selected' : '' }}>Kreditur (Pemasok Hutang)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="kontak" class="form-label fw-bold small">Nomor Telepon/WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kontak" name="kontak" placeholder="0812xxxx" required value="{{ old('kontak') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold small">Alamat Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="nama@email.com" required value="{{ old('email') }}">
                            </div>
                            <div class="col-12">
                                <label for="alamat" class="form-label fw-bold small">Alamat Lengkap <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="2" placeholder="Alamat lengkap..." required>{{ old('alamat') }}</textarea>
                            </div>
                            <div class="col-12 text-end mt-4">
                                <button type="reset" class="btn btn-light px-4 me-2">Reset</button>
                                <button type="submit" class="btn btn-primary px-5" id="btnSubmit">
                                    <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                                    <span class="btn-text"><i class="bi bi-check-circle me-1"></i> Simpan Data</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table List Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="table-pelanggan">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0">Nama Pelanggan</th>
                                <th class="border-0">Kontak</th>
                                <th class="border-0">Email</th>
                                <th class="border-0">Jenis</th>
                                <th class="border-0 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pelanggan as $item)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-success">{{ $item->nama }}</div>
                                        <small class="text-muted d-block text-truncate" style="max-width: 200px;">{{ $item->alamat }}</small>
                                    </td>
                                    <td>{{ $item->kontak }}</td>
                                    <td>{{ $item->email }}</td>
                                    <td>
                                        @if ($item->jenis == 'debitur')
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill">
                                                <i class="bi bi-person-up me-1"></i> Debitur
                                            </span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                                                <i class="bi bi-person-down me-1"></i> Kreditur
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group shadow-sm">
                                            <form action="{{ route('debitur-kreditur.destroy', $item->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm border-0" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                        Belum ada data debitur atau kreditur.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            $('#table-pelanggan').DataTable({
                searching: true,
                paging: true,
                responsive: true,
            });

            // Loading button on submit
            $('#createForm').on('submit', function() {
                const $btn = $('#btnSubmit');
                $btn.prop('disabled', true);
                $btn.find('.spinner-border').removeClass('d-none');
                $btn.find('.btn-text').text(' Memproses...');
            });
        });

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sukses!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 1800,
                toast: true,
                position: 'top-end'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                showConfirmButton: false,
                timer: 1800,
                toast: true,
                position: 'top-end'
            });
        @endif
    </script>
@endpush
