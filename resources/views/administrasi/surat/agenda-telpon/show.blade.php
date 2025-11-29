@extends('layouts.partial.layouts')

@section('page-title', 'Update Agenda Janji Telpon | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Update Agenda Janji Telpon')

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

        <form action="{{ route('administrasi.agenda-telpon.update', ["id" => $agenda->id]) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="card shadow">
                <div class="card-header bg-primary text-white fw-bold">
                    Update Agenda Janji Telpon
                </div>
                <div class="card-body mt-3">

                    {{-- Bagian 1 --}}
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Tanggal Panggilan Telpon</label>
                            <input type="date" name="tgl_panggilan" class="form-control"
                                value="{{ old('tgl_panggilan', $agenda->tgl_panggilan) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Waktu Panggilan</label>
                            <input type="time" name="waktu_panggilan" class="form-control"
                                value="{{ old('waktu_panggilan', $agenda->waktu_panggilan) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Nama Penelpon</label>
                            <input type="text" name="nama_penelpon" class="form-control"
                                value="{{ old('nama_penelpon', $agenda->nama_penelpon) }}">
                        </div>
                    </div>

                    {{-- Bagian 2 --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Nama Perusahaan</label>
                            <input type="text" name="perusahaan" class="form-control"
                                value="{{ old('perusahaan', $agenda->perusahaan) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Nomor Telpon</label>
                            <input type="text" name="nomor_telpon" class="form-control"
                                value="{{ old('nomor_telpon', $agenda->nomor_telpon) }}">
                        </div>
                    </div>

                    <h5 class="fw-bold mt-4">JADWAL TELPON</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Tanggal</label>
                            <input type="date" name="jadwal_tanggal" class="form-control"
                                value="{{ old('jadwal_tanggal', $agenda->jadwal_tanggal) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Waktu</label>
                            <input type="time" name="jadwal_waktu" class="form-control"
                                value="{{ old('jadwal_waktu', $agenda->jadwal_waktu) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Dengan</label>
                            <input type="text" name="jadwal_dengan" class="form-control"
                                value="{{ old('jadwal_dengan', $agenda->jadwal_dengan) }}">
                        </div>
                    </div>

                    <h5 class="fw-bold mt-4">KEPERLUAN</h5>
                    <textarea name="keperluan" rows="3" class="form-control">{{ old('keperluan', $agenda->keperluan) }}</textarea>

                    <h5 class="fw-bold mt-4">TINDAK LANJUT / STATUS</h5>
                    <select name="tingkat_status" class="form-control">
                        <option value="">-- Pilih --</option>
                        <option value="urgent" {{ $agenda->tingkat_status == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="penting" {{ $agenda->tingkat_status == 'penting' ? 'selected' : '' }}>Penting</option>
                        <option value="normal" {{ $agenda->tingkat_status == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="dijadwalkan" {{ $agenda->tingkat_status == 'dijadwalkan' ? 'selected' : '' }}>Bisa Dijadwalkan</option>
                    </select>

                    <h5 class="fw-bold mt-4">CATATAN KHUSUS</h5>
                    <textarea name="catatan_khusus" rows="3" class="form-control">{{ old('catatan_khusus', $agenda->catatan_khusus) }}</textarea>

                    <h5 class="fw-bold mt-4">STATUS AKHIR</h5>
                    <select name="status" class="form-control">
                        <option value="terkonfirmasi" {{ $agenda->status == 'terkonfirmasi' ? 'selected' : '' }}>Terkonfirmasi</option>
                        <option value="belum" {{ $agenda->status == 'belum' ? 'selected' : '' }}>Belum</option>
                        <option value="reschedule" {{ $agenda->status == 'reschedule' ? 'selected' : '' }}>Reschedule</option>
                        <option value="dibatalkan" {{ $agenda->status == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                        <option value="selesai" {{ $agenda->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>

                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <label>Dicatat oleh</label>
                            <input type="text" name="dicatat_oleh" class="form-control"
                                value="{{ old('dicatat_oleh', $agenda->dicatat_oleh) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Tanggal Catat</label>
                            <input type="date" name="dicatat_tgl" class="form-control"
                                value="{{ old('dicatat_tgl', $agenda->dicatat_tgl) }}">
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <a href="{{ route('administrasi.agenda-telpon.index') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>

                </div>
            </div>
        </form>
    </div>

@endsection
