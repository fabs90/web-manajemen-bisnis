@extends('layouts.partial.layouts')

@section('page-title', 'Surat Keluar | Digitrans - Administrasi Surat')
@section('section-heading', 'Form Surat Keluar')

@section('section-row')
    <div class="container pb-5">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form id="surat-keluar-form" action="{{ route('administrasi.surat-keluar.store') }}" method="POST"
            enctype="multipart/form-data">
            @csrf

            <div class="row g-4">
                {{-- Kiri: Detail Surat & Penerima --}}
                <div class="col-lg-7">
                    {{-- Section 1: Data Utama --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header py-3">
                            <h5 class="card-title mb-0 text-primary fw-bold">
                                <i class="bi bi-info-circle me-2"></i>Informasi Utama Surat
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Nomor Surat</label>
                                    <input type="text" name="nomor_surat" class="form-control"
                                        placeholder="001/SK/II/2024" value="{{ old('nomor_surat') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Tanggal Surat</label>
                                    <input type="date" name="tanggal_surat" class="form-control"
                                        value="{{ old('tanggal_surat', date('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">Perihal</label>
                                    <input type="text" name="perihal" class="form-control"
                                        placeholder="Contoh: Permohonan Kerjasama" value="{{ old('perihal') }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Lampiran (Teks)</label>
                                    <input type="text" name="lampiran_text" class="form-control"
                                        placeholder="Contoh: 1 (satu) Berkas atau -" value="{{ old('lampiran_text') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Penerima --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header py-3">
                            <h5 class="card-title mb-0 text-primary fw-bold">
                                <i class="bi bi-person-down me-2"></i>Informasi Penerima
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email Penerima</label>
                                <input type="text" name="email_penerima" class="form-control"
                                    placeholder="email@example.com" value="{{ old('email_penerima') }}" required>
                                <small class="text-muted">Gunakan koma (,) untuk memisahkan beberapa email</small>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Nama Penerima</label>
                                    <input type="text" name="nama_penerima" class="form-control"
                                        placeholder="Nama Lengkap" value="{{ old('nama_penerima') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Jabatan Penerima</label>
                                    <input type="text" name="jabatan_penerima" class="form-control"
                                        placeholder="Contoh: Direktur Utama" value="{{ old('jabatan_penerima') }}">
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-semibold">Alamat Penerima</label>
                                <textarea name="alamat_penerima" class="form-control" rows="3" placeholder="Alamat lengkap instansi/penerima"
                                    required>{{ old('alamat_penerima') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: Isi Surat --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header  py-3">
                            <h5 class="card-title mb-0 text-primary fw-bold">
                                <i class="bi bi-file-earmark-text me-2"></i>Isi Konten Surat
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Paragraf Pembuka</label>
                                <textarea name="paragraf_pembuka" class="form-control" rows="3"
                                    placeholder="Contoh: Dengan hormat, sehubungan dengan..." required>{{ old('paragraf_pembuka') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Inti / Isi Surat</label>
                                <textarea name="paragraf_isi" class="form-control" rows="8"
                                    placeholder="Tuliskan detail maksud dan tujuan surat di sini..." required>{{ old('paragraf_isi') }}</textarea>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-semibold">Paragraf Penutup</label>
                                <textarea name="paragraf_penutup" class="form-control" rows="3"
                                    placeholder="Contoh: Demikian permohonan ini kami sampaikan..." required>{{ old('paragraf_penutup') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kanan: Pengirim, Tembusan & Upload --}}
                <div class="col-lg-5">
                    {{-- Section 4: Pengirim --}}
                    <div class="card shadow-sm mb-4 border-4">
                        <div class="card-header  py-3">
                            <h5 class="card-title mb-0 text-primary fw-bold">
                                <i class="bi bi-person-up me-2"></i>Informasi Pengirim
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Pengirim</label>
                                <input type="text" name="nama_pengirim" class="form-control"
                                    placeholder="Nama yang bertanda tangan" value="{{ old('nama_pengirim') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jabatan Pengirim</label>
                                <input type="text" name="jabatan_pengirim" class="form-control"
                                    placeholder="Jabatan resmi" value="{{ old('jabatan_pengirim') }}" required>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-semibold">Tembusan <small
                                        class="text-muted fw-normal">(Opsional)</small></label>
                                <textarea name="tembusan" class="form-control" rows="3" placeholder="1. Direktur Utama&#10;2. Arsip">{{ old('tembusan') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Section 5: Lampiran Visual --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header  py-3">
                            <h5 class="card-title mb-0 text-primary fw-bold">
                                <i class="bi bi-cloud-upload me-2"></i>File Lampiran & TTD
                            </h5>
                        </div>
                        <div class="card-body">
                            {{-- Stylish TTD Upload --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Tanda Tangan digital</label>
                                <div class="upload-container shadow-sm border rounded p-4 text-center bg-light"
                                    id="ttd-drop-zone">
                                    <i class="bi bi-pen fs-1 text-secondary mb-2 d-block"></i>
                                    <span class="d-block mb-3 text-muted small">Format: JPG, PNG (Max 2MB)</span>
                                    <input type="file" name="ttd" id="ttd-input" class="d-none"
                                        accept="image/*">
                                    <button type="button" class="btn btn-outline-primary btn-sm px-4"
                                        onclick="document.getElementById('ttd-input').click()">
                                        Pilih File TTD
                                    </button>
                                    <div id="ttd-preview-container" class="mt-3 d-none">
                                        <img id="ttd-preview" src="#" alt="Preview"
                                            class="img-fluid rounded border shadow-sm" style="max-height: 120px;">
                                        <p class="mb-0 mt-2 small text-success fw-bold" id="ttd-filename"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- Stylish File Lampiran Upload --}}
                            <div class="mb-0">
                                <label class="form-label fw-semibold">Dokumen Lampiran<small
                                        class="text-muted fw-normal">(PDF/Gambar)</small></label>
                                <div class="upload-container shadow-sm border rounded p-4 text-center bg-light"
                                    id="lampiran-drop-zone">
                                    <i class="bi bi-file-earmark-pdf fs-1 text-secondary mb-2 d-block"></i>
                                    <span class="d-block mb-3 text-muted small">Max 5MB (1 File)</span>
                                    <input type="file" name="file_lampiran" id="lampiran-input" class="d-none">
                                    <button type="button" class="btn btn-outline-primary btn-sm px-4 text-dark"
                                        onclick="document.getElementById('lampiran-input').click()">
                                        Pilih Dokumen
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

                    {{-- Actions --}}
                    <div class="sticky-bottom py-3 bg-white border border-top shadow-lg rounded-4 px-3 mb-4"
                        style="bottom: 1.5rem; z-index: 1000;">
                        <button type="submit" id="submitBtn" class="btn btn-primary btn-lg w-100 shadow-sm rounded-3">
                            <span class="btn-text fw-bold">Kirim & Simpan Surat Keluar</span>
                            <span class="btn-loading d-none">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                Memproses...
                            </span>
                        </button>
                        <a href="{{ route('administrasi.surat-keluar.index') }}"
                            class="btn btn-link w-100 text-white mt-2 text-decoration-none small">Kembali</a>
                    </div>
                </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // =========================
            // TTD Preview
            // =========================
            const ttdInput = document.getElementById('ttd-input');
            const ttdPreview = document.getElementById('ttd-preview');
            const ttdPreviewContainer = document.getElementById('ttd-preview-container');
            const ttdFilename = document.getElementById('ttd-filename');

            ttdInput?.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        ttdPreview.src = e.target.result;
                        ttdPreviewContainer.classList.remove('d-none');
                        ttdFilename.textContent = ttdInput.files[0].name;
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });

            // =========================
            // Lampiran Preview
            // =========================
            const lampiranInput = document.getElementById('lampiran-input');
            const lampiranPreviewContainer = document.getElementById('lampiran-preview-container');
            const lampiranFilename = document.getElementById('lampiran-filename');
            const lampiranFilesize = document.getElementById('lampiran-filesize');

            lampiranInput?.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    lampiranPreviewContainer.classList.remove('d-none');
                    lampiranFilename.textContent = this.files[0].name;

                    let size = this.files[0].size / 1024;
                    lampiranFilesize.textContent =
                        size > 1024 ?
                        (size / 1024).toFixed(2) + ' MB' :
                        size.toFixed(2) + ' KB';
                }
            });

            // =========================
            // Submit Loading
            // =========================
            const form = document.getElementById("surat-keluar-form");
            form.addEventListener("submit", function(e) {
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
