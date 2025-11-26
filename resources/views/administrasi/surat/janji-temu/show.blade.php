@extends('layouts.partial.layouts')

@section('page-title', 'Detail Janji Temu | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-row')
<div class="container mt-4">
    @if(session('error'))
        <div class="alert alert-error alert-dismissible fade show">
            <strong>Sukses!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white fw-bold mb-3">
                FORM JANJI TEMU
            </div>

            <div class="card-body">

                {{-- ================= HEADER ================ --}}
                <table class="table table-bordered">
                    <tr class="table-light text-center">
                        <th colspan="2">JANJI TEMU</th>
                    </tr>

                    <tr>
                        <td width="35%">a. Tgl membuat janji temu:</td>
                        <td>
                            <input type="date" name="tgl_membuat"
                                class="form-control @error('tgl_membuat') is-invalid @enderror" value="{{ $agendaJanjiTemu->tgl_membuat }}">
                        </td>
                    </tr>

                    <tr>
                        <td>b. Nama Pembuat Janji Temu:</td>
                        <td>
                            <input type="text" name="nama_pembuat"
                                class="form-control @error('nama_pembuat') is-invalid @enderror" value="{{$agendaJanjiTemu->nama_pembuat}}">
                        </td>
                    </tr>

                    <tr>
                        <td>c. Perusahaan:</td>
                        <td>
                            <input type="text" name="perusahaan"
                                class="form-control @error('perusahaan') is-invalid @enderror" value="{{$agendaJanjiTemu->perusahaan}}">
                        </td>
                    </tr>

                    <tr>
                        <td>d. Nomor Telpon:</td>
                        <td>
                            <input type="text" name="nomor_telpon"
                                class="form-control @error('nomor_telpon') is-invalid @enderror" value="{{$agendaJanjiTemu->nomor_telpon}}">
                        </td>
                    </tr>

                    {{-- ================= JADWAL JANJI TEMU ================ --}}
                    <tr class="table-light">
                        <th colspan="2">JADWAL JANJI TEMU</th>
                    </tr>

                    <tr>
                        <td>e. Tanggal:</td>
                        <td>
                            <input type="date" name="tgl_janji"
                                class="form-control @error('tgl_janji') is-invalid @enderror" value="{{$agendaJanjiTemu->tgl_janji}}">
                        </td>
                    </tr>

                    <tr>
                        <td>f. Waktu:</td>
                        <td>
                            <input type="time" name="waktu"
                                class="form-control @error('waktu') is-invalid @enderror" value="{{$agendaJanjiTemu->waktu}}">
                        </td>
                    </tr>

                    <tr>
                        <td>g. Bertemu dengan:</td>
                        <td>
                            <input type="text" name="bertemu_dengan"
                                class="form-control @error('bertemu_dengan') is-invalid @enderror" value="{{$agendaJanjiTemu->bertemu_dengan}}">
                        </td>
                    </tr>

                    <tr>
                        <td>h. Tempat pertemuan:</td>
                        <td>
                            <input type="text" name="tempat_pertemuan"
                                class="form-control @error('tempat_pertemuan') is-invalid @enderror" value="{{$agendaJanjiTemu->tempat_pertemuan}}">
                        </td>
                    </tr>

                    {{-- ================= KEPERLUAN ================ --}}
                    <tr class="table-light">
                        <th colspan="2">KEPERLUAN</th>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <textarea name="keperluan" rows="4"
                                class="form-control @error('keperluan') is-invalid @enderror">{{$agendaJanjiTemu->keperluan}}</textarea>
                        </td>
                    </tr>

                    {{-- ================= STATUS ================ --}}
                    <tr class="table-light">
                        <th colspan="2">STATUS</th>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <label class="me-4">
                                <input type="radio" name="status" value="terkonfirmasi"
                                    {{ old('status', $agendaJanjiTemu->status) == 'terkonfirmasi' ? 'checked' : '' }}>
                                Terkonfirmasi
                            </label>

                            <label class="me-4">
                                <input type="radio" name="status" value="reschedule"
                                    {{ old('status', $agendaJanjiTemu->status) == 'reschedule' ? 'checked' : '' }}>
                                Reschedule
                            </label>

                            <label class="me-4">
                                <input type="radio" name="status" value="dibatalkan"
                                    {{ old('status', $agendaJanjiTemu->status) == 'dibatalkan' ? 'checked' : '' }}>
                                Dibatalkan
                            </label>
                        </td>
                    </tr>

                    {{-- ================= DICATAT OLEH ================ --}}
                    <tr>
                        <td>Dicatat oleh:</td>
                        <td>
                            <input type="text" name="dicatat_oleh"
                                class="form-control @error('dicatat_oleh') is-invalid @enderror" value="{{$agendaJanjiTemu->dicatat_oleh}}">
                        </td>
                    </tr>

                    <tr>
                        <td>Tgl:</td>
                        <td>
                            <input type="date" name="dicatat_tgl"
                                class="form-control @error('dicatat_tgl') is-invalid @enderror" value="{{$agendaJanjiTemu->dicatat_tgl}}">
                        </td>
                    </tr>

                </table>

                {{-- BUTTON --}}
   <a href="{{ route('administrasi.janji-temu.index') }}" class="btn btn-secondary">Kembali</a>

            </div>
        </div>
</div>
@endsection
