@extends('layouts.partial.layouts')

@section('page-title', 'Kas Kecil | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Daftar Kas Kecil')

@section('section-row')

<div class="card shadow-sm p-3">
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
                    <th>Nama Pemohon</th>
                    <th>Departemen</th>
                    <th>Penerimaan</th>
                    <th>Pengeluaran</th>
                    <th>Saldo Akhir</th>
                    <th style="width: 120px">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @if ($kasKecil->isEmpty())
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            <em>Belum ada data kas kecil.</em>
                        </td>
                    </tr>
                @else
                    @foreach ($kasKecil as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ date('d-m-Y', strtotime($item->tanggal)) }}</td>

                            <td>
                                {{ $item->nomor_referensi }}
                            </td>
                            <td>
                                {{$item->KasKecilFormulir->pluck('nama_pemohon')->join(', ')}}
                            </td>
                            <td>{{ $item->KasKecilFormulir->first()->departemen ?? '-' }}</td>
                            <td>
                                {{ $item->penerimaan }}
                            </td>
                            <td>
                                {{ $item->pengeluaran }}
                            </td>
                            <td>
                                {{ $item->saldo_akhir }}
                            </td>

                            <td class="text-center">
                                <a href="#" class="btn btn-info btn-sm">
                                    <i class="bi bi-eye"></i> Detail
                                </a>


                                <form action="{{route('administrasi.kas-kecil.destroy', ['kasId' => $item->id])}}"
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

@push('scripts')
<script>
    $(document).ready(function () {
        $('#kas-kecil-table').DataTable({
            responsive: true,
            pageLength: 10,
        });
    });
</script>
@endpush
