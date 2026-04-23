@extends('layouts.partial.layouts')

@section('page-title', 'Input Data Notulen Rapat | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

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
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Oops! Ada kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
 <form action="{{ route('administrasi.rapat.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">
                    FORM NOTULEN RAPAT
                </div>

                <div class="card-body">
                    {{-- ================= HEADER AGENDA RAPAT ================= --}}
                    <table class="table table-bordered">
                        <thead class="table-light text-center">
                            <tr><th colspan="2">NOTULEN RAPAT</th></tr>
                        </thead>
                        <tr>
                            <td width="30%">Nomor Surat Rapat</td>
                            <td><input type="text" name="nomor_surat_rapat" class="form-control" required></td>
                        </tr>
                        <tr>
                            <td>Judul Rapat</td>
                            <td><input type="text" name="judul_rapat" class="form-control" required></td>
                        </tr>
                        <tr>
                            <td>Tempat</td>
                            <td><input type="text" name="tempat" class="form-control" required></td>
                        </tr>
                        <tr>
                            <td>Nama Kota</td>
                            <td><input type="text" name="nama_kota" class="form-control" required></td>
                        </tr>
                        <tr>
                            <td>Tanggal</td>
                            <td><input type="date" name="tanggal" class="form-control" required></td>
                        </tr>
                        <tr>
                            <td>Waktu</td>
                            <td><input type="time" name="waktu" class="form-control" required></td>
                        </tr>
                        <tr>
                            <td>Nama Pemimpin Rapat</td>
                            <td><input type="text" name="pemimpin_rapat" class="form-control" required></td>
                        </tr>
                        <tr>
                            <td>Tanda Tangan Pimpinan</td>
                            <td>
                                <div class="upload-container shadow-sm border rounded p-3 text-center bg-light" id="ttd-pemimpin-drop-zone"
                                    onclick="document.getElementById('ttd-pemimpin-input').click()">
                                    <div id="ttd-pemimpin-placeholder">
                                        <i class="bi bi-pen fs-3 text-secondary mb-2 d-block"></i>
                                        <span class="d-block mb-2 text-muted small">Klik atau Seret Tanda Tangan ke Sini</span>
                                        <button type="button" class="btn btn-outline-primary btn-sm px-3">Pilih File TTD</button>
                                    </div>
                                    <input type="file" name="ttd_pemimpin" id="ttd-pemimpin-input" class="d-none" accept="image/*">
                                    <div id="ttd-pemimpin-preview-container" class="mt-2 d-none">
                                        <div class="position-relative d-inline-block">
                                            <img id="ttd-pemimpin-preview" src="#" alt="Preview"
                                                class="img-fluid rounded border shadow-sm" style="max-height: 80px;">
                                        </div>
                                        <p class="mb-0 mt-2 small text-success fw-bold">
                                            <i class="bi bi-file-earmark-check me-1"></i> Terpilih: <span id="ttd-pemimpin-filename"></span>
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Nama Notulis</td>
                            <td><input type="text" name="nama_notulis" class="form-control" required></td>
                        </tr>
                        <tr>
                            <td>Tanda Tangan Notulis</td>
                            <td>
                                <div class="upload-container shadow-sm border rounded p-3 text-center bg-light" id="ttd-notulis-drop-zone"
                                    onclick="document.getElementById('ttd-notulis-input').click()">
                                    <div id="ttd-notulis-placeholder">
                                        <i class="bi bi-pen fs-3 text-secondary mb-2 d-block"></i>
                                        <span class="d-block mb-2 text-muted small">Klik atau Seret Tanda Tangan ke Sini</span>
                                        <button type="button" class="btn btn-outline-primary btn-sm px-3">Pilih File TTD</button>
                                    </div>
                                    <input type="file" name="ttd_notulis" id="ttd-notulis-input" class="d-none" accept="image/*">
                                    <div id="ttd-notulis-preview-container" class="mt-2 d-none">
                                        <div class="position-relative d-inline-block">
                                            <img id="ttd-notulis-preview" src="#" alt="Preview"
                                                class="img-fluid rounded border shadow-sm" style="max-height: 80px;">
                                        </div>
                                        <p class="mb-0 mt-2 small text-success fw-bold">
                                            <i class="bi bi-file-earmark-check me-1"></i> Terpilih: <span id="ttd-notulis-filename"></span>
                                        </p>
                                    </div>
                                </div>
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
                                <td><input type="text" name="peserta_nama[]" class="form-control" required></td>
                                <td><input type="text" name="peserta_jabatan[]" class="form-control"></td>
                                <td><input type="file" name="peserta_ttd[]" class="form-control" accept="image/*" required></td>
                                <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="addPeserta">+ Tambah Peserta</button>

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
                                <td>
                                    <textarea name="pembahasan_isi[]" rows="2" class="form-control"></textarea>
                                </td>
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
                    <h6 class="fw-bold">Rapat Berikutnya (Opsional):</h6>
                    <table class="table table-bordered text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Agenda</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                        <tr>
                            <td><input type="text" name="agenda_rapat_berikutnya" class="form-control"></td>
                            <td><input type="date" name="tanggal_rapat_berikutnya" class="form-control"></td>
                            <td><input type="time" name="waktu_rapat_berikutnya" class="form-control"></td>
                        </tr>
                    </table>
                    <small class="text-muted">Note: Kosongkan jika tidak ada rapat berikutnya.</small>


                    {{-- ================= BUTTON ================= --}}
                            <div class="text-end mt-3">
                                <a href="{{ route('administrasi.rapat.index') }}" class="btn btn-secondary">Batal</a>
                                <button class="btn btn-success px-3"><i class="fa fa-save me-1"></i> Simpan</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
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

        function setupUploadPreview(id) {
            const dropZone = document.getElementById(`ttd-${id}-drop-zone`);
            const input = document.getElementById(`ttd-${id}-input`);
            const preview = document.getElementById(`ttd-${id}-preview`);
            const placeholder = document.getElementById(`ttd-${id}-placeholder`);
            const previewContainer = document.getElementById(`ttd-${id}-preview-container`);
            const filename = document.getElementById(`ttd-${id}-filename`);

            if (!dropZone || !input) return;

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, e => {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
            });

            dropZone.addEventListener('drop', e => {
                const dt = e.dataTransfer;
                input.files = dt.files;
                input.dispatchEvent(new Event('change'));
            }, false);

            input.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        placeholder.classList.add('d-none');
                        previewContainer.classList.remove('d-none');
                        filename.textContent = input.files[0].name;
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            setupUploadPreview('pemimpin');
            setupUploadPreview('notulis');
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

@push('styles')
    <style>
        .upload-container {
            border: 2px dashed #dee2e6 !important;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .upload-container:hover,
        .upload-container.dragover {
            border-color: #0d6efd !important;
            background-color: #f1f8ff !important;
            transform: translateY(-2px);
        }
    </style>
@endpush

@endsection
