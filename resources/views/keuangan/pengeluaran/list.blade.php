@extends('layouts.partial.layouts')
@section('page-title', 'Pengeluaran | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'Pengeluaran')
@section('section-row')
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

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Semua Pengeluaran Perusahaan</h5>
    <a href="{{ route('keuangan.pengeluaran.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Tambah Data Pengeluaran
    </a>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle" id="allDatasTable">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Uraian</th>
                <th>Hutang Dagang</th>
                <th>Pembelian Tunai</th>
                <th>Pot. Pembelian</th>
                <th>Lain-lain</th>
                <th>Total Biaya</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($allDatas as $data)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($data->tanggal)->format('d-m-Y') }}</td>
                    <td>{{ $data->uraian }}</td>
                    <td>Rp {{ number_format($data->jumlah_hutang ?? 0, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($data->jumlah_pembelian_tunai ?? 0, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($data->potongan_pembelian ?? 0, 0, ',', '.') }}</td>
                    <td>
                        Rp {{ number_format($data->lain_lain ?? 0, 0, ',', '.') }}
                    </td>
                    <td>Rp {{ number_format($data->jumlah_pengeluaran ?? 0, 0, ',', '.') }}</td>
                    <td>
                        <form id="deleteForm-{{ $data->id }}" action="{{route('keuangan.pengeluaran.destroy', ["id" => $data->id])}}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $data->id }})">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-center"><b>Total Pengeluaran</b></td>
                <td><b>Rp {{ number_format($totalPengeluaran ?? 0, 0, ',', '.') }}</b></td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- ===================== TABEL Hutang ===================== --}}
<h5 class="mt-5 mb-3">Semua Data Hutang Perusahaan</h5>
<div class="table-responsive">
    <table class="table table-sm hutang-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Uraian</th>
                <th>Debit</th>
                <th>Kredit</th>
                <th>Saldo</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($dataHutang as $pelangganId => $items)
            <tr class="table-secondary fw-bold">
                <td colspan="6">
                    {{ $items->first()->pelanggan->nama ?? 'Tidak diketahui' }}
                </td>
            </tr>

            @foreach ($items as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                    <td>{{ $item->uraian }}</td>
                    <td>Rp {{ number_format($item->debit ?? 0, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->kredit ?? 0, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->saldo ?? 0, 0, ',', '.') }}</td>
                    <td>
                        <form
                              action="{{ route('keuangan.hutang.destroy', $item->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')

                            <button type="button" class="btn btn-danger btn-sm delete-btn">
                                <span class="delete-text"><i class="bi bi-trash"></i></span>
                                <span class="spinner-border spinner-border-sm d-none"></span>
                            </button>
                        </form>
                    </td>

                </tr>
            @endforeach
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-3">
                    <em>Tidak ada data piutang.</em>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
@push('script')
<script>
    $(document).ready(function() {
        $('.pengeluaran-table').DataTable({
            searching: false,
            paging: false,
            info: false,
            responsive: false
        });

        $('#allDatasTable').DataTable({
            paging: true,
            pageLength: 10,
            ordering: true,
            responsive: true,
            info: false,
            language: {
                emptyTable: "Tidak ada data untuk ditampilkan",
                search: "Cari:"
            }
        });

        $('.delete-btn').on('click', function () {
            let form = $(this).closest('form');

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

                    let btn = form.find('.delete-btn');
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
