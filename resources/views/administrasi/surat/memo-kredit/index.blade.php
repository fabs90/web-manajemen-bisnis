@extends('layouts.partial.layouts')

@section('page-title', 'Memo Kredit | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-row')
<div class="card shadow-sm p-3">
{{-- Alert sukses --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <strong>Sukses!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Alert Error --}}
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Gagal!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
    <div class="d-flex justify-content-between mb-3">
        <h5 class="mb-0">Data Memo Kredit</h5>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="kas-kecil-table">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode Faktur</th>
                    <th>Tanggal</th>
                    <th style="width:10%">Nomor Pesanan</th>
                    <th>Jenis Pengiriman</th>
                    <th>Alasan Pengembalian</th>
                    <th>Total Memo Kredit</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                    @foreach ($memoKredit as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->kode_faktur }}</td>
                            <td>
                                <span class="badge bg-primary px-3 py-2">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary px-3 py-2">
                                    {{ $item->nomor_pesanan }}
                                </span>
                            </td>

                            <td>
                                {{$item->jenis_pengiriman}}
                            </td>
                            <td>
                                {{ $item->memoKredit->alasan_pengembalian ?? "-" }}
                            </td>
                            <td>
                                Rp {{ optional($item->memoKredit)->total ? number_format(optional($item->memoKredit)->total, 0, ',', '.') : '-' }}
                            </td>

                            <td class="text-center">
                                <a href="{{route('administrasi.memo-kredit.create', ['fakturId' => $item->id])}}" class="btn btn-info btn-sm">
                                    <i class="bi bi-plus text-white"></i>
                                </a>
                                <a href="{{ $item->memoKredit
                                        ? route('administrasi.memo-kredit.generatePdf', ['fakturId' => $item->id])
                                        : '#' }}"
                                    class="btn btn-success btn-sm {{ $item->memoKredit ? '' : 'disabled' }}">
                                    <i class="bi bi-file-pdf text-white"></i>
                                </a>


                                <form action="{{route('administrasi.memo-kredit.destroy', ['fakturId' => $item->id])}}"
                                      method="POST" class="d-inline delete-form"
                                      >
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm delete-btn"
                                            type="submit"
                                            title="Hapus"
                                            {{ $item->memoKredit ? '' : 'disabled' }}>
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

@endsection

@push('script')
<script>
    $(document).ready(function () {
        $('#kas-kecil-table').DataTable({
            responsive: true,
            pageLength: 10,
            language: {
                emptyTable: "Belum ada Data Memo Kredit📪 (Dibuat dari faktur penjualan)"
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
