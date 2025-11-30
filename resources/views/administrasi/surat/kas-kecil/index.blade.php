@extends('layouts.partial.layouts')

@section('page-title', 'Kas Kecil | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Daftar Kas Kecil')

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
        <h5 class="mb-0">Data Kas Kecil</h5>
        <a href="{{route('administrasi.kas-kecil.create')}}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Kas Kecil
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="kas-kecil-table">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>No. Referensi</th>
                    <th>Uraian</th>
                    <th>Nama Pemohon</th>
                    <th>Departemen</th>
                    <th>Penerimaan</th>
                    <th>Pengeluaran</th>
                    <th>Saldo Akhir</th>
                    <th style="width: 120px">Aksi</th>
                </tr>
            </thead>

            <tbody>
                    @foreach ($kasKecil as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ date('d-m-Y', strtotime($item->tanggal)) }}</td>

                            <td>
                                {{ $item->nomor_referensi }}
                            </td>
                            <td>
                                @if ($item->kasKecilLog->isEmpty())
                                    {{$item->kasKecilDetail->pluck('keterangan')->join(', ')}}
                                @else
                                    {{ $item->kasKecilLog->pluck('uraian')->join(', ') }}
                                @endif
                            </td>
                            <td>
                                {{$item->KasKecilFormulir->pluck('nama_pemohon')->join(', ')}}
                            </td>
                            <td>{{ $item->KasKecilFormulir->first()->departemen ?? '-' }}</td>
                            <td>
                                Rp {{ number_format($item->penerimaan, 0, ',', '.') }}
                            </td>
                            <td>
                                Rp {{ number_format($item->pengeluaran, 0, ',', '.') }}
                            </td>
                            <td>
                                Rp {{ number_format($item->saldo_akhir, 0, ',', '.') }}
                            </td>


                            <td class="text-center">
                                <a href="{{route('administrasi.kas-kecil.generatePdf', ['id' => $item->id])}}" class="btn btn-info btn-sm">
                                    <i class="bi bi-file-pdf text-white"></i>
                                </a>


                                <form action="{{route('administrasi.kas-kecil.destroy', ['kasId' => $item->id])}}"
                                      method="POST" class="d-inline delete-form"
                                      >
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm delete-btn"
                                            type="submit"
                                            title="Hapus"
                                            {{ $item->kasKecilDetail ? '' : 'disabled' }}>
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
                emptyTable: "Belum ada Data Kas Kecil📪"
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
