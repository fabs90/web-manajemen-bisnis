@extends('layouts.partial.layouts')

@section('page-title', 'Arsip Surat Masuk | Digitrans - Administrasi Surat')
@section('section-heading', 'Arsip Surat Masuk')

@section('section-row')
    <div class="container">
        <div class="row">
            <div class="col">
                <a href="{{ route('administrasi.surat-masuk.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i>
                    Tambah Surat Masuk</a>
            </div>
        </div>
        <div class="row">
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-agenda table-bordered mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Nomor Agenda</th>
                                <th>Tanggal Terima</th>
                                <th>Nomor Surat</th>
                                <th>Pengirim</th>
                                <th>Perihal</th>
                                <th>Status</th>
                                <th width="150px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($agendaSuratMasuk as $s)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $s->nomor_agenda }}</td>
                                    <td>{{ $s->tanggal_terima }}</td>
                                    <td>{{ $s->nomor_surat }}</td>
                                    <td>{{ $s->pengirim }}</td>
                                    <td>{{ $s->perihal }}</td>
                                    <td>
                                        @if ($s->status_disposisi == 'selesai')
                                            <span class="badge bg-success">Sudah Didisposisi</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Belum Didisposisi</span>
                                        @endif
                                    </td>

                                    <td>
                                        <a href="{{ route('administrasi.surat-masuk.disposisi.create', $s->id) }}"
                                            class="btn btn-sm btn-primary mb-1">
                                            Disposisi
                                        </a>
                                        @if ($s->file_surat)
                                            <a href="{{ asset('storage/' . $s->file_surat) }}" target="_blank"
                                                class="btn btn-sm btn-secondary mb-1">
                                                Lihat Pdf
                                            </a>
                                        @endif
                                        <form id="delete-form-{{ $s->id }}"
                                            action="{{ route('administrasi.surat-masuk.destroy', $s->id) }}" method="POST"
                                            style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete({{ $s->id }})"
                                                class="btn btn-sm btn-danger">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
