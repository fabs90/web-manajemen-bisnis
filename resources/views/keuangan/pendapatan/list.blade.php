@extends('layouts.partial.layouts')
@section('page-title', 'Penerimaan | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'Penerimaan ')
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
        <h5 class="mb-0">Daftar Penerimaan Perusahaan</h5>
        <a href="{{ route('keuangan.pendapatan.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Data Penerimaan
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle" id="allDatasTable">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tanggal</th>
                    <th>Uraian</th>
                    <th>Piutang Dagang (Kredit)</th>
                    <th>Penjualan Tunai (Kredit)</th>
                    <th>Penjualan Kredit (Kredit)</th>
                    <th>Potongan Penjualan (Debit)</th>
                    <th>Lain-lain (Kredit)</th>
                    <th>Kas (Debit)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($allDatas as $data)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($data->tanggal)->format('d-m-Y') }}</td>
                        <td>{{ $data->uraian }}</td>
                        <td>Rp {{ number_format($data->piutang_dagang ?? 0, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($data->penjualan_tunai ?? 0, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($data->penjualan_kredit ?? 0, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($data->potongan_pembelian ?? 0, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($data->lain_lain ?? 0, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($data->uang_diterima ?? 0, 0, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('keuangan.pendapatan.show', $data->id) }}" class="btn btn-info btn-sm text-white">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            <form id="deleteForm-{{ $data->id }}"
                                action="{{ route('keuangan.pendapatan.destroy', $data->id) }}" method="POST"
                                class="d-inline delete-btn">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger btn-sm"
                                    onclick="confirmDelete({{ $data->id }})">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="8" class="text-end pe-3">Bunga Bank</td>
                    <td>Rp {{ number_format($bunga_bank->bunga_bank ?? 0, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="8" class="text-end pe-3">Total Kas Masuk</td>
                    <td><b>Rp {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}</b></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <h5 class="mb-3">Semua Data Piutang</h5>
    <div class="table-responsive">
        <table class="table table-sm" id="dataPiutangTable">
            <thead>
                <tr>
                    <th>Nama Debitur</th>
                    <th>Tanggal</th>
                    <th>Uraian</th>
                    <th>Debit</th>
                    <th>Kredit</th>
                    <th>Saldo</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dataPiutang as $pelangganId => $items)
                    <tr class="table-secondary fw-bold">
                        <td colspan="7">
                            {{ $items->first()->pelanggan->nama ?? 'Tidak diketahui' }}
                        </td>
                    </tr>

                    @foreach ($items as $item)
                        <tr>
                            <td></td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                            <td>{{ $item->uraian }}</td>
                            <td>Rp {{ number_format($item->debit ?? 0, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->kredit ?? 0, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->saldo ?? 0, 0, ',', '.') }}</td>
                            <td>
                                <form id="deletePiutang-{{ $item->id }}"
                                      action="{{route('keuangan.piutang.destroy', $item->id)}}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger delete-btn">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">
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
            $('#allDatasTable').DataTable({
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
