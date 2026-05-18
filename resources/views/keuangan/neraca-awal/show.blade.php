@extends('layouts.partial.layouts')
@section('page-title', 'Detail Neraca Awal | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'Detail Neraca Awal')
@section('section-row')
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('laporan-keuangan.neraca-awal.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <h5 class="mb-0 fw-bold text-uppercase">
            NERACA AWAL {{ strtoupper(Auth::user()->name) }}
        </h5>
        <div>
            <span class="badge bg-primary">Ref: {{ $entry->reference_number }}</span>
        </div>
    </div>
    
    <p class="text-center text-muted">
        PER TANGGAL: {{ $entry->date->format('d/m/Y') }}
    </p>

    <div class="row">
        {{-- AKTIVA --}}
        <div class="col-md-6">
            <h6 class="fw-bold text-primary">AKTIVA (DEBIT)</h6>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Akun</th>
                        <th class="text-end">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalDebit = 0; @endphp
                    @foreach($entry->items->where('debit', '>', 0) as $item)
                        <tr>
                            <td>
                                {{ $item->account->name }}
                                @if($item->subLedger)
                                    <br><small class="text-muted ps-3">— {{ $item->subLedger->nama }}</small>
                                @endif
                            </td>
                            <td class="text-end">
                                Rp {{ number_format($item->debit, 0, ',', '.') }}
                            </td>
                        </tr>
                        @php $totalDebit += $item->debit; @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-secondary fw-bold">
                        <td>Total Aktiva</td>
                        <td class="text-end">
                            Rp {{ number_format($totalDebit, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- PASIVA --}}
        <div class="col-md-6">
            <h6 class="fw-bold text-primary">PASIVA (KREDIT)</h6>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Akun</th>
                        <th class="text-end">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalCredit = 0; @endphp
                    @foreach($entry->items->where('credit', '>', 0) as $item)
                        <tr>
                            <td>
                                {{ $item->account->name }}
                                @if($item->subLedger)
                                    <br><small class="text-muted ps-3">— {{ $item->subLedger->nama }}</small>
                                @endif
                            </td>
                            <td class="text-end">
                                Rp {{ number_format($item->credit, 0, ',', '.') }}
                            </td>
                        </tr>
                        @php $totalCredit += $item->credit; @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-secondary fw-bold">
                        <td>Total Pasiva</td>
                        <td class="text-end">
                            Rp {{ number_format($totalCredit, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Status Balance --}}
    <div class="mt-4 text-center">
        @if (number_format($totalDebit, 2) == number_format($totalCredit, 2))
            <div class="alert alert-success fw-bold">
                ✅ Neraca Awal seimbang — Total Aktiva dan Pasiva bernilai sama (Rp {{ number_format($totalDebit, 0, ',', '.') }}).
            </div>
        @else
            <div class="alert alert-danger fw-bold">
                ⚠️ Neraca Awal tidak seimbang!<br>
                Selisih sebesar <span class="text-decoration-underline">
                    Rp {{ number_format(abs($totalDebit - $totalCredit), 0, ',', '.') }}
                </span>.
            </div>
        @endif
    </div>
</div>
@endsection
