@extends('layouts.partial.layouts')
@section('page-title', 'Form Jadwal Perjalanan | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', isset($jadwal) ? 'Edit Jadwal Perjalanan' : 'Tambah Jadwal Perjalanan')

@section('section-row')
<div class="card shadow-sm rounded-3">
    <div class="card-header">
        <h5 class="mb-0">{{ isset($jadwal) ? 'Edit Jadwal' : 'Tambah Jadwal' }}</h5>
    </div>

    <div class="card-body">
        <form
            action="#"
            method="POST"
        >
            @csrf
            @if(isset($jadwal))
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nama_supir" class="form-label">Nama Supir</label>
                    <input type="text" name="nama_supir" id="nama_supir" class="form-control"
                        value="{{ old('nama_supir', $jadwal->nama_supir ?? '') }}" required>
                </div>

                <div class="col-md-6">
                    <label for="kendaraan" class="form-label">Kendaraan + Nopol</label>
                    <input type="text" name="kendaraan" id="kendaraan" class="form-control"
                        value="{{ old('kendaraan', $jadwal->kendaraan ?? '') }}" required>
                </div>

                <div class="col-md-6">
                    <label for="tujuan" class="form-label">Tujuan</label>
                    <input type="text" name="tujuan" id="tujuan" class="form-control"
                        value="{{ old('tujuan', $jadwal->tujuan ?? '') }}" required>
                </div>

                <div class="col-md-3">
                    <label for="tanggal_berangkat" class="form-label">Tanggal Berangkat</label>
                    <input type="date" name="tanggal_berangkat" id="tanggal_berangkat" class="form-control"
                        value="{{ old('tanggal_berangkat', $jadwal->tanggal_berangkat ?? '') }}" required>
                </div>

                <div class="col-md-3">
                    <label for="tanggal_kembali" class="form-label">Tanggal Kembali</label>
                    <input type="date" name="tanggal_kembali" id="tanggal_kembali" class="form-control"
                        value="{{ old('tanggal_kembali', $jadwal->tanggal_kembali ?? '') }}">
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="berangkat" {{ old('status', $jadwal->status ?? '') == 'berangkat' ? 'selected' : '' }}>Berangkat</option>
                        <option value="selesai" {{ old('status', $jadwal->status ?? '') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="3" class="form-control">{{ old('keterangan', $jadwal->keterangan ?? '') }}</textarea>
                </div>

                <div class="col-md-12 text-end mt-3">
                    <a href="{{ route('jadwal-perjalanan.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success">
                        {{ isset($jadwal) ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
