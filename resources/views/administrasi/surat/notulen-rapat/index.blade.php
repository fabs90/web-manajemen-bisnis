@extends('layouts.partial.layouts')

@section('page-title', 'Agenda Rapat | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

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
                <strong>DATA AGENDA RAPAT</strong>
                <a href="{{ route('administrasi.rapat.create') }}" class="btn btn-light btn-sm">
                    <i class="fa fa-plus me-1"></i> Tambah Agenda Rapat
                </a>
            </div>

            <div class="card-body">
                <div class="table-responsive">

                    <table class="table table-bordered table-striped" id="agenda-rapat-table">
                        <thead class="table-light text-center">
                            <tr>
                                <th width="5%">No</th>
                                <th>Judul Rapat</th>
                                <th>Tempat</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Pimpinan Rapat</th>
                                <th>Notulis</th>
                                <th width="13%">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($agendaRapat as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->judul_rapat }}</td>
                                    <td>{{ $item->tempat }}</td>
                                    <td class="text-center">
                                        {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-center">{{ $item->waktu ?? '-' }}</td>
                                    <td>{{ $item->pimpinan_rapat }}</td>
                                    <td>{{ $item->notulis ?? '-' }}</td>

                                    <td class="text-center">
                                        <a href="#" class="btn btn-info btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="#" class="btn btn-warning btn-sm">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>

                                        <form action="#" method="POST" class="d-inline"
                                            onsubmit="return confirm('Hapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">
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

    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            $('#agenda-rapat-table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    emptyTable: "Belum ada agenda rapat📪"
                }
            });
        });
    </script>
@endpush
