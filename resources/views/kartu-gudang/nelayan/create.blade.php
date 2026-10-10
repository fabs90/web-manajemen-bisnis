@extends('layouts.partial.layouts')

@section('page-title', 'Tambah Stok Ikan | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Atur Stok Ikan (Kartu Gudang)')

@section('section-row')
    <div class="container-fluid">
        <div class="row">
            <!-- Info Hasil Tangkapan Card -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Hasil Tangkapan</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 text-center">
                            <h4 class="fw-bold text-primary mb-1">{{ $barang->nama }}</h4>
                            <span class="badge bg-secondary px-3 py-2">{{ $barang->kode_barang }}</span>
                        </div>
                        <hr class="my-4">

                        <div class="row g-3">
                            @php
                                $latest = $barang->kartuGudang()->latest()->first();
                                $stokKg = $latest->saldo_persatuan ?? 0;
                            @endphp
                            <div class="col-6 text-center">
                                <small class="text-muted d-block text-uppercase small fw-bold">Stok Saat Ini</small>
                                <span class="fs-4 fw-bold text-primary">{{ number_format($stokKg, 0, ',', '.') }}</span>
                                <small class="d-block text-muted">Kg</small>
                            </div>
                            <div class="col-6 text-center border-start">
                                <small class="text-muted d-block text-uppercase small fw-bold">Harga Jual / Kg</small>
                                <span class="fs-5 fw-bold text-success">Rp {{ number_format($barang->harga_jual_per_unit, 0, ',', '.') }}</span>
                                <small class="d-block text-muted">Per-kilogram</small>
                            </div>
                        </div>

                        <div class="mt-4 p-3 rounded-3 border">
                            <h6 class="fw-bold mb-2 small text-uppercase text-muted">Detail Satuan</h6>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Satuan Utama</span>
                                <span class="small fw-bold">Kilogram (Kg)</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="small">Konversi Sistem</span>
                                <span class="small fw-bold text-info">1 Unit = 1 Kg</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header border-bottom py-3">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Input Mutasi Stok Ikan</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('kartu-gudang.store', ['barang_id' => $barang->id]) }}" method="post">
                            @csrf
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4"
                                    role="alert">
                                    <div class="d-flex">
                                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="tanggal" class="form-label fw-bold">Tanggal Transaksi <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text border-end-0"><i
                                                class="bi bi-calendar3"></i></span>
                                        <input type="date" class="form-control border-start-0" id="tanggal"
                                            name="tanggal" required value="{{ old('tanggal', date('Y-m-d')) }}">
                                    </div>
                                    <div class="form-text mt-2">Pilih tanggal pergerakan stok ikan.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="uraian" class="form-label fw-bold">Uraian / Keterangan <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i
                                                class="bi bi-chat-dots"></i></span>
                                        <input type="text" class="form-control border-start-0" id="uraian"
                                            name="uraian" placeholder="Contoh: Hasil Tangkapan Melaut Pagi" required
                                            value="{{ old('uraian') }}">
                                    </div>
                                    <div class="form-text mt-2">Keterangan singkat mengenai pergerakan stok ikan.</div>
                                </div>

                                <div class="col-12">
                                    <hr class="my-2">
                                    <p class="text-muted small fw-bold text-uppercase mb-3">Detail Pergerakan Ikan (Per-Kilogram)
                                    </p>
                                </div>

                                <div class="col-md-6">
                                    <div
                                        class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3">
                                        <label for="diterima" class="form-label fw-bold text-success">
                                            <i class="bi bi-arrow-down-left-circle me-1"></i> Ikan Masuk (Per-kg)
                                        </label>
                                        <div class="input-group">
                                            <input type="number" class="form-control form-control-lg border-success"
                                                id="diterima" name="diterima" placeholder="0" min="0"
                                                value="{{ old('diterima', 0) }}">
                                            <span class="input-group-text bg-success text-white border-success">Kg</span>
                                        </div>
                                        <div class="form-text text-success small mt-2">Gunakan jika ada hasil tangkapan baru yang masuk (1 unit = 1 kg).</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div
                                        class="p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3">
                                        <label for="dikeluarkan" class="form-label fw-bold text-danger">
                                            <i class="bi bi-arrow-up-right-circle me-1"></i> Ikan Keluar (Per-kg)
                                        </label>
                                        <div class="input-group">
                                            <input type="number" class="form-control form-control-lg border-danger"
                                                id="dikeluarkan" name="dikeluarkan" placeholder="0" min="0"
                                                value="{{ old('dikeluarkan', 0) }}">
                                            <span class="input-group-text bg-danger text-white border-danger">Kg</span>
                                        </div>
                                        <div class="form-text text-danger small mt-2">Gunakan jika ada ikan yang terjual atau keluar gudang (1 unit = 1 kg).</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 pt-3 d-flex justify-content-between align-items-center">
                                <a href="{{ route('kartu-gudang.detail', $barang->id) }}"
                                    class="btn btn-link text-secondary text-decoration-none p-0">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail Ikan
                                </a>
                                <div class="d-flex gap-2">
                                    <button type="reset" class="btn btn-light px-4">Reset</button>
                                    <button type="submit" id="btnSubmit"
                                        class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
                                        <span class="spinner-border spinner-border-sm d-none me-2" role="status"
                                            aria-hidden="true"></span>
                                        <span class="btn-text"><i class="bi bi-check2-circle me-1"></i> Simpan
                                            Transaksi</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            // Logika sederhana: Jika satu diisi > 0, reset yang lain ke 0
            $('#diterima').on('change keyup input', function() {
                if ($(this).val() > 0) {
                    $('#dikeluarkan').val(0);
                }
            });

            $('#dikeluarkan').on('change keyup input', function() {
                if ($(this).val() > 0) {
                    $('#diterima').val(0);
                }
            });

            // Loading button on submit
            $('form').on('submit', function() {
                const $btn = $('#btnSubmit');
                $btn.prop('disabled', true);
                $btn.find('.spinner-border').removeClass('d-none');
                $btn.find('.btn-text').text(' Memproses...');
            });
        });

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3000,
                toast: true,
                position: 'top-end',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            });
        @endif
    </script>
@endpush
