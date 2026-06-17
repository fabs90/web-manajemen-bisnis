@extends('layouts.partial.layouts')

@section('page-title', 'Edit Surat Undangan Rapat | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Edit Surat Undangan Rapat')

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

        <form action="{{ route('administrasi.surat-undangan-rapat.update', $suratUndanganRapat->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white mb-3">
                    <strong>Data Surat</strong>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label">Nomor Surat <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_surat"
                            class="form-control @error('nomor_surat') is-invalid @enderror"
                            placeholder="Ct: 001/UND/DGT/11/2025" value="{{ old('nomor_surat', $suratUndanganRapat->nomor_surat) }}">
                        @error('nomor_surat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lampiran</label>
                            <input type="text" name="lampiran" class="form-control" placeholder="Contoh: 1 lembar"
                                value="{{ old('lampiran', $suratUndanganRapat->lampiran) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Perihal / Judul Rapat <span class="text-danger">*</span></label>
                            <input type="text" name="perihal" class="form-control @error('perihal') is-invalid @enderror"
                                placeholder="Undangan Rapat Koordinasi" value="{{ old('perihal', $suratUndanganRapat->perihal) }}">
                            @error('perihal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white mb-3">
                    <strong>Data Penerima Surat</strong>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label">Nama Penerima <span class="text-danger">*</span></label>
                        <input type="text" name="nama_penerima"
                            class="form-control @error('nama_penerima') is-invalid @enderror"
                            placeholder="Nama lengkap penerima surat" value="{{ old('nama_penerima', $suratUndanganRapat->nama_penerima) }}">
                        @error('nama_penerima')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Penerima <span class="text-danger">*</span></label>
                        <input type="email" name="email_penerima"
                            class="form-control @error('email_penerima') is-invalid @enderror"
                            placeholder="Email penerima surat" value="{{ old('email_penerima', $suratUndanganRapat->email_penerima) }}">
                        @error('email_penerima')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jabatan Penerima</label>
                        <input type="text" name="jabatan_penerima" class="form-control"
                            placeholder="Manager / Direktur / Kepala Bagian" value="{{ old('jabatan_penerima', $suratUndanganRapat->jabatan_penerima) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kota Penerima</label>
                        <input type="text" name="kota_penerima" class="form-control" placeholder="Jakarta"
                            value="{{ old('kota_penerima', $suratUndanganRapat->kota_penerima) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Judul Rapat <span class="text-danger">*</span></label>
                        <input type="text" name="judul_rapat"
                            class="form-control @error('judul_rapat') is-invalid @enderror"
                            placeholder="Rapat Evaluasi Program" value="{{ old('judul_rapat', $suratUndanganRapat->judul_rapat) }}">
                        @error('judul_rapat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>


            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white mb-3">
                    <strong>Data Rapat</strong>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Hari</label>
                            <input type="text" name="hari" class="form-control" placeholder="Senin"
                                value="{{ old('hari', $suratUndanganRapat->hari) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Rapat <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_rapat"
                                class="form-control @error('tanggal_rapat') is-invalid @enderror"
                                value="{{ old('tanggal_rapat', $suratUndanganRapat->tanggal_rapat) }}">
                            @error('tanggal_rapat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tempat Rapat</label>
                            <input type="text" name="tempat" class="form-control" placeholder="Ruang Rapat Lantai 3"
                                value="{{ old('tempat', $suratUndanganRapat->tempat) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Mulai</label>
                            <input type="time" name="waktu_mulai" class="form-control"
                                value="{{ old('waktu_mulai', $suratUndanganRapat->waktu_mulai) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Selesai</label>
                            <input type="time" name="waktu_selesai" class="form-control"
                                value="{{ old('waktu_selesai', $suratUndanganRapat->waktu_selesai) }}">
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
                            @if(count($suratUndanganRapat->details) > 0)
                                @foreach($suratUndanganRapat->details as $index => $detail)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td><input type="text" name="agenda[]" class="form-control" placeholder="Agenda {{ $index + 1 }}" value="{{ $detail->agenda }}"></td>
                                        <td class="text-center">
                                            @if($index == 0)
                                                <button type="button" class="btn btn-success btn-sm" onclick="addAgenda()"><i class="bi bi-plus"></i></button>
                                            @else
                                                <button type="button" class="btn btn-danger btn-sm" onclick="removeAgenda(this)"><i class="bi bi-trash"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="text-center">1</td>
                                    <td><input type="text" name="agenda[]" class="form-control" placeholder="Agenda 1">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-success btn-sm" onclick="addAgenda()"><i
                                                class="bi bi-plus"></i></button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white mb-3">
                    <strong>Tembusan (Opsional)</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Tembusan</label>
                        <input type="text" name="tembusan"
                            class="form-control @error('tembusan') is-invalid @enderror"
                            placeholder="Contoh: Direktur Utama, Bagian Keuangan, Arsip" value="{{ old('tembusan', $suratUndanganRapat->tembusan) }}">
                        <div class="form-text text-muted">Gunakan tanda koma (,) jika tembusan lebih dari satu.</div>
                        @error('tembusan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white mb-3">
                    <strong>Lampiran (Opsional)</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Dokumen Lampiran <small class="text-muted fw-normal">(PDF/Gambar)</small></label>
                        @if($suratUndanganRapat->file_lampiran)
                            <div class="mb-2">
                                <a href="{{ asset('storage/' . $suratUndanganRapat->file_lampiran) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-file-earmark-pdf"></i> Lihat Lampiran Saat Ini
                                </a>
                            </div>
                        @endif
                        <div class="upload-container shadow-sm border rounded p-4 text-center bg-light"
                            id="lampiran-drop-zone">
                            <i class="bi bi-file-earmark-pdf fs-1 text-secondary mb-2 d-block"></i>
                            <span class="d-block mb-3 text-black small">Max 5MB (Ganti lampiran jika perlu)</span>
                            <input type="file" name="file_lampiran" id="lampiran-input" class="d-none">
                            <button type="button" class="btn btn-outline-primary btn-sm px-4 text-white"
                                onclick="document.getElementById('lampiran-input').click()">
                                Pilih Dokumen Baru
                            </button>
                            <div id="lampiran-preview-container" class="mt-3 d-none text-start">
                                <div class="d-flex align-items-center p-2 border rounded ">
                                    <i class="bi bi-check-circle-fill text-success fs-4 me-2"></i>
                                    <div class="overflow-hidden">
                                        <p class="mb-0 fw-bold small text-truncate" id="lampiran-filename"></p>
                                        <span class="small text-muted" id="lampiran-filesize"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mb-5">
                <a href="{{ route('administrasi.surat-undangan-rapat.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" id="submitBtn" class="btn btn-primary px-4 shadow">
                    <span class="btn-text fw-bold">Perbarui Surat Undangan</span>
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Memperbarui...
                    </span>
                </button>
            </div>

        </form>
    </div>

@endsection

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

@push('script')
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

        document.addEventListener('DOMContentLoaded', function() {
            // =========================
            // Lampiran Preview
            // =========================
            const lampiranDropZone = document.getElementById('lampiran-drop-zone');
            const lampiranInput = document.getElementById('lampiran-input');
            const lampiranPreviewContainer = document.getElementById('lampiran-preview-container');
            const lampiranFilename = document.getElementById('lampiran-filename');
            const lampiranFilesize = document.getElementById('lampiran-filesize');

            lampiranDropZone?.addEventListener('click', function(e) {
                if (e.target !== lampiranInput && !e.target.closest('button')) {
                    lampiranInput.click();
                }
            });

            lampiranInput?.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    lampiranPreviewContainer.classList.remove('d-none');
                    lampiranFilename.textContent = this.files[0].name;

                    let size = this.files[0].size / 1024;
                    lampiranFilesize.textContent =
                        size > 1024 ?
                        (size / 1024).toFixed(2) + ' MB' :
                        size.toFixed(2) + ' KB';
                } else {
                    lampiranPreviewContainer.classList.add('d-none');
                }
            });

            if (lampiranDropZone) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    lampiranDropZone.addEventListener(eventName, e => {
                        e.preventDefault();
                        e.stopPropagation();
                    }, false);
                });

                ['dragenter', 'dragover'].forEach(eventName => {
                    lampiranDropZone.addEventListener(eventName, () => lampiranDropZone.classList.add('dragover'), false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    lampiranDropZone.addEventListener(eventName, () => lampiranDropZone.classList.remove('dragover'), false);
                });

                lampiranDropZone.addEventListener('drop', e => {
                    const dt = e.dataTransfer;
                    if (dt.files && dt.files.length > 0) {
                        lampiranInput.files = dt.files;
                        lampiranInput.dispatchEvent(new Event('change'));
                    }
                }, false);
            }

            // =========================
            // Submit Loading
            // =========================
            const form = document.querySelector("form");
            form.addEventListener("submit", function() {
                if (form.checkValidity()) {
                    const btn = document.getElementById("submitBtn");
                    btn.disabled = true;
                    btn.querySelector(".btn-text").classList.add("d-none");
                    btn.querySelector(".btn-loading").classList.remove("d-none");
                }
            });
        });
    </script>
@endpush
