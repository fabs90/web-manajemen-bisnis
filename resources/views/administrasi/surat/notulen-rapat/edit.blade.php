@extends('layouts.partial.layouts')

@section('page-title', 'Edit Data Agenda Rapat - '.$rapat->judul_rapat.' | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-row')
<div class="container mt-4">
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
    <form action="{{route('administrasi.rapat.update', ['rapatId' => $rapat->id])}}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark fw-bold">
                EDIT AGENDA RAPAT & NOTULEN RAPAT
            </div>

            <div class="card-body">

                {{-- ================= HEADER AGENDA RAPAT ================= --}}
                <table class="table table-bordered">
                    <thead class="table-light text-center">
                        <tr>
                            <th colspan="2">AGENDA RAPAT & NOTULEN RAPAT</th>
                        </tr>
                    </thead>

                    <tr>
                        <td width="30%">Judul Rapat</td>
                        <td>
                            <input type="text" name="judul_rapat" value="{{ old('judul_rapat', $rapat->judul_rapat) }}" class="form-control">
                        </td>
                    </tr>

                    <tr>
                        <td>Tempat</td>
                        <td>
                            <input type="text" name="tempat" value="{{ old('tempat', $rapat->tempat) }}" class="form-control">
                        </td>
                    </tr>

                    <tr>
                        <td>Tanggal</td>
                        <td>
                            <input type="date" name="tanggal" value="{{ old('tanggal', $rapat->tanggal) }}" class="form-control">
                        </td>
                    </tr>

                    <tr>
                        <td>Waktu</td>
                        <td>
                            <input type="time" name="waktu" value="{{ old('waktu', $rapat->waktu) }}" class="form-control">
                        </td>
                    </tr>

                    <tr>
                        <td>Pimpinan Rapat</td>
                        <td>
                            <input type="text" name="pimpinan_rapat" value="{{ old('pimpinan_rapat', $rapat->pimpinan_rapat) }}" class="form-control">
                        </td>
                    </tr>

                    <tr>
                        <td>Notulis</td>
                        <td>
                            <input type="text" name="notulis" value="{{ old('notulis', $rapat->notulis) }}" class="form-control">
                        </td>
                    </tr>
                </table>

                {{-- ================= DAFTAR PESERTA ================= --}}
                <h6 class="fw-bold mt-3">Daftar Hadir Peserta:</h6>
                <table class="table table-bordered text-center">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th>Tanda Tangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody id="peserta-rapat-body">
                        @foreach($rapat->pesertaRapat as $i => $peserta)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td><input type="text" name="peserta_nama[]" value="{{ $peserta->nama }}" class="form-control"></td>
                            <td><input type="text" name="peserta_jabatan[]" value="{{ $peserta->jabatan }}" class="form-control"></td>
                            <td>
                                @if($peserta->tanda_tangan)
                                    <img src="{{ asset('storage/'.$peserta->tanda_tangan) }}" width="60" class="mb-1">
                                @endif
                                <input type="file" name="peserta_ttd[]" class="form-control">
                                <input type="hidden" name="peserta_ttd_old[]" value="{{ $peserta->tanda_tangan }}">
                            </td>
                              <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="addPeserta">
                    + Tambah Peserta
                </button>

                {{-- ================= PEMBAHASAN ================= --}}
                <h6 class="fw-bold">Pembahasan:</h6>
                <table class="table table-bordered text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Agenda</th>
                            <th>Pembicara</th>
                            <th>Pembahasan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pembahasan-body">
                        @foreach($rapat->rapatDetails as $detail)
                        <tr>
                            <td><input type="text" name="pembahasan_agenda[]"  class="form-control" value="{{ $detail->judul_agenda }}"></td>
                            <td><input type="text" name="pembahasan_pembicara[]" class="form-control" value="{{ $detail->pembicara }}"></td>
                            <td><textarea name="pembahasan_isi[]" rows="2" class="form-control">{{ $detail->pembahasan }}</textarea></td>
                            <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="addPembahasan">
                    + Tambah Pembahasan
                </button>

                {{-- ================= KEPUTUSAN RAPAT ================= --}}
                <h6 class="fw-bold mt-3">Keputusan Rapat:</h6>
                <textarea name="keputusan_rapat" rows="3" class="form-control">{{ $rapat->keputusan_rapat }}</textarea>

                {{-- ================= TINDAK LANJUT ================= --}}
                <h6 class="fw-bold mt-3">Tindak Lanjut:</h6>
                <table class="table table-bordered text-center">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tindakan</th>
                            <th>Pelaksana</th>
                            <th>Target Selesai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tindak-lanjut-body">
                        @foreach($rapat->tindakLanjutRapat as $i => $tindak)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td><input type="text" name="tindak_tindakan[]" value="{{ $tindak->tindakan }}" class="form-control"></td>
                            <td><input type="text" name="tindak_pelaksana[]" value="{{ $tindak->pelaksana }}" class="form-control"></td>
                            <td><input type="date" name="tindak_target[]" value="{{ $tindak->target_selesai }}" class="form-control"></td>
                            <td><input type="text" name="tindak_status[]" value="{{ $tindak->status }}" class="form-control"></td>
                            <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="addTindak">
                    + Tambah Tindak Lanjut
                </button>

                {{-- ================= RAPAT BERIKUTNYA ================= --}}
                <h6 class="fw-bold">Rapat Berikutnya:</h6>
                <table class="table table-bordered">
                    <tr>
                        <td width="30%">Tanggal</td>
                        <td><input type="date" name="tanggal_rapat_berikutnya" class="form-control" value="{{ $rapat->tanggal_rapat_berikutnya }}"></td>
                    </tr>
                    <tr>
                        <td>Agenda</td>
                        <td><input type="text" name="agenda_rapat_berikutnya" class="form-control" value="{{ $rapat->agenda_rapat_berikutnya }}"></td>
                    </tr>
                    <tr>
                        <td>Nama Kota</td>
                        <td><input type="text" name="nama_kota" class="form-control" value="{{ $rapat->nama_kota }}"></td>
                    </tr>
                </table>

                {{-- ================= BUTTON ================= --}}
                <div class="text-end mt-3">
                    <a href="{{ route('administrasi.rapat.index') }}" class="btn btn-secondary">Batal</a>
                    <button class="btn btn-warning px-3">
                        <i class="fa fa-save me-1"></i> Update
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
function reindexRows() {
    document.querySelectorAll("#peserta-rapat-body tr").forEach((row, index) => {
        row.children[0].innerText = index + 1;
    });

    document.querySelectorAll("#tindak-lanjut-body tr").forEach((row, index) => {
        row.children[0].innerText = index + 1;
    });
}

document.addEventListener("click", function(e) {
    if (e.target.classList.contains("deleteRow")) {
        if (!confirm("Yakin ingin menghapus baris ini? Data akan hilang setelah disimpan!")) {
            return; // batal hapus
        }
        e.target.closest("tr").remove();
        reindexRows();
    }
});

document.getElementById('addPeserta').addEventListener('click', function() {
    const table = document.getElementById('peserta-rapat-body');
    const rowCount = table.rows.length + 1;
    const row = `
    <tr>
        <td>${rowCount}</td>
        <td><input type="text" name="peserta_nama[]" class="form-control"></td>
        <td><input type="text" name="peserta_jabatan[]" class="form-control"></td>
        <td><input type="file" name="peserta_ttd[]" class="form-control"></td>
        <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
    </tr>`;
    table.insertAdjacentHTML('beforeend', row);
});

document.getElementById('addPembahasan').addEventListener('click', function() {
    const table = document.getElementById('pembahasan-body');
    const row = `
    <tr>
        <td><input type="text" name="pembahasan_agenda[]" class="form-control"></td>
        <td><input type="text" name="pembahasan_pembicara[]" class="form-control"></td>
        <td><textarea name="pembahasan_isi[]" rows="2" class="form-control"></textarea></td>
        <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
    </tr>`;
    table.insertAdjacentHTML('beforeend', row);
});

document.getElementById('addTindak').addEventListener('click', function() {
    const table = document.getElementById('tindak-lanjut-body');
    const rowCount = table.rows.length + 1;
    const row = `
    <tr>
        <td>${rowCount}</td>
        <td><input type="text" name="tindak_tindakan[]" class="form-control"></td>
        <td><input type="text" name="tindak_pelaksana[]" class="form-control"></td>
        <td><input type="date" name="tindak_target[]" class="form-control"></td>
        <td><input type="text" name="tindak_status[]" class="form-control"></td>
        <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
    </tr>`;
    table.insertAdjacentHTML('beforeend', row);
});
</script>
@endsection
