@extends('layouts.partial.layouts')
@section('page-title', 'Kartu Gudang | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'List Kartu Gudang')
@section('section-row')

    <div class="alert alert-success d-flex align-items-center mb-4 shadow-sm" role="alert">
        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
        <div>
            <h5 class="alert-heading mb-1">Total Nilai Persediaan Barang Dagang Akhir: <strong>Rp {{ number_format($totalNilaiPersediaan ?? 0, 0, ',', '.') }}</strong></h5>
            <small>
                Nilai ini didapatkan dari akumulasi seluruh barang dagang. Rumus per barang: <br>
                <em>(Saldo Persatuan Terakhir di Kartu Gudang) × (Harga Beli Per Unit Barang)</em>
            </small>
        </div>
    </div>
    @forelse ($barang as $item)
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center pb-0 flex-wrap">
                <div class="mb-2 mb-md-0">
                    <h6 class="mb-0">
                        <strong>{{ $item->kode_barang }}</strong> — {{ $item->nama }}
                    </h6>
                    <small class="text-muted d-block mt-1">
                        Harga Beli Unit: Rp {{ number_format($item->harga_beli_per_unit, 0, ',', '.') }} |
                        Harga Jual Unit: Rp {{ number_format($item->harga_jual_per_unit, 0, ',', '.') }} |
                        Jumlah Unit Per-Kemasan: {{ $item->jumlah_unit_per_kemasan }}
                    </small>
                    <div class="mt-2 text-primary">
                        <small>
                            <strong>Kalkulasi Persediaan:</strong> 
                            {{ $item->saldo_akhir }} unit (Saldo Akhir) × Rp {{ number_format($item->harga_beli_per_unit, 0, ',', '.') }} (Harga Beli) 
                            = <strong>Rp {{ number_format($item->nilai_persediaan, 0, ',', '.') }}</strong>
                        </small>
                    </div>
                </div>
                <div>
                    <a href="{{ route('kartu-gudang.create', ['barang_id' => $item->id]) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Data
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm kartu-gudang-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Diterima</th>
                                <th>Dikeluarkan</th>
                                <th>Uraian</th>
                                <th>Saldo Persatuan</th>
                                <th>Saldo Perkemasan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($item->kartuGudang as $kartu)
                                <tr>
                                    <td>{{ $kartu->tanggal->format('d-m-Y') }}</td>
                                    <td>{{ $kartu->diterima }}</td>
                                    <td>{{ $kartu->dikeluarkan }}</td>
                                    <td>{{ $kartu->uraian }}</td>
                                    <td>{{ $kartu->saldo_persatuan }}</td>
                                    <td>{{ $kartu->saldo_perkemasan }}</td>
                                    <td>
                                        <form action="{{ route('kartu-gudang.destroy', $kartu->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Yakin ingin menghapus barang ini?')">
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
                                    <td colspan="6" class="text-center text-muted py-3">
                                        <em>Tidak ada data kartu gudang untuk barang ini.</em>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info">
            Tidak ada barang yang tersedia.
        </div>
    @endforelse
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            $('.kartu-gudang-table').each(function() {
                $(this).DataTable({
                    searching: true,
                    paging: true,
                    info: true,
                    ordering: true,
                    responsive: true,
                    columnDefs: [{
                        targets: "_all",
                        defaultContent: ""
                    }]
                });
            });
        });

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sukses!',
                text: '{{ session('success') }}',
                showConfirmButton: true,
                timer: 2500,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            });
        @endif
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: '{{ session('error') }}',
                showConfirmButton: true,
                timer: 2500,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            });
        @endif
    </script>
@endpush
