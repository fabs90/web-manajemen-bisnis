@extends('layouts.partial.layouts')

@section('page-title', 'Edit Data Agenda Rapat - '.$rapat->judul_rapat.' | Digitrans')

@section('section-row')
<div class="container mt-4">

    {{-- ================= ALERT ================= --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <strong>Sukses!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Gagal!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif


    {{-- ================= FORM ================= --}}
    <form id="formRapat" action="{{route('administrasi.rapat.update', ['rapatId' => $rapat->id])}}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark fw-bold">
                EDIT AGENDA RAPAT & NOTULEN RAPAT
            </div>

            <div class="card-body">

                {{-- HEADER --}}
                <table class="table table-bordered">
                    <tr>
                        <td width="30%">Judul Rapat</td>
                        <td><input type="text" name="judul_rapat" value="{{ old('judul_rapat', $rapat->judul_rapat) }}" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Tempat</td>
                        <td><input type="text" name="tempat" value="{{ old('tempat', $rapat->tempat) }}" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td><input type="date" name="tanggal" value="{{ old('tanggal', $rapat->tanggal) }}" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Waktu</td>
                        <td><input type="time" name="waktu" value="{{ old('waktu', $rapat->waktu) }}" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Pimpinan Rapat</td>
                        <td><input type="text" name="pimpinan_rapat" value="{{ old('pimpinan_rapat', $rapat->pimpinan_rapat) }}" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Notulis</td>
                        <td><input type="text" name="notulis" value="{{ old('notulis', $rapat->notulis) }}" class="form-control"></td>
                    </tr>
                </table>

                {{-- PESERTA --}}
                <h6 class="fw-bold mt-3">Daftar Hadir Peserta</h6>
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th>Tanda Tangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="peserta-rapat-body">
                        @foreach($rapat->pesertaRapat as $i => $p)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td><input type="text" name="peserta_nama[]" value="{{ $p->nama }}" class="form-control"></td>
                            <td><input type="text" name="peserta_jabatan[]" value="{{ $p->jabatan }}" class="form-control"></td>
                            <td>
                                @if($p->tanda_tangan)
                                    <img src="{{ asset('storage/'.$p->tanda_tangan) }}" width="60">
                                @endif
                                <input type="file" name="peserta_ttd[]" class="form-control">
                                <input type="hidden" name="peserta_ttd_old[]" value="{{ $p->tanda_tangan }}">
                            </td>
                            <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="addPeserta">+ Tambah Peserta</button>

                {{-- PEMBAHASAN --}}
                <h6 class="fw-bold">Pembahasan</h6>
                <table class="table table-bordered">
                    <tbody id="pembahasan-body">
                        @foreach($rapat->rapatDetails as $d)
                        <tr>
                            <td><input type="text" name="pembahasan_agenda[]" value="{{ $d->judul_agenda }}" class="form-control"></td>
                            <td><input type="text" name="pembahasan_pembicara[]" value="{{ $d->pembicara }}" class="form-control"></td>
                            <td><textarea name="pembahasan_isi[]" class="form-control">{{ $d->pembahasan }}</textarea></td>
                            <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="addPembahasan">+ Tambah Pembahasan</button>

                {{-- KEPUTUSAN --}}
                <h6 class="fw-bold">Keputusan Rapat</h6>
                <textarea name="keputusan_rapat" class="form-control">{{ $rapat->keputusan_rapat }}</textarea>

                {{-- TINDAK LANJUT --}}
                <h6 class="fw-bold mt-3">Tindak Lanjut</h6>
                <table class="table table-bordered">
                    <tbody id="tindak-lanjut-body">
                        @foreach($rapat->tindakLanjutRapat as $i => $t)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td><input type="text" name="tindak_tindakan[]" value="{{ $t->tindakan }}" class="form-control"></td>
                            <td><input type="text" name="tindak_pelaksana[]" value="{{ $t->pelaksana }}" class="form-control"></td>
                            <td><input type="date" name="tindak_target[]" value="{{ $t->target_selesai }}" class="form-control"></td>
                            <td><input type="text" name="tindak_status[]" value="{{ $t->status }}" class="form-control"></td>
                            <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="addTindak">+ Tambah Tindak Lanjut</button>

                {{-- RAPAT BERIKUTNYA --}}
                <h6 class="fw-bold">Rapat Berikutnya</h6>
                <table class="table table-bordered">
                    <tr>
                        <td width="30%">Tanggal</td>
                        <td><input type="date" name="tanggal_rapat_berikutnya" value="{{ $rapat->tanggal_rapat_berikutnya }}" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Agenda</td>
                        <td><input type="text" name="agenda_rapat_berikutnya" value="{{ $rapat->agenda_rapat_berikutnya }}" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Nama Kota</td>
                        <td><input type="text" name="nama_kota" value="{{ $rapat->nama_kota }}" class="form-control"></td>
                    </tr>
                </table>

                {{-- BUTTON --}}
                <div class="text-end mt-3">
                    <a href="{{ route('administrasi.rapat.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-warning px-3" id="btnSubmit">
                        <i class="fa fa-save me-1"></i> Update
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
// SUBMIT UX (AMAN)
document.getElementById("formRapat").addEventListener("submit", function(e){
    if (!confirm("Yakin ingin menyimpan perubahan?")) {
        e.preventDefault();
        return;
    }

    const btn = document.getElementById("btnSubmit");
    btn.disabled = true;
    btn.innerHTML = 'Menyimpan...';
});

// DELETE ROW
document.addEventListener("click", function(e){
    if(e.target.classList.contains("deleteRow")){
        if(!confirm("Yakin hapus data ini?")) return;
        e.target.closest("tr").remove();
    }
});

// ADD ROWS (TETAP)
document.getElementById("addPeserta").onclick = function(){
    document.getElementById("peserta-rapat-body").insertAdjacentHTML('beforeend', `
    <tr>
        <td>#</td>
        <td><input name="peserta_nama[]" class="form-control"></td>
        <td><input name="peserta_jabatan[]" class="form-control"></td>
        <td><input type="file" name="peserta_ttd[]" class="form-control"></td>
        <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
    </tr>`);
};

document.getElementById("addPembahasan").onclick = function(){
    document.getElementById("pembahasan-body").insertAdjacentHTML('beforeend', `
    <tr>
        <td><input name="pembahasan_agenda[]" class="form-control"></td>
        <td><input name="pembahasan_pembicara[]" class="form-control"></td>
        <td><textarea name="pembahasan_isi[]" class="form-control"></textarea></td>
        <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
    </tr>`);
};

document.getElementById("addTindak").onclick = function(){
    document.getElementById("tindak-lanjut-body").insertAdjacentHTML('beforeend', `
    <tr>
        <td>#</td>
        <td><input name="tindak_tindakan[]" class="form-control"></td>
        <td><input name="tindak_pelaksana[]" class="form-control"></td>
        <td><input type="date" name="tindak_target[]" class="form-control"></td>
        <td><input name="tindak_status[]" class="form-control"></td>
        <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
    </tr>`);
};
</script>
@endsection