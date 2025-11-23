@extends('layouts.partial.layouts')

@section('page-title',
    'Detail Agenda Janji Telpon - {{ $agenda->id }} | Digitrans - Pengelolaan Administrasi dan
    Transaksi Bisnis')
@section('section-heading', 'Form Agenda Janji Telpon')

@section('section-row')

    <div class="container mt-4">
        {{-- Alert sukses --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Sukses!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Alert error --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Gagal!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow">
            <div class="card-header bg-primary text-white fw-bold">
                Form Agenda Janji Telpon
            </div>
            <div class="card-body mt-3">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Tanggal Panggilan Telpon</label>
                            <input type="date" name="tgl_panggilan"
                                class="form-control @error('tgl_panggilan') is-invalid @enderror"
                                value="{{ $agenda->tgl_panggilan }}">
                            @error('tgl_panggilan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Waktu Panggilan</label>
                            <input type="time" name="waktu_panggilan"
                                class="form-control @error('waktu_panggilan') is-invalid @enderror"
                                value="{{ $agenda->waktu_panggilan }}">
                            @error('waktu_panggilan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Nama Penelpon</label>
                            <input type="text" name="nama_penelpon"
                                class="form-control @error('nama_penelpon') is-invalid @enderror"
                                value="{{ $agenda->nama_penelpon }}">
                            @error('nama_penelpon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Nama Perusahaan</label>
                            <input type="text" name="perusahaan"
                                class="form-control @error('perusahaan') is-invalid @enderror"
                                value="{{ $agenda->perusahaan }}">
                            @error('perusahaan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Nomor Telpon</label>
                            <input type="text" name="nomor_telpon"
                                class="form-control @error('nomor_telpon') is-invalid @enderror"
                                value="{{ $agenda->nomor_telpon }}">
                            @error('nomor_telpon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h5 class="fw-bold mt-4">JADWAL TELPON</h5>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Tanggal</label>
                            <input type="date" name="jadwal_tanggal"
                                class="form-control @error('jadwal_tanggal') is-invalid @enderror"
                                value="{{ $agenda->jadwal_tanggal }}">
                            @error('jadwal_tanggal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Waktu</label>
                            <input type="time" name="jadwal_waktu"
                                class="form-control @error('jadwal_waktu') is-invalid @enderror"
                                value="{{ $agenda->jadwal_waktu }}">
                            @error('jadwal_waktu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Dengan</label>
                            <input type="text" name="jadwal_dengan"
                                class="form-control @error('jadwal_dengan') is-invalid @enderror"
                                value="{{ $agenda->jadwal_dengan }}">
                            @error('jadwal_dengan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h5 class="fw-bold mt-4">KEPERLUAN</h5>
                    <textarea name="keperluan" rows="3" class="form-control @error('keperluan') is-invalid @enderror">{{ $agenda->keperluan }}</textarea>
                    @error('keperluan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <h5 class="fw-bold mt-4">TINDAK LANJUT / STATUS</h5>
                    <select name="tingkat_status" class="form-control @error('tingkat_status') is-invalid @enderror">
                        <option value="">-- Pilih --</option>
                        <option value="urgent" {{ $agenda->tingkat_status == 'urgent' ? 'selected' : '' }}>Urgent
                        </option>
                        <option value="penting" {{ $agenda->tingkat_status == 'penting' ? 'selected' : '' }}>Penting
                        </option>
                        <option value="normal" {{ $agenda->tingkat_status == 'normal' ? 'selected' : '' }}>Normal
                        </option>
                        <option value="dijadwalkan" {{ $agenda->tingkat_status == 'dijadwalkan' ? 'selected' : '' }}>Bisa
                            Dijadwalkan</option>
                    </select>
                    @error('tingkat_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <h5 class="fw-bold mt-4">CATATAN KHUSUS</h5>
                    <textarea name="catatan_khusus" rows="3" class="form-control @error('catatan_khusus') is-invalid @enderror">{{ $agenda->catatan_khusus }}</textarea>
                    @error('catatan_khusus')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <h5 class="fw-bold mt-4">STATUS AKHIR</h5>
                    <select name="status" class="form-control @error('status') is-invalid @enderror">
                        <option value="belum" {{ $agenda->status == 'belum' ? 'selected' : '' }}>Belum</option>
                        <option value="terkonfirmasi" {{ $agenda->status == 'terkonfirmasi' ? 'selected' : '' }}>
                            Terkonfirmasi</option>
                        <option value="reschedule" {{ $agenda->status == 'reschedule' ? 'selected' : '' }}>Reschedule
                        </option>
                        <option value="dibatalkan" {{ $agenda->status == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan
                        </option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <label>Dicatat oleh</label>
                            <input type="text" name="dicatat_oleh"
                                class="form-control @error('dicatat_oleh') is-invalid @enderror"
                                value="{{ $agenda->dicatat_oleh }}">
                            @error('dicatat_oleh')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Tanggal Catat</label>
                            <input type="date" name="dicatat_tgl"
                                class="form-control @error('dicatat_tgl') is-invalid @enderror"
                                value="{{ $agenda->dicatat_tgl }}">
                            @error('dicatat_tgl')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
            </div>
        </div>
    </div>
@endsection
