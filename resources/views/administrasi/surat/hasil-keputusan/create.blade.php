@extends('layouts.partial.layouts')

@section('page-title', 'Tambah Hasil Keputusan Rapat | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-row')
<div class="container mt-4">
    {{-- Alert sukses --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <strong>Sukses!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    {{-- Alert Error --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Gagal!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white mb-3">
            <strong>FORM INPUT HASIL KEPUTUSAN RAPAT</strong>
        </div>

        <div class="card-body">
            <form action="{{route('administrasi.rapat.hasil-keputusan.store')}}" method="POST">
                @csrf
                {{-- Select Agenda Rapat --}}
                <div class="mb-3">
                    <label class="form-label"><strong>Pilih Agenda Rapat</strong></label>
                    <select name="agenda_rapat_id" class="form-select" required>
                        <option value="" selected disabled>-- Pilih Agenda Rapat --</option>
                        @foreach ($agendaRapat as $agenda)
                            <option value="{{ $agenda->id }}">
                                {{ $agenda->judul_rapat }} - {{ \Carbon\Carbon::parse($agenda->tanggal)->format('d-m-Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Nomor Surat --}}
                <div class="mb-3">
                    <label class="form-label"><strong>Nomor Surat</strong></label>
                    <input type="text" class="form-control"
                           name="nomor_surat" placeholder="Contoh: 002/Kep/Perusahaan/XI/2025" required>
                </div>

                {{-- Keputusan --}}
                <div class="mb-3">
                    <label class="form-label"><strong>Keputusan Rapat</strong></label>
                    <textarea name="keputusan" class="form-control" rows="4"
                        placeholder="Isi keputusan rapat, misalnya:
1. Keputusan 1
2. Keputusan 2
Dst..."
                        required></textarea>
                </div>

                {{-- Kota Tujuan --}}
                <div class="mb-3">
                    <label class="form-label"><strong>Kota Tujuan</strong></label>
                    <input type="text" class="form-control"
                           name="kota_tujuan" placeholder="Contoh: Jakarta" required>
                </div>

                {{-- Tanggal Tujuan --}}
                <div class="mb-3">
                    <label class="form-label"><strong>Tanggal Keputusan</strong></label>
                    <input type="date" class="form-control"
                           name="tanggal_tujuan" required>
                </div>

                {{-- Jabatan PJ --}}
                <div class="mb-3">
                    <label class="form-label"><strong>Jabatan Penanggung Jawab</strong></label>
                    <input type="text" class="form-control"
                           name="jabatan_penanggung_jawab" placeholder="Contoh: Direktur Operasional" required>
                </div>

                {{-- Nama PJ --}}
                <div class="mb-3">
                    <label class="form-label"><strong>Nama Penanggung Jawab</strong></label>
                    <input type="text" class="form-control"
                           name="nama_penanggung_jawab" placeholder="Contoh: Budi Santoso" required>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('administrasi.rapat.hasil-keputusan.index') }}" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Simpan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
