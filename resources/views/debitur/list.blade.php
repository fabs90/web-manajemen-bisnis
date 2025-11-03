@extends('layouts.partial.layouts')
@section('page-title', 'List Debitur dan Kreditur')
@section('section-heading', 'List Debitur dan Kreditur')
@section('section-row')

    @if ($pelanggan->isEmpty())
        <div class="alert alert-primary">
            Tidak ada data pelanggan.
        </div>
    @else
        <table class="table" id="table-barang">
            <thead>
                <tr>
                    <th>Nama Pelanggan</th>
                    <th>Kontak Pelanggan</th>
                    <th>Alamat Pelanggan</th>
                    <th>Email Pelanggan</th>
                    <th>Jenis</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pelanggan as $index => $item)
                    <tr>
                        <td><strong>{{ $item->nama }}</strong></td>
                        <td>{{ $item->kontak }}</td>
                        <td>{{ $item->alamat }}</td>
                        <td>{{ $item->email }}</td>
                        <td>
                            @if ($item->jenis == 'debitur')
                                <span class="badge bg-info">Debitur</span>
                            @else
                                <span class="badge bg-success">Kreditur</span>
                            @endif
                        </td>
                        <td>
                            <form action="#" method="POST" class="d-inline"
                                onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">
                            <em>Tidak ada data barang.</em>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif


@endsection
@push('script')
    <script>
        $(document).ready(function() {
            $('#table-barang').DataTable({
                searching: true,
                paging: true,
                responsive: false
            });
        });
    </script>
@endpush
