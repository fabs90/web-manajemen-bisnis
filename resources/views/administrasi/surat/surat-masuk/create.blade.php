@extends('layouts.partial.layouts')

@section('page-title', 'Surat Masuk | Digitrans - Administrasi Surat')
@section('section-heading', 'Surat Masuk')

@section('section-row')

    <div class="container">
        <div class="card mb-4">
            <div class="card-header fw-bold">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Buat Surat Masuk</h5>
                    </div>
                    <div>
                        <a href="{{ route('administrasi.surat-masuk.index') }}" class="btn btn-primary">
                            Kembali
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('administrasi.surat-masuk.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Nomor Agenda *</label>
                            <input type="text" name="nomor_agenda" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Tanggal Terima *</label>
                            <input type="date" name="tanggal_terima" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Nomor Surat *</label>
                            <input type="text" name="nomor_surat" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Tanggal Surat *</label>
                            <input type="date" name="tanggal_surat" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Pengirim *</label>
                        <input type="text" name="pengirim" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Perihal *</label>
                        <input type="text" name="perihal" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Upload Surat (PDF/JPG)</label>
                        <input type="file" name="file_surat" class="form-control">
                    </div>

                    <button class="btn btn-primary w-100">Simpan Surat</button>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        $('.table-agenda').DataTable({
            paging: true,
            pageLength: 10,
            ordering: true,
            responsive: false,
            info: false,
            language: {
                emptyTable: "Tidak ada data untuk ditampilkan",
                search: "Cari:"
            }
        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data surat akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }

        // -----------------------------
        // SweetAlert feedback dari session
        // -----------------------------
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sukses!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 2500,
                toast: true,
                position: 'top-end',
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: '{{ session('error') }}',
                showConfirmButton: false,
                timer: 3000,
                toast: true,
                position: 'top-end',
            });
        @endif
    </script>
@endpush
