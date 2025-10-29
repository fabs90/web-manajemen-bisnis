@extends('layouts.partial.layouts')
@section('page-title', 'List Kartu Gudang')
@section('section-heading', 'List Kartu Gudang')
@section('section-row')
    @forelse ($barang as $item)
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center pb-0 flex-wrap">
                <div class="mb-2 mb-md-0">
                    <h6 class="mb-0">
                        <strong>{{ $item->kode_barang }}</strong> — {{ $item->nama }}
                    </h6>
                    <small class="text-muted">
                        Harga Beli Unit: Rp {{ number_format($item->harga_beli_per_unit, 0, ',', '.') }} |
                        Harga Jual Unit: Rp {{ number_format($item->harga_jual_per_unit, 0, ',', '.') }}
                    </small>
                </div>
                <div>
                    <a href="{{ route('kartu-gudang.create', ['barang_id' => $item->id]) }}"
                       class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Data
                    </a>
                </div>
            </div>

            <div class="card-body">
                {{-- ✅ Bungkus tabel dengan table-responsive agar bisa di-scroll horizontal di mobile --}}
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
                searching: false,
                paging: false,
                info: false,
                responsive: false
                order: [[0, 'desc']],
                columnDefs: [{
                    targets: "_all",
                    defaultContent: ""
                }]
            });
        });
    });
</script>
@endpush
