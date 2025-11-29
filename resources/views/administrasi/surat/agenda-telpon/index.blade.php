@extends('layouts.partial.layouts')

@section('page-title', 'Agenda Janji Telpon | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Daftar Agenda Janji Telpon')

@section('section-row')

    <div class="container mt-4">
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
        <a href="{{ route('administrasi.agenda-telpon.create') }}" class="btn btn-primary mb-3">+ Tambah Agenda</a>
        <div class="card mb-4">
            <div class="card-header text-white bg-warning fw-bold">
                Agenda Belum Dilaksanakan
            </div>
            <div class="card-body">

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Tgl Panggilan</th>
                            <th>Nama Penelpon</th>
                            <th>No Telpon</th>
                            <th>Jadwal</th>
                            <th>Keperluan</th>
                            <th>Status Tingkat</th>
                            <th>Status</th>
                            <th>(Centang Jika Sudah Dilakukan)</th>
                            <th width="110px">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($agendaBelum as $item)
                                <tr>
                                    <td>{{ $item->tgl_panggilan }}</td>
                                    <td>{{ $item->nama_penelpon }}</td>
                                    <td>{{ $item->nomor_telpon }}</td>
                                    <td>
                                        {{ $item->jadwal_tanggal }}<br>
                                        {{ $item->jadwal_waktu }}
                                    </td>
                                    <td>{{ $item->keperluan }}</td>
                                    <td>
                                        @php
                                            $badge = [
                                                'urgent' => 'danger',
                                                'penting' => 'warning',
                                                'normal' => 'primary',
                                                'dijadwalkan' => 'secondary',
                                            ];
                                            $class = $badge[$item->tingkat_status] ?? 'secondary';
                                        @endphp

                                        <span class="badge bg-{{ $class }}">
                                            {{ ucfirst($item->tingkat_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($item->status == 'terkonfirmasi')
                                            <span class="badge bg-success">Terkonfirmasi</span>
                                        @elseif ($item->status == 'reschedule')
                                            <span class="badge bg-info text-dark">Reschedule</span>
                                        @elseif ($item->status == 'dibatalkan')
                                            <span class="badge bg-danger">Dibatalkan</span>
                                            @elseif ($item->status == 'selesai')
                                                <span class="badge bg-success">Selesai</span>
                                        @else
                                            <span class="badge bg-secondary">Belum Ditentukan</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('administrasi.agenda-telpon.update-done', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="checkbox" onchange="this.form.submit()" {{ $item->is_done ? 'checked' : '' }}>
                                        </form>
                                    </td>

                                    <td>
                                        <a href="{{ route('administrasi.agenda-telpon.show', $item->id) }}"
                                            class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('administrasi.agenda-telpon.destroy', $item->id) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus agenda ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                        @endforeach
                    </tbody>

                </table>

            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-success text-white fw-bold">
                Agenda Sudah Dilaksanakan
            </div>
            <div class="card-body">

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Tgl Panggilan</th>
                            <th>Nama Penelpon</th>
                            <th>No Telpon</th>
                            <th>Jadwal</th>
                            <th>Keperluan</th>
                            <th>Status Tingkat</th>
                            <th>Status</th>
                            <th>Check</th>
                            <th width="110px">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    @foreach ($agendaSelesai as $item)
                    <tr>
                        <td>{{ $item->tgl_panggilan }}</td>
                        <td>{{ $item->nama_penelpon }}</td>
                        <td>{{ $item->nomor_telpon }}</td>
                        <td>{{ $item->jadwal_tanggal }}<br>{{ $item->jadwal_waktu }}</td>
                        <td>{{ $item->keperluan }}</td>
                        <td>
                            <span class="badge bg-{{ $badge[$item->tingkat_status] ?? 'secondary' }}">
                                {{ ucfirst($item->tingkat_status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success">Selesai</span>
                        </td>
                        <td class="text-center">
                            <form action="{{ route('administrasi.agenda-telpon.update-done', $item->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="checkbox" onchange="this.form.submit()" {{ $item->is_done ? 'checked' : '' }}>
                            </form>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info text-white"
                                data-bs-toggle="modal" data-bs-target="#detailModal{{ $item->id }}">
                                Detail
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @foreach ($agendaSelesai as $item)
                <!-- Modal Detail -->
                <div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1"
                    aria-labelledby="detailModalLabel{{ $item->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">

                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title" id="detailModalLabel{{ $item->id }}">
                                    Detail Agenda Telpon
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body p-3">
                                <table class="table table-bordered">
                                    <tr>
                                        <th class="bg-primary">Tanggal Panggilan</th>
                                        <td>{{ $item->tgl_panggilan }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-primary">Waktu Panggilan</th>
                                        <td>{{ $item->waktu_panggilan }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-primary">Nama Penelpon</th>
                                        <td>{{ $item->nama_penelpon }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-primary">Perusahaan</th>
                                        <td>{{ $item->perusahaan }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-primary">No Telpon</th>
                                        <td>{{ $item->nomor_telpon }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-primary">Jadwal</th>
                                        <td>{{ $item->jadwal_tanggal }} - {{ $item->jadwal_waktu }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-primary">Keperluan</th>
                                        <td>{{ $item->keperluan }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-primary">Tingkat Status</th>
                                        <td>{{ ucfirst($item->tingkat_status) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-primary">Status</th>
                                        <td>{{ ucfirst($item->status) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-primary">Catatan Khusus</th>
                                        <td>{{ $item->catatan_khusus ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
