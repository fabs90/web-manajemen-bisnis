@extends('layouts.partial.layouts')

@section('page-title', 'Edit Surat Undangan Rapat | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
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
                    <strong>Penandatangan</strong>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Penandatangan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_penandatangan"
                                class="form-control @error('nama_penandatangan') is-invalid @enderror"
                                placeholder="Nama lengkap" value="{{ old('nama_penandatangan', $suratUndanganRapat->nama_penandatangan) }}">
                            @error('nama_penandatangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jabatan Penandatangan <span class="text-danger">*</span></label>
                            <input type="text" name="jabatan_penandatangan"
                                class="form-control @error('jabatan_penandatangan') is-invalid @enderror"
                                placeholder="Direktur / Manager" value="{{ old('jabatan_penandatangan', $suratUndanganRapat->jabatan_penandatangan) }}">
                            @error('jabatan_penandatangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Tanda Tangan</label>
                        @if($suratUndanganRapat->ttd)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $suratUndanganRapat->ttd) }}" alt="Tanda Tangan" class="img-fluid border rounded" style="max-height: 100px;">
                                <p class="small text-muted">Tanda tangan saat ini</p>
                            </div>
                        @endif
                        <div class="upload-container shadow-sm border rounded p-4 text-center bg-light" id="ttd-drop-zone"
                            onclick="document.getElementById('ttd-input').click()">
                            <div id="ttd-placeholder">
                                <i class="bi bi-pen fs-1 text-secondary mb-2 d-block"></i>
                                <span class="d-block mb-3 text-muted small">Klik atau Seret Tanda Tangan Baru ke Sini<br>(JPG,
                                    PNG Max 2MB)</span>
                                <button type="button" class="btn btn-outline-primary btn-sm px-4">
                                    Pilih File TTD Baru
                                </button>
                            </div>
                            <input type="file" name="ttd" id="ttd-input" class="d-none" accept="image/*">
                            <div id="ttd-preview-container" class="mt-2 d-none">
                                <div class="position-relative d-inline-block">
                                    <img id="ttd-preview" src="#" alt="Preview"
                                        class="img-fluid rounded border shadow-sm" style="max-height: 120px;">
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success border border-white">
                                        <i class="bi bi-check"></i>
                                    </span>
                                </div>
                                <p class="mb-0 mt-2 small text-success fw-bold">
                                    <i class="bi bi-file-earmark-check me-1"></i> Terpilih: <span
                                        id="ttd-filename"></span>
                                </p>
                                <button type="button"
                                    class="btn btn-link btn-sm text-muted mt-1 text-decoration-none">Ganti Tanda
                                    Tangan</button>
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
            // Drag and Drop Logic
            // =========================
            const ttdDropZone = document.getElementById('ttd-drop-zone');
            const ttdInput = document.getElementById('ttd-input');

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                ttdDropZone.addEventListener(eventName, e => {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                ttdDropZone.addEventListener(eventName, () => ttdDropZone.classList.add('dragover'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                ttdDropZone.addEventListener(eventName, () => ttdDropZone.classList.remove('dragover'),
                    false);
            });

            ttdDropZone.addEventListener('drop', e => {
                const dt = e.dataTransfer;
                ttdInput.files = dt.files;
                ttdInput.dispatchEvent(new Event('change'));
            }, false);

            // =========================
            // TTD Preview
            // =========================
            const ttdPreview = document.getElementById('ttd-preview');
            const ttdPlaceholder = document.getElementById('ttd-placeholder');
            const ttdPreviewContainer = document.getElementById('ttd-preview-container');
            const ttdFilename = document.getElementById('ttd-filename');

            ttdInput?.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        ttdPreview.src = e.target.result;
                        ttdPlaceholder.classList.add('d-none');
                        ttdPreviewContainer.classList.remove('d-none');
                        ttdFilename.textContent = ttdInput.files[0].name;
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });

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
