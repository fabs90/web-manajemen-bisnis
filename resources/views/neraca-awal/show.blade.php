@extends('layouts.partial.layouts')
@section('page-title', 'Neraca Awal | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'Neraca Awal')
@section('section-row')
<div class="card p-4 shadow-sm">
    <h5 class="mb-3 text-center fw-bold text-uppercase">
        NERACA AWAL {{ strtoupper($user->name) }}
    </h5>
    <p class="text-center text-muted">
        PER {{ \Carbon\Carbon::parse($neracaAwal->tanggal ?? $neracaAwal->created_at)->format('d-m-Y') }}
    </p>

    <div class="row">
        {{-- AKTIVA --}}
        <div class="col-md-6">
            <h6 class="fw-bold text-primary">AKTIVA</h6>
            <table class="table table-bordered">
                <tr><td colspan="2"><b>Aktiva Lancar</b></td></tr>
                <tr>
                    <td>Kas / Bank</td>
                    <td class="text-end">
                        Rp {{ number_format($neracaAwal->kas_awal ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td>Piutang Dagang</td>
                    <td class="text-end">
                        Rp {{ number_format($neracaAwal->total_piutang ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td>Persediaan Barang Dagangan</td>
                    <td class="text-end">
                        Rp {{ number_format($neracaAwal->total_persediaan ?? 0, 0, ',', '.') }}
                    </td>
                </tr>

                <tr><td colspan="2"><b>Aktiva Tetap</b></td></tr>
                <tr>
                    <td>Tanah & Bangunan</td>
                    <td class="text-end">
                        Rp {{ number_format($neracaAwal->tanah_bangunan ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td>Kendaraan</td>
                    <td class="text-end">
                        Rp {{ number_format($neracaAwal->kendaraan ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td>Meubel & Peralatan</td>
                    <td class="text-end">
                        Rp {{ number_format($neracaAwal->meubel_peralatan ?? 0, 0, ',', '.') }}
                    </td>
                </tr>

                <tr class="table-secondary fw-bold">
                    <td>Total Aktiva</td>
                    <td class="text-end">
                        Rp {{ number_format($neracaAwal->total_debit ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- PASIVA --}}
        <div class="col-md-6">
            <h6 class="fw-bold text-primary">PASIVA</h6>
            <table class="table table-bordered">
                <tr><td>Hutang Dagang</td>
                    <td class="text-end">
                        Rp {{ number_format($neracaAwal->total_hutang ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                <tr><td>Modal Awal</td>
                    <td class="text-end">
                        Rp {{ number_format($neracaAwal->modal_awal ?? 0, 0, ',', '.') }}
                    </td>
                </tr>

                <tr class="table-secondary fw-bold">
                    <td>Total Pasiva</td>
                    <td class="text-end">
                        Rp {{ number_format($neracaAwal->total_kredit ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Status Balance --}}
    <div class="mt-4 text-center">
        @if (($neracaAwal->total_debit ?? 0) == ($neracaAwal->total_kredit ?? 0))
            <div class="alert alert-success fw-bold">
                ✅ Neraca Awal seimbang — Total Aktiva dan Pasiva bernilai sama (Rp {{ number_format($neracaAwal->total_debit ?? 0, 0, ',', '.') }}).
            </div>
        @else
            <div class="alert alert-danger fw-bold">
                ⚠️ Neraca Awal tidak seimbang!<br>
                Selisih sebesar <span class="text-decoration-underline">
                    Rp {{ number_format(abs(($neracaAwal->total_debit ?? 0) - ($neracaAwal->total_kredit ?? 0)), 0, ',', '.') }}
                </span>.
            </div>
        @endif
    </div>
</div>
@endsection
