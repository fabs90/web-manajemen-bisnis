@extends('layouts.partial.layouts')

@section('page-title', 'Agenda Janji Temu | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-row')
<div class="container mt-4">
    {{-- Alert sukses --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <strong>Sukses!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <strong>DATA JANJI TEMU</strong>
            <a href="{{ route('administrasi.janji-temu.create') }}" class="btn btn-light btn-sm">
                <i class="fa fa-plus me-1"></i> Tambah Janji Temu
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">

                <table class="table table-bordered table-striped" id="janji-temu-table">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Pembuat</th>
                            <th>Perusahaan</th>
                            <th>Telpon</th>
                            <th>Tgl Janji</th>
                            <th>Waktu</th>
                            <th>Bertemu Dengan</th>
                            <th>Status</th>
                            <th width="13%">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($agendaJanjiTemu as $item)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $item->nama_pembuat }}</td>
                            <td>{{ $item->perusahaan }}</td>
                            <td>{{ $item->nomor_telpon }}</td>
                            <td class="text-center">{{ $item->tgl_janji ?? '-' }}</td>
                            <td class="text-center">{{ $item->waktu ?? '-' }}</td>
                            <td>{{ $item->bertemu_dengan }}</td>

                            <td class="text-center">
                                @if ($item->status == 'terkonfirmasi')
                                    <span class="badge bg-success">Terkonfirmasi</span>
                                @elseif ($item->status == 'reschedule')
                                    <span class="badge bg-warning text-dark">Reschedule</span>
                                @else
                                    <span class="badge bg-danger">Dibatalkan</span>
                                @endif
                            </td>

                            <td class="text-center">

                                <a href="{{route('administrasi.janji-temu.show', $item->id)}}"
                                   class="btn btn-info btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a href="{{route('administrasi.janji-temu.pdf', $item->id)}}"
                                   class="btn btn-warning btn-sm">
                                    <i class="bi bi-file-pdf"></i>
                                </a>

                                <form action="{{route('administrasi.janji-temu.destroy', $item->id)}}"
                                      method="POST" class="d-inline"
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
    $(document).ready(function () {
        $('#janji-temu-table').DataTable({
            responsive: true,
            pageLength: 10,
            language: {
                   emptyTable: "Belum ada data janji temu tersedia 📪"
               }
        });
    });
</script>
@endpush
