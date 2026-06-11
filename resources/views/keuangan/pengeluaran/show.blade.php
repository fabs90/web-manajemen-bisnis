@extends('layouts.partial.layouts')
@section('page-title', 'Detail Pengeluaran | Digitrans')

@section('section-heading', 'Detail Pengeluaran')
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
                    @endphp
                    @foreach($entry->items as $item)
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
        
        <div class="mt-3">
            <a href="{{ route('keuangan.pengeluaran.list') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>
@endsection
