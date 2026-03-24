@extends('layouts.partial.layouts')

@section('page-title', 'Agenda Surat Keluar')
@section('section-heading', 'Agenda Surat Keluar')

@section('section-row')

    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('administrasi.surat-keluar.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Surat Keluar
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0" id="surat-keluar-table">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nomor Surat</th>
                            <th>Tanggal Surat</th>
                            <th>Nama Penerima</th>
                            <th>Perihal</th>
                            <th>Tembusan</th>
                            <th width="150px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($suratKeluar as $index => $surat)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $surat->nomor_surat }}</td>
                                <td>{{ \Carbon\Carbon::parse($surat->tanggal_surat)->format('d/m/Y') }}</td>
                                <td>{{ $surat->nama_penerima }}</td>
                                <td>{{ $surat->perihal }}</td>
                                <td>{{ $surat->tembusan ?? '-' }}</td>
                                <td>
                                    <form
                                        action="{{ route('administrasi.surat-keluar.downloadPdf', ['id' => $surat->id]) }}"
                                        method="post" target="_blank">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('administrasi.surat-keluar.destroy', $surat->id) }}"
                                        method="POST" class="d-inline" id="delete-form-{{ $surat->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete({{ $surat->id }})">
                                            <i class="bi bi-trash"></i>
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
@endsection
@push('script')
    <script>
        $(document).ready(function() {
            $('#surat-keluar-table').DataTable();
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

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sukses!',
                text: '{{ session('success') }}',
                showConfirmButton: true,
                timer: 2500,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            });
        @endif
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: '{{ session('error') }}',
                showConfirmButton: true,
                timer: 2500,
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
