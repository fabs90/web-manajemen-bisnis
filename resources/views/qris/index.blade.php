@extends('layouts.partial.layouts')

@section('page-title', 'Manajemen QRIS')
@section('section-heading', 'Manajemen QRIS')

@section('section-row')
<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">QRIS Barcode Saat Ini</h5>
            </div>
            <div class="card-body text-center">
                @if($user->qris_image)
                    <img src="{{ asset('storage/' . $user->qris_image) }}" alt="QRIS Barcode" class="img-fluid border p-2 mb-3" style="max-height: 400px;">
                    <p class="text-muted">Barcode ini akan ditampilkan pada halaman kasir saat memilih jenis pembayaran QRIS.</p>
                @else
                    <div class="py-5">
                        <i class="text-light mb-3"></i>
                        <p class="text-muted">Anda belum mengunggah QRIS barcode.</p>
                    </div>
                @endif

                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUploadQris">
                    <i class="fas fa-upload me-1"></i> {{ $user->qris_image ? 'Perbarui QRIS' : 'Unggah QRIS' }}
                </button>
                <form action="{{ route('qris.destroy') }}" method="POST" id="deleteQrisForm" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger @if (!$user->qris_image)
                        disabled
                    @endif">
                        <i class="bi bi-trash me-1"></i>Hapus QRIS
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">Informasi QRIS</h5>
            </div>
            <div class="card-body">
                <h6>Apa itu QRIS?</h6>
                <p>QRIS (Quick Response Code Indonesian Standard) adalah standar kode QR nasional untuk memfasilitasi pembayaran kode QR di Indonesia.</p>

                <h6>Bagaimana cara kerjanya?</h6>
                <ul>
                    <li>Unggah gambar QRIS barcode toko Anda di sini.</li>
                    <li>Pada halaman kasir, pilih jenis pembayaran <strong>QRIS</strong>.</li>
                    <li>Sistem akan secara otomatis menampilkan barcode yang Anda unggah untuk dipindai oleh pelanggan.</li>
                </ul>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Pastikan gambar yang Anda unggah jelas dan dapat dipindai dengan mudah.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload QRIS -->
<div class="modal fade" id="modalUploadQris" tabindex="-1" aria-labelledby="modalUploadQrisLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('qris.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUploadQrisLabel">Unggah QRIS Barcode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="qris_image" class="form-label">Pilih Gambar QRIS</label>
                        <input type="file" class="form-control @error('qris_image') is-invalid @enderror" id="qris_image" name="qris_image" accept="image/*" required>
                        @error('qris_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Format: JPEG, PNG, JPG. Maks: 2MB.</div>
                    </div>

                    <div id="preview-container" class="text-center d-none">
                        <p class="fw-bold mb-1">Pratinjau:</p>
                        <img id="image-preview" src="#" alt="Pratinjau QRIS" class="img-fluid border p-1" style="max-height: 200px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan QRIS</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    document.getElementById('qris_image').onchange = evt => {
        const [file] = evt.target.files;
        if (file) {
            const previewContainer = document.getElementById('preview-container');
            const imagePreview = document.getElementById('image-preview');

            imagePreview.src = URL.createObjectURL(file);
            previewContainer.classList.remove('d-none');
        }
    }

    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Sukses!',
            text: "{{ session('success') }}",
            toast: true,
            position: 'top-end',
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if (session('info'))
        Swal.fire({
            icon: 'info',
            title: 'Info',
            text: "{{ session('info') }}",
            toast: true,
            position: 'top-end',
            timer: 3000,
            showConfirmButton: false
        });
    @endif
</script>
@endpush
