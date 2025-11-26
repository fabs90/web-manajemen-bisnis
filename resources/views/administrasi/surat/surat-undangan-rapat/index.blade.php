@extends('layouts.partial.layouts')

@section('page-title', 'Agenda Surat Undangan Rapat | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-row')
    <div class="container mt-4">
        {{-- Alert sukses --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <strong>Sukses!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <strong>DATA SURAT UNDANGAN RAPAT</strong>
                <a href="{{ route('administrasi.surat-undangan-rapat.create') }}" class="btn btn-light btn-sm">
                    <i class="fa fa-plus me-1"></i> Tambah Surat Undangan Rapat
                </a>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="surat-undangan-rapat-table">
                        <thead class="table-light text-center">
                            <tr>
                                <th width="5%">No</th>
                                <th>Nomor Surat</th>
                                <th>Perihal / Judul Rapat</th>
                                <th>Hari / Tanggal</th>
                                <th>Waktu</th>
                                <th>Tempat</th>
                                <th width="13%">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($agendaSuratUndanganRapat as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>

                                    <td>{{ $item->nomor_surat ?? '-' }}</td>

                                    <td>{{ $item->perihal ?? ($item->judul_rapat ?? '-') }}</td>

                                    <td class="text-center">
                                        {{ $item->hari ?? '-' }},
                                        {{ $item->tanggal_rapat ? \Carbon\Carbon::parse($item->tanggal_rapat)->format('d-m-Y') : '-' }}
                                    </td>

                                    <td class="text-center">
                                        {{ $item->waktu_mulai ? \Carbon\Carbon::parse($item->waktu_mulai)->format('H:i') : '-' }}
                                        s/d
                                        {{ $item->waktu_selesai ? \Carbon\Carbon::parse($item->waktu_selesai)->format('H:i') : '-' }}
                                        WIB
                                    </td>

                                    <td>{{ $item->tempat ?? '-' }}</td>

                                    <td class="text-center">

                                        <a href="#" class="btn btn-info btn-sm"">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <a href="{{ route('administrasi.surat-undangan-rapat.pdf', ['id' => $item->id]) }}"
                                            class="btn btn-warning btn-sm" target="_blank">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>

                                        <form action="{{ route('administrasi.surat-undangan-rapat.destroy', $item->id) }}"
                                            method="POST" class="d-inline delete-form">

                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm delete-btn" type="submit">
                                                <span class="delete-text"><i class="bi bi-trash"></i></span>
                                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
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
        $(document).ready(function() {
            $('#surat-undangan-rapat-table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    emptyTable: "Belum ada data agenda surat undangan rapat 📪"
                }
            });

            $('.delete-form').on('submit', function(e) {
                e.preventDefault();
                let form = this;

                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let btn = $(form).find('.delete-btn');
                        btn.prop('disabled', true);
                        btn.find('.delete-text').addClass('d-none');
                        btn.find('.spinner-border').removeClass('d-none');
                        form.submit();
                    }
                });

            });
        });
    </script>
@endpush
