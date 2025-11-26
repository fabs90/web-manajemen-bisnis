@extends('layouts.partial.layouts')

@section('page-title', 'Input Surat Undangan Rapat | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Input Surat Undangan Rapat')

@section('section-row')
    <div class="container mt-4">

        {{-- Alert Error --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Gagal!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Alert Success --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <strong>Sukses!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('administrasi.surat-undangan-rapat.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white mb-3">
                    <strong>Data Surat</strong>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label">Nomor Surat <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_surat"
                            class="form-control @error('nomor_surat') is-invalid @enderror"
                            placeholder="Ct: 001/UND/DGT/11/2025" value="{{ old('nomor_surat') }}">
                        @error('nomor_surat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lampiran</label>
                            <input type="text" name="lampiran" class="form-control" placeholder="Contoh: 1 lembar"
                                value="{{ old('lampiran') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Perihal / Judul Rapat <span class="text-danger">*</span></label>
                            <input type="text" name="perihal" class="form-control @error('perihal') is-invalid @enderror"
                                placeholder="Undangan Rapat Koordinasi" value="{{ old('perihal') }}">
                            @error('perihal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white mb-3">
                    <strong>Data Penerima Surat</strong>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label">Nama Penerima <span class="text-danger">*</span></label>
                        <input type="text" name="nama_penerima"
                            class="form-control @error('nama_penerima') is-invalid @enderror"
                            placeholder="Nama lengkap penerima surat" value="{{ old('nama_penerima') }}">
                        @error('nama_penerima')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jabatan Penerima</label>
                        <input type="text" name="jabatan_penerima" class="form-control"
                            placeholder="Manager / Direktur / Kepala Bagian" value="{{ old('jabatan_penerima') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kota Penerima</label>
                        <input type="text" name="kota_penerima" class="form-control" placeholder="Jakarta"
                            value="{{ old('kota_penerima') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Judul Rapat <span class="text-danger">*</span></label>
                        <input type="text" name="judul_rapat"
                            class="form-control @error('judul_rapat') is-invalid @enderror"
                            placeholder="Rapat Evaluasi Program" value="{{ old('judul_rapat') }}">
                        @error('judul_rapat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>


            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white mb-3">
                    <strong>Data Rapat</strong>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Hari</label>
                            <input type="text" name="hari" class="form-control" placeholder="Senin"
                                value="{{ old('hari') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Rapat <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_rapat"
                                class="form-control @error('tanggal_rapat') is-invalid @enderror"
                                value="{{ old('tanggal_rapat') }}">
                            @error('tanggal_rapat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tempat Rapat</label>
                            <input type="text" name="tempat" class="form-control" placeholder="Ruang Rapat Lantai 3"
                                value="{{ old('tempat') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Mulai</label>
                            <input type="time" name="waktu_mulai" class="form-control"
                                value="{{ old('waktu_mulai') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Selesai</label>
                            <input type="time" name="waktu_selesai" class="form-control"
                                value="{{ old('waktu_selesai') }}">
                        </div>
                    </div>

                    {{-- Agenda Dynamic --}}
                    <label class="form-label">Agenda Rapat</label>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th width="5%">No</th>
                                <th>Agenda</th>
                                <th width="8%"></th>
                            </tr>
                        </thead>
                        <tbody id="agenda-list">
                            <tr>
                                <td class="text-center">1</td>
                                <td><input type="text" name="agenda[]" class="form-control" placeholder="Agenda 1">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-success btn-sm" onclick="addAgenda()"><i
                                            class="bi bi-plus"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white mb-3">
                    <strong>Penandatangan</strong>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Penandatangan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_penandatangan"
                                class="form-control @error('nama_penandatangan') is-invalid @enderror"
                                placeholder="Nama lengkap" value="{{ old('nama_penandatangan') }}">
                            @error('nama_penandatangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jabatan Penandatangan <span class="text-danger">*</span></label>
                            <input type="text" name="jabatan_penandatangan"
                                class="form-control @error('jabatan_penandatangan') is-invalid @enderror"
                                placeholder="Direktur / Manager" value="{{ old('jabatan_penandatangan') }}">
                            @error('jabatan_penandatangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>
            </div>

            <div class="text-end">
                <a href="{{ route('administrasi.surat-undangan-rapat.index') }}" class="btn btn-secondary">Kembali</a>
                <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
            </div>

        </form>
    </div>


    {{-- ================= Script Dynamic Agenda ================= --}}
    <script>
        function addAgenda() {
            let table = document.getElementById('agenda-list');
            let rowCount = table.rows.length + 1;
            let row = `<tr>
                        <td class="text-center">${rowCount}</td>
                        <td><input type="text" name="agenda[]" class="form-control" placeholder="Agenda ${rowCount}"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeAgenda(this)"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
            table.insertAdjacentHTML('beforeend', row);
        }

        function removeAgenda(el) {
            el.closest('tr').remove();
            updateAgendaNumbers();
        }

        function updateAgendaNumbers() {
            let rows = document.querySelectorAll('#agenda-list tr');
            rows.forEach((row, index) => {
                row.children[0].innerText = index + 1;
                row.children[1].children[0].placeholder = `Agenda ${index + 1}`;
            });
        }
    </script>

@endsection
