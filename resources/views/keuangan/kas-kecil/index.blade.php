@extends('layouts.partial.layouts')

@section('page-title', 'Pengisian Kas Kecil | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Pengisian Kas Kecil')

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
    <div class="d-flex justify-content-end mb-3">
        <a href="{{route('keuangan.pengeluaran-kas-kecil.create')}}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Isi Kas Kecil
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="kas-kecil-table">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>No.Referensi</th>
                    <th>Uraian</th>
                    <th>Penerimaan</th>
                    <th>Saldo Akhir</th>
                    <th style="width: 120px">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @if ($kasKecilLogs->isEmpty())
                    <tr>
                        <td colspan="9" class="text-center text-muted py-3">
                            <em>Belum ada data kas kecil.</em>
                        </td>
                    </tr>
                @else
                    @foreach ($kasKecilLogs as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ date('d-m-Y', strtotime($item->tanggal)) }}</td>

                            <td>
                                {{ $item->bukuBesarKas->kode }}
                            </td>
                            <td>
                                {{$item->uraian}}
                            </td>
                            <td>
                                Rp {{ number_format($item->jumlah  ?? 0, 0, ',', '.') }}
                            </td>
                            <td>
                                Rp {{ number_format($item->kasKecil->saldo_akhir  ?? 0, 0, ',', '.') }}
                            </td>

                            <td class="text-center">
                                <form action="#"
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
