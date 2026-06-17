@extends('layouts.partial.layouts')

@section('page-title', 'Input Data Notulen Rapat | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-row')
    <div class="container mt-4">
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
                            <td width="30%">Nomor Surat Rapat <span class="text-danger">*</span></td>
                            <td><input type="text" name="nomor_surat_rapat" class="form-control" value="{{ old('nomor_surat_rapat') }}" required></td>
                        </tr>
                        <tr>
                            <td>Judul Rapat <span class="text-danger">*</span></td>
                            <td><input type="text" name="judul_rapat" class="form-control" value="{{ old('judul_rapat') }}" required></td>
                        </tr>
                        <tr>
                            <td>Tempat <span class="text-danger">*</span></td>
                            <td><input type="text" name="tempat" class="form-control" value="{{ old('tempat') }}" required></td>
                        </tr>
                        <tr>
                            <td>Nama Kota <span class="text-danger">*</span></td>
                            <td><input type="text" name="nama_kota" class="form-control" value="{{ old('nama_kota') }}" required></td>
                        </tr>
                        <tr>
                            <td>Tanggal <span class="text-danger">*</span></td>
                            <td><input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}" required></td>
                        </tr>
                        <tr>
                            <td>Waktu <span class="text-danger">*</span></td>
                            <td><input type="time" name="waktu" class="form-control" value="{{ old('waktu') }}" required></td>
                        </tr>
                        <tr>
                            <td>Nama Pemimpin Rapat <span class="text-danger">*</span></td>
                            <td><input type="text" name="pemimpin_rapat" class="form-control" value="{{ old('pemimpin_rapat') }}" required></td>
                        </tr>
                        <tr>
                            <td>Tanda Tangan Pimpinan <span class="text-danger">*</span></td>
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
                            <td>Nama Notulis <span class="text-danger">*</span></td>
                            <td><input type="text" name="nama_notulis" class="form-control" value="{{ old('nama_notulis') }}" required></td>
                        </tr>
                        <tr>
                            <td>Tanda Tangan Notulis <span class="text-danger">*</span></td>
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
                                <th>Nama <span class="text-danger">*</span></th>
                                <th>Jabatan <span class="text-danger">*</span></th>
                                <th>Tanda Tangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="peserta-rapat-body">
                            @if(old('peserta_nama'))
                                @foreach(old('peserta_nama') as $index => $nama)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><input type="text" name="peserta_nama[]" class="form-control" value="{{ $nama }}" required></td>
                                    <td><input type="text" name="peserta_jabatan[]" class="form-control" value="{{ old('peserta_jabatan.'.$index) }}" required></td>
                                    <td><input type="file" name="peserta_ttd[]" class="form-control" accept="image/*"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td>1</td>
                                    <td><input type="text" name="peserta_nama[]" class="form-control" required></td>
                                    <td><input type="text" name="peserta_jabatan[]" class="form-control" required></td>
                                    <td><input type="file" name="peserta_ttd[]" class="form-control" accept="image/*"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="addPeserta">+ Tambah Peserta</button>

                    {{-- ================= AGENDA RAPAT ================= --}}
                    <h6 class="fw-bold">Agenda Rapat: <span class="text-danger">*</span></h6>
                    <textarea name="agenda_rapat" rows="3" class="form-control @error('agenda_rapat') is-invalid @enderror" required>{{ old('agenda_rapat') }}</textarea>

                    <hr>
                    {{-- ================= PEMBAHASAN ================= --}}
                    <h6 class="fw-bold mt-3">Pembahasan:</h6>
                    <table class="table table-bordered text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Agenda <span class="text-danger">*</span></th>
                                <th>Pembicara <span class="text-danger">*</span></th>
                                <th>Pembahasan <span class="text-danger">*</span></th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="pembahasan-body">
                            @if(old('pembahasan_agenda'))
                                @foreach(old('pembahasan_agenda') as $index => $agenda)
                                <tr>
                                    <td><input type="text" name="pembahasan_agenda[]" class="form-control" value="{{ $agenda }}" required></td>
                                    <td><input type="text" name="pembahasan_pembicara[]" class="form-control" value="{{ old('pembahasan_pembicara.'.$index) }}" required></td>
                                    <td>
                                        <textarea name="pembahasan_isi[]" rows="2" class="form-control" required>{{ old('pembahasan_isi.'.$index) }}</textarea>
                                    </td>
                                    <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td><input type="text" name="pembahasan_agenda[]" class="form-control" required></td>
                                    <td><input type="text" name="pembahasan_pembicara[]" class="form-control" required></td>
                                    <td>
                                        <textarea name="pembahasan_isi[]" rows="2" class="form-control" required></textarea>
                                    </td>
                                    <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="addPembahasan">
                        + Tambah Pembahasan
                    </button>

                    {{-- ================= KEPUTUSAN RAPAT ================= --}}
                    <h6 class="fw-bold mt-3">Keputusan Rapat: <span class="text-danger">*</span></h6>
                    <textarea name="keputusan_rapat" rows="3" class="form-control @error('keputusan_rapat') is-invalid @enderror" required>{{ old('keputusan_rapat') }}</textarea>

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
                            @if(old('tindak_tindakan'))
                                @foreach(old('tindak_tindakan') as $index => $tindakan)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><input type="text" name="tindak_tindakan[]" class="form-control" value="{{ $tindakan }}"></td>
                                    <td><input type="text" name="tindak_pelaksana[]" class="form-control" value="{{ old('tindak_pelaksana.'.$index) }}"></td>
                                    <td><input type="date" name="tindak_target[]" class="form-control" value="{{ old('tindak_target.'.$index) }}"></td>
                                    <td><input type="text" name="tindak_status[]" class="form-control" value="{{ old('tindak_status.'.$index) }}"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td>1</td>
                                    <td><input type="text" name="tindak_tindakan[]" class="form-control"></td>
                                    <td><input type="text" name="tindak_pelaksana[]" class="form-control"></td>
                                    <td><input type="date" name="tindak_target[]" class="form-control"></td>
                                    <td><input type="text" name="tindak_status[]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
                                </tr>
                            @endif
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
                            <td><input type="text" name="agenda_rapat_berikutnya" class="form-control" value="{{ old('agenda_rapat_berikutnya') }}"></td>
                            <td><input type="date" name="tanggal_rapat_berikutnya" class="form-control" value="{{ old('tanggal_rapat_berikutnya') }}"></td>
                            <td><input type="time" name="waktu_rapat_berikutnya" class="form-control" value="{{ old('waktu_rapat_berikutnya') }}"></td>
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
            <td><input type="text" name="peserta_nama[]" class="form-control" required></td>
            <td><input type="text" name="peserta_jabatan[]" class="form-control" required></td>
            <td><input type="file" name="peserta_ttd[]" class="form-control" accept="image/*"></td>
            <td><button type="button" class="btn btn-danger btn-sm deleteRow">X</button></td>
        </tr>`;
            table.insertAdjacentHTML('beforeend', row);
        });

        document.getElementById('addPembahasan').addEventListener('click', function() {
            const table = document.getElementById('pembahasan-body');
            const row = `
        <tr>
            <td><input type="text" name="pembahasan_agenda[]" class="form-control" required></td>
            <td><input type="text" name="pembahasan_pembicara[]" class="form-control" required></td>
            <td><textarea name="pembahasan_isi[]" rows="2" class="form-control" required></textarea></td>
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

    @push('script')
        <script>
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: "{{ session('success') }}",
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: "{{ session('error') }}",
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Oops! Ada kesalahan:',
                    html: '<ul class="text-start">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
                });
            @endif
        </script>
    @endpush

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
