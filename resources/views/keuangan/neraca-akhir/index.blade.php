@extends('layouts.partial.layouts')
@section('page-title', 'Neraca Akhir')

@section('section-heading', 'Neraca Akhir')
@section('section-row')
<div class="card p-4 shadow-sm">
    <h5 class="mb-3 text-center fw-bold text-uppercase">NERACA AKHIR</h5>
    <div class="row">
        <div class="col-md-6">
            <h6 class="fw-bold text-primary">AKTIVA</h6>
            <table class="table table-bordered">
                <tr><td colspan="2"><b>Aktiva Lancar</b></td></tr>
                <tr><td>Kas</td><td class="text-end">Rp {{ number_format($saldoKas, 0, ',', '.') }}</td></tr>
                <tr><td>Piutang</td><td class="text-end">Rp {{ number_format($saldoPiutang, 0, ',', '.') }}</td></tr>
                <tr><td>Persediaan Barang</td><td class="text-end">Rp {{ number_format($nilaiPersediaan, 0, ',', '.') }}</td></tr>

                <tr><td colspan="2"><b>Aktiva Tetap</b></td></tr>
                <tr><td>Tanah</td><td class="text-end">Rp {{ number_format($tanah, 0, ',', '.') }}</td></tr>
                <tr><td>Kendaraan</td><td class="text-end">Rp {{ number_format($kendaraan, 0, ',', '.') }}</td></tr>
                <tr><td>Peralatan</td><td class="text-end">Rp {{ number_format($peralatan, 0, ',', '.') }}</td></tr>

                <tr class="table-secondary fw-bold">
                    <td>Total Aktiva</td>
                    <td class="text-end">Rp {{ number_format($totalAktiva, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="col-md-6">
            <h6 class="fw-bold text-primary">PASIVA</h6>
            <table class="table table-bordered">
                <tr><td>Hutang</td><td class="text-end">Rp {{ number_format($saldoHutang, 0, ',', '.') }}</td></tr>
                <tr><td>Pajak</td><td class="text-end">Rp {{ number_format($pajak, 0, ',', '.') }}</td></tr>
                <tr><td>Laba</td><td class="text-end">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td></tr>
                <tr><td>Modal</td><td class="text-end">Rp {{ number_format($modal, 0, ',', '.') }}</td></tr>

                <tr class="table-secondary fw-bold">
                    <td>Total Pasiva</td>
                    <td class="text-end">Rp {{ number_format($totalPasiva, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Status Balance --}}
    <div class="mt-4 text-center">
        @if ($totalAktiva == $totalPasiva)
            <div class="alert alert-success fw-bold">
                ✅ Neraca seimbang — Total Aktiva dan Pasiva bernilai sama (Rp {{ number_format($totalAktiva, 0, ',', '.') }}).
            </div>
        @else
            <div class="alert alert-danger fw-bold">
                ⚠️ Neraca tidak seimbang!<br>
                Selisih sebesar <span class="text-decoration-underline">
                    Rp {{ number_format(abs($totalAktiva - $totalPasiva), 0, ',', '.') }}
                </span>.
            </div>
        @endif
    </div>
</div>
@endsection
