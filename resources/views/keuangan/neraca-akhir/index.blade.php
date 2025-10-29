@extends('layouts.partial.layouts')
@section('page-title', 'Neraca Akhir')

@section('section-heading', 'Neraca Akhir')
@section('section-row')
<div class="card p-4">
    <h5 class="mb-3 text-center">NERACA AKHIR</h5>

    <div class="mb-4">
        <h6 class="fw-bold">Laporan Laba/Rugi</h6>
        <table class="table table-bordered">
            <tr><td>Pendapatan</td><td class="text-end">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td></tr>
            <tr><td>Pengeluaran</td><td class="text-end">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td></tr>
            <tr><td>Laba Kotor</td><td class="text-end">Rp {{ number_format($labaKotor, 0, ',', '.') }}</td></tr>
            <tr><td>Pajak (15%)</td><td class="text-end">Rp {{ number_format($pajak, 0, ',', '.') }}</td></tr>
            <tr class="table-secondary fw-bold">
                <td>Laba Bersih</td><td class="text-end">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="row">
        <div class="col-md-6">
            <h6 class="fw-bold">AKTIVA</h6>
            <table class="table table-bordered">
                <tr><td>Kas</td><td class="text-end">Rp {{ number_format($saldoKas, 0, ',', '.') }}</td></tr>
                <tr><td>Piutang</td><td class="text-end">Rp {{ number_format($saldoPiutang, 0, ',', '.') }}</td></tr>
                <tr><td>Persediaan Barang</td><td class="text-end">Rp {{ number_format($nilaiPersediaan, 0, ',', '.') }}</td></tr>
                <tr class="table-secondary fw-bold">
                    <td>Total Aktiva</td><td class="text-end">Rp {{ number_format($totalAktiva, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6 class="fw-bold">PASIVA</h6>
            <table class="table table-bordered">
                <tr><td>Hutang</td><td class="text-end">Rp {{ number_format($saldoHutang, 0, ',', '.') }}</td></tr>
                <tr><td>Laba</td><td class="text-end">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td></tr>
                <tr class="table-secondary fw-bold">
                    <td>Total Pasiva</td><td class="text-end">Rp {{ number_format($totalPasiva, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection
