@extends('layouts.partial.layouts')

@section('page-title')
    Neraca Awal | {{ \Carbon\Carbon::parse($neracaAwal->tanggal ?? $neracaAwal->created_at)->format('d-m-Y') }}
@endsection

@section('section-heading')
    Detail Neraca Awal {{ $user->name }}
@endsection

@section('section-row')
<h4 class="text-start">
    PER {{ \Carbon\Carbon::parse($neracaAwal->tanggal ?? $neracaAwal->created_at)->format('d-m-Y') }}
</h4>

<table class="table">
    <tr>
        <th>Asset Lancar</th>
        <th>Hutang</th>
    </tr>
    <tr>
        <td>
            <b>Kas / Bank</b><br>
            @forelse ($bukuBesarKas as $kas)
                {{ $kas->uraian }} : Rp {{ number_format($kas->saldo, 0, ',', '.') }}<br>
            @empty
                <i>Tidak ada data kas</i>
            @endforelse
        </td>
        <td>
            <b>Hutang Dagang</b><br>
            @forelse ($bukuBesarHutang as $hutang)
                {{ $hutang->pelanggan->nama ?? 'Tanpa Nama' }} : Rp {{ number_format($hutang->saldo, 0, ',', '.') }}<br>
            @empty
                <i>Tidak ada hutang</i>
            @endforelse
        </td>
    </tr>

    <tr>
        <td>
            <b>Piutang Dagang</b><br>
            @forelse ($bukuBesarPiutang as $piutang)
                {{ $piutang->pelanggan->nama ?? 'Tanpa Nama' }} : Rp {{ number_format($piutang->saldo, 0, ',', '.') }}<br>
            @empty
                <i>Tidak ada piutang</i>
            @endforelse
        </td>
        <td></td>
    </tr>

    <tr>
        <td>
            <b>Persediaan Barang Dagangan</b><br>
            @forelse ($neracaAwal->barang as $b)
                @php
                    $kartu = $kartuGudang->firstWhere('barang_id', $b->id);
                    $saldo_perkemas = $kartu->saldo_perkemasan ?? 0;
                    $total = $b->harga_beli_per_kemas * $saldo_perkemas;
                @endphp
                <p>
                    {{ $b->nama }} :
                    Rp {{ number_format($b->harga_beli_per_kemas, 0, ',', '.') }}
                    × {{ $saldo_perkemas }}
                    = Rp {{ number_format($total, 0, ',', '.') }}
                </p>
            @empty
                <i>Tidak ada data barang</i>
            @endforelse

            <div class="d-flex">
                <hr width="50%" style="border: 0.5px solid #555; margin-right:5px;">
                <span>+</span>
            </div>
            Rp {{ number_format($neracaAwal->total_persediaan ?? 0, 0, ',', '.') }}
        </td>

        <td>
            <b>Modal</b><br>
            Rp {{ number_format($neracaAwal->modal_awal ?? 0, 0, ',', '.') }}
        </td>
    </tr>

    <tr class="highlight">
        <td colspan="2"><b>Asset Tetap</b></td>
    </tr>
    <tr>
        <td>
            Tanah & Bangunan : Rp {{ number_format($neracaAwal->tanah_bangunan ?? 0, 0, ',', '.') }}<br>
            Kendaraan : Rp {{ number_format($neracaAwal->kendaraan ?? 0, 0, ',', '.') }}<br>
            Meubel & Peralatan : Rp {{ number_format($neracaAwal->meubel_peralatan ?? 0, 0, ',', '.') }}
        </td>
        <td></td>
    </tr>

    <tr class="highlight">
        <td class="text-right">
            <div class="d-flex align-items-center mb-2">
                <hr style="width:50%; border: 0.5px solid #555; margin-right:5px;">
                <span>+</span>
            </div>
            <b>Rp {{ number_format($neracaAwal->total_debit ?? 0, 0, ',', '.') }}</b>
        </td>
        <td class="text-right">
            <div class="d-flex align-items-center mb-2">
                <hr style="width:50%; border: 0.5px solid #555; margin-right:5px;">
                <span>+</span>
            </div>
            <b>Rp {{ number_format($neracaAwal->total_kredit ?? 0, 0, ',', '.') }}</b>
        </td>
    </tr>
</table>
@endsection
