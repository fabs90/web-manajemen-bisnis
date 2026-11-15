@extends('layouts.partial.layouts')
@section('page-title', 'Edit Penerimaan Kas Perusahaan | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'Edit Penerimaan Kas Perusahaan')
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

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Gagal!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('keuangan.pendapatan.update', $entry->id) }}" method="POST" id="pendapatan-edit-form">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="uraian_pendapatan" class="form-label">Uraian Penerimaan Kas Perusahaan<span class="text-danger">*</span></label>
                    <input type="text" name="uraian_pendapatan" id="uraian_pendapatan"
                        class="form-control @error('uraian_pendapatan') is-invalid @enderror"
                        value="{{ old('uraian_pendapatan', $entry->description) }}" required>
                    @error('uraian_pendapatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="tanggal" class="form-label">Tanggal Transaksi<span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" id="tanggal"
                        class="form-control @error('tanggal') is-invalid @enderror"
                        value="{{ old('tanggal', $entry->date?->format('Y-m-d')) }}" required>
                    @error('tanggal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @php
                    $currentJenis = old('jenis_pendapatan', $jenisPendapatan);
                @endphp

                <div class="mb-3">
                    <label for="jenis_pendapatan" class="form-label">Jenis Penerimaan Kas Perusahaan<span class="text-danger">*</span></label>
                    <select name="jenis_pendapatan" id="jenis_pendapatan"
                        class="form-select @error('jenis_pendapatan') is-invalid @enderror" required>
                        <option value="lain_lain" {{ $currentJenis === 'lain_lain' ? 'selected' : '' }}>
                            Pendapatan Lain-Lain
                        </option>
                        <option value="kredit" {{ $currentJenis === 'kredit' ? 'selected' : '' }}>
                            Pelunasan Hutang dari Debitur
                        </option>
                    </select>
                    @error('jenis_pendapatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div id="debitur-section" class="{{ $currentJenis === 'kredit' ? '' : 'd-none' }}">
                    <hr>
                    <h5 class="mb-3">Detail Debitur</h5>

                    <div class="mb-3">
                        <label for="nama_pelanggan" class="form-label">Nama Debitur<span class="text-danger">*</span></label>
                        <select name="nama_pelanggan" id="nama_pelanggan"
                            class="form-select @error('nama_pelanggan') is-invalid @enderror"
                            {{ $currentJenis === 'kredit' ? '' : 'disabled' }}>
                            <option value="" disabled {{ old('nama_pelanggan', $selectedPelangganId) ? '' : 'selected' }}>-- Pilih Debitur --</option>
                            @foreach ($debitur as $pelanggan)
                                <option value="{{ $pelanggan->id }}"
                                    {{ old('nama_pelanggan', $selectedPelangganId) == $pelanggan->id ? 'selected' : '' }}>
                                    {{ $pelanggan->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('nama_pelanggan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="jumlah_kredit" class="form-label">Jumlah Pelunasan Piutang<span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_kredit" id="jumlah_kredit" min="0"
                            class="form-control @error('jumlah_kredit') is-invalid @enderror"
                            value="{{ old('jumlah_kredit', $jumlahKredit) }}"
                            {{ $currentJenis === 'kredit' ? '' : 'disabled' }}>
                        @error('jumlah_kredit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if ($listPiutang->isNotEmpty())
                            <small class="text-muted d-block mt-1">
                                Piutang aktif tersedia:
                                @foreach ($listPiutang as $piutang)
                                    <span class="badge bg-light text-dark border">
                                        {{ $piutang->pelanggan->nama ?? '-' }}: Rp {{ number_format($piutang->saldo, 0, ',', '.') }}
                                    </span>
                                @endforeach
                            </small>
                        @endif
                    </div>
                </div>

                <div id="jumlah-section" class="mb-3 {{ $currentJenis === 'lain_lain' ? '' : 'd-none' }}">
                    <label for="jumlah" class="form-label">Jumlah Penerimaan<span class="text-danger">*</span></label>
                    <input type="number" name="jumlah" id="jumlah" min="0"
                        class="form-control @error('jumlah') is-invalid @enderror"
                        value="{{ old('jumlah', $jumlah) }}">
                    @error('jumlah')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="biaya_lain" class="form-label">Biaya Lain / Tambahan (Opsional)</label>
                    <input type="number" name="biaya_lain" id="biaya_lain" min="0"
                        class="form-control @error('biaya_lain') is-invalid @enderror"
                        value="{{ old('biaya_lain', $biayaLain) }}">
                    @error('biaya_lain')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('keuangan.pendapatan.list') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const jenisSelect = document.getElementById('jenis_pendapatan');
            const debiturSection = document.getElementById('debitur-section');
            const namaPelanggan = document.getElementById('nama_pelanggan');
            const jumlahKredit = document.getElementById('jumlah_kredit');
            const jumlahSection = document.getElementById('jumlah-section');
            const jumlahInput = document.getElementById('jumlah');

            function toggleSections() {
                const jenis = jenisSelect.value;
                if (jenis === 'kredit') {
                    debiturSection.classList.remove('d-none');
                    namaPelanggan.disabled = false;
                    jumlahKredit.disabled = false;
                    jumlahSection.classList.add('d-none');
                    jumlahInput.value = 0;
                } else {
                    debiturSection.classList.add('d-none');
                    namaPelanggan.disabled = true;
                    jumlahKredit.disabled = true;
                    jumlahSection.classList.remove('d-none');
                }
            }

            jenisSelect.addEventListener('change', toggleSections);
        });
    </script>
@endsection
