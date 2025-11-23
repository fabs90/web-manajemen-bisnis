@extends('layouts.partial.layouts')

@section('page-title', 'Agenda Perjalanan | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-row')
{{-- Alert sukses --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Sukses!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Alert error --}}
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Gagal!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm p-3">
    <div class="d-flex justify-content-between mb-3">
        <h5 class="mb-0">Data Agenda Perjalanan</h5>
        <a href="{{ route('administrasi.agenda-perjalanan.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Agenda Perjalanan
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="agenda-perjalanan">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Nama Pelaksana</th>
                    <th>Jabatan</th>
                    <th>Tujuan</th>
                    <th>Tanggal</th>
                    <th>Keperluan</th>
                    <th>Total Biaya</th>
                    <th style="width: 120px">Aksi</th>
                </tr>
            </thead>

            <tbody>
            @if ($agenda->isEmpty())
                <tr>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>   <em>Belum ada data Agenda Perjalanan ✈️.</em></td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                    <td colspan="9" class="text-center text-muted py-3">
                     -
                    </td>
                </tr>
            @else
                @foreach ($agenda as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td>{{ $item->nama_pelaksana }}</td>

                        <td>{{ $item->jabatan }}</td>

                        <td>{{ $item->tujuan }}</td>

                        <td>
                            {{ $item->tanggal_mulai ? date('d-m-Y', strtotime($item->tanggal_mulai)) : '-' }}
                            s/d
                            {{ $item->tanggal_selesai ? date('d-m-Y', strtotime($item->tanggal_selesai)) : '-' }}
                        </td>

                        <td>{{ $item->keperluan ?? '-' }}</td>

                        <td>Rp {{ number_format($item->total_biaya, 0, ',', '.') }}</td>

                        <td class="text-center">
                            <a href="{{ route('administrasi.agenda-perjalanan.show', $item->id) }}"
                               class="btn btn-info btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>

                            <form action="{{ route('administrasi.agenda-perjalanan.destroy', $item->id) }}"
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
            @endif
            </tbody>

        </table>
    </div>
</div>

@endsection

@push('script')
<script>
    $(document).ready(function () {
        $('#agenda-perjalanan').DataTable({
            responsive: true,
            pageLength: 10,
        });
    });
</script>
@endpush
