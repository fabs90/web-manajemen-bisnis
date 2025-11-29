@extends('layouts.partial.layouts')

@section('page-title', 'Input Data Agenda Rapat | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

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
        <form action="{{ route('administrasi.rapat.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">
                    FORM AGENDA RAPAT & NOTULEN RAPAT
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
                                <input type="text" name="judul_rapat"
                                    class="form-control @error('judul_rapat') is-invalid @enderror">
                            </td>
                        </tr>

                        <tr>
                            <td>Tempat</td>
                            <td>
                                <input type="text" name="tempat"
                                    class="form-control @error('tempat') is-invalid @enderror">
                            </td>
                        </tr>

                        <tr>
                            <td>Tanggal</td>
                            <td>
                                <input type="date" name="tanggal"
                                    class="form-control @error('tanggal') is-invalid @enderror">
                            </td>
                        </tr>

                        <tr>
                            <td>Waktu</td>
                            <td>
                                <input type="time" name="waktu"
                                    class="form-control @error('waktu') is-invalid @enderror">
                            </td>
                        </tr>

                        <tr>
                            <td>Pimpinan Rapat</td>
                            <td>
                                <input type="text" name="pimpinan_rapat"
                                    class="form-control @error('pimpinan_rapat') is-invalid @enderror">
                            </td>
                        </tr>

                        <tr>
                            <td>Notulis</td>
                            <td>
                                <input type="text" name="notulis"
                                    class="form-control @error('notulis') is-invalid @enderror">
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
                            <tr>
                                <td>1</td>
                                <td><input type="text" name="peserta_nama[]" class="form-control"></td>
                                <td><input type="text" name="peserta_jabatan[]" class="form-control"></td>
                                <td><input type="file" name="peserta_ttd[]" class="form-control"></td>
                                <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
                            </tr>
                        </tbody>

                    </table>
                    <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="addPeserta">
                        + Tambah Peserta
                    </button>

                    {{-- ================= AGENDA RAPAT ================= --}}
                    <h6 class="fw-bold">Agenda Rapat:</h6>
                    <textarea name="agenda_rapat" rows="3" class="form-control @error('agenda_rapat') is-invalid @enderror"></textarea>

                    <hr>
                    {{-- ================= PEMBAHASAN ================= --}}
                    <h6 class="fw-bold mt-3">Pembahasan:</h6>
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
                            <tr>
                                <td><input type="text" name="pembahasan_agenda[]" class="form-control"></td>
                                <td><input type="text" name="pembahasan_pembicara[]" class="form-control"></td>
                                <td><textarea name="pembahasan_isi[]" rows="2" class="form-control"></textarea></td>
                                <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
                            </tr>
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="addPembahasan">
                        + Tambah Pembahasan
                    </button>

                    {{-- ================= KEPUTUSAN RAPAT ================= --}}
                    <h6 class="fw-bold mt-3">Keputusan Rapat:</h6>
                    <textarea name="keputusan_rapat" rows="3" class="form-control @error('keputusan_rapat') is-invalid @enderror"></textarea>

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
                            <tr>
                                <td>1</td>
                                <td><input type="text" name="tindak_tindakan[]" class="form-control"></td>
                                <td><input type="text" name="tindak_pelaksana[]" class="form-control"></td>
                                <td><input type="date" name="tindak_target[]" class="form-control"></td>
                                <td><input type="text" name="tindak_status[]" class="form-control"></td>
                                <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
                            </tr>
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
                            <td><input type="date" name="tanggal_rapat_berikutnya" class="form-control"></td>
                        </tr>
                        <tr>
                            <td>Agenda</td>
                            <td><input type="text" name="agenda_rapat_berikutnya" class="form-control"></td>
                        </tr>
                        <tr>
                            <td>Nama Kota</td>
                            <td><input type="text" name="nama_kota" class="form-control"></td>
                        </tr>
                    </table>

                    {{-- ================= BUTTON ================= --}}
                    <div class="text-end mt-3">
                        <a href="{{ route('administrasi.rapat.index') }}" class="btn btn-secondary">Batal</a>
                        <button class="btn btn-success px-3">
                            <i class="fa fa-save me-1"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- ================= SCRIPT DYNAMIC ROW ================= --}}
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
