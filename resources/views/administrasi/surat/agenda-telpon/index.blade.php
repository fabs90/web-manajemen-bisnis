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
                            <th width="110px">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($agenda as $item)
                            @if ($item->is_done == false)
                                <tr>
                                    <td>{{ $item->tgl_panggilan }}</td>
                                    <td>{{ $item->nama_penelpon }}</td>
                                    <td>{{ $item->nomor_telpon }}</td>
                                    <td>
                                        {{ $item->jadwal_tanggal }}<br>
                                        {{ $item->jadwal_waktu }}
                                    </td>
                                    <td>{{ $item->keperluan }}</td>
                                    <td>{{ ucfirst($item->tingkat_status) }}</td>
                                    <td>
                                        <span class="badge bg-warning text-dark">Belum</span>
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
                            @endif
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
                            <th width="110px">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($agenda as $item)
                            @if ($item->is_done == true)
                                <tr>
                                    <td>{{ $item->tgl_panggilan }}</td>
                                    <td>{{ $item->nama_penelpon }}</td>
                                    <td>{{ $item->nomor_telpon }}</td>
                                    <td>
                                        {{ $item->jadwal_tanggal }}<br>
                                        {{ $item->jadwal_waktu }}
                                    </td>
                                    <td>{{ $item->keperluan }}</td>
                                    <td>{{ ucfirst($item->tingkat_status) }}</td>
                                    <td>
                                        @if ($item->status == 'terkonfirmasi')
                                            <span class="badge bg-success">Terkonfirmasi</span>
                                        @elseif($item->status == 'reschedule')
                                            <span class="badge bg-info text-dark">Reschedule</span>
                                        @elseif($item->status == 'dibatalkan')
                                            <span class="badge bg-danger">Dibatalkan</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info text-white">Detail</a>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>

                </table>

            </div>
        </div>

    </div>

@endsection
