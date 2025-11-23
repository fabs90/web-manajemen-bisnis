@extends('layouts.partial.layouts')

@section('page-title', 'Surat Masuk | Digitrans - Administrasi Surat')
@section('section-heading', 'Surat Masuk')

@section('section-row')

<div class="container">

    {{-- ========================= --}}
    {{-- FORM INPUT SURAT MASUK --}}
    {{-- ========================= --}}
    <div class="card mb-4">
        <div class="card-header fw-bold">Tambah Surat Masuk</div>
        <div class="card-body">
            <form action="{{route('administrasi.surat-masuk.store')}}" method="POST" enctype="multipart/form-data">
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

    {{-- ========================= --}}
    {{-- TABLE AGENDA SURAT MASUK --}}
    {{-- ========================= --}}
    <div class="card">
        <div class="card-header fw-bold">Agenda Surat Masuk</div>
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
                    @forelse ($agendaSuratMasuk as $s)
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
                                    <a href="{{ asset('storage/'.$s->file_surat) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-secondary mb-1">
                                        Lihat Surat
                                    </a>
                                @endif
                                <form id="delete-form-{{ $s->id }}"
                                      action="{{ route('administrasi.surat-masuk.destroy', $s->id) }}"
                                      method="POST"
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
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada surat masuk</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
