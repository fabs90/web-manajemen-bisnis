@extends('layouts.partial.layouts')
@section('page-title', 'Detail Penerimaan Kas Perusahaan | TRANSDIGITAL')

@section('section-heading', 'Detail Penerimaan Kas Perusahaan')
@section('section-row')

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Detail Jurnal Transaksi</h5>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Nomor Referensi:</strong> {{ $entry->reference_number }}<br>
                <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($entry->date)->format('d-m-Y') }}<br>
            </div>
            <div class="col-md-6">
                <strong>Uraian:</strong> {{ $entry->description }}<br>
                <strong>Tipe Transaksi:</strong> {{ ucwords(str_replace('_', ' ', $entry->transaction_type)) }}<br>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Sub Ledger (Buku Tambahan)</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalDebit = 0;
                        $totalCredit = 0;
                        
                        $groupedItems = collect($entry->items)->groupBy(function($item) {
                            return $item->account_id . '-' . $item->sub_ledger_id;
                        })->map(function($items) {
                            $first = $items->first();
                            return (object) [
                                'account' => $first->account,
                                'subLedger' => $first->subLedger,
                                'debit' => $items->sum('debit'),
                                'credit' => $items->sum('credit'),
                            ];
                        })->values();
                    @endphp
                    @foreach($groupedItems as $item)
                        @php
                            $totalDebit += $item->debit;
                            $totalCredit += $item->credit;
                        @endphp
                        <tr>
                            <td>{{ $item->account->code ?? '-' }}</td>
                            <td>{{ $item->account->name ?? 'Unknown Account' }}</td>
                            <td>{{ $item->subLedger->nama ?? '-' }}</td>
                            <td class="text-end">Rp {{ number_format($item->debit, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($item->credit, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end">Total</td>
                        <td class="text-end">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        @if($entry->kartuGudang->count() > 0)
        <h6 class="mt-4 fw-bold">Detail Barang Terjual</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th class="text-center">Jumlah Dikeluarkan</th>
                        <th>Uraian Kartu Gudang</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entry->kartuGudang as $kg)
                    <tr>
                        <td>{{ $kg->barang->kode_barang ?? '-' }}</td>
                        <td>{{ $kg->barang->nama ?? 'Barang Dihapus' }}</td>
                        <td class="text-center">{{ $kg->dikeluarkan }} pcs</td>
                        <td>{{ $kg->uraian }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        
        <div class="mt-3">
            <a href="{{ route('keuangan.pendapatan.list') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>
@endsection
