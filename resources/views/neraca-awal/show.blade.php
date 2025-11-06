@extends('layouts.partial.layouts')
@section('page-title')
Neraca Awal | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis
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

            {{-- Kas Masuk (Debit) --}}
            <div class="mb-2">
                <strong>Kas Masuk:</strong><br>
                @php $totalDebit = 0; @endphp
                @forelse ($bukuBesarKas->where('debit', '>', 0) as $kasMasuk)
                    {{ $kasMasuk->uraian }} :
                    <span class="text-success">
                        Rp {{ number_format($kasMasuk->debit, 0, ',', '.') }}
                    </span><br>
                    @php $totalDebit += $kasMasuk->debit; @endphp
                @empty
                    <i>Tidak ada kas masuk</i><br>
                @endforelse
                <hr style="width:50%; border: 0.5px solid #555; margin:5px 0;">
                <b>Total Kas Masuk:</b> Rp {{ number_format($totalDebit, 0, ',', '.') }}
            </div>

            {{-- Kas Keluar (Kredit) --}}
            <div>
                <strong>Kas Keluar:</strong><br>
                @php $totalKredit = 0; @endphp
                @forelse ($bukuBesarKas->where('kredit', '>', 0) as $kasKeluar)
                    {{ $kasKeluar->uraian }} :
                    <span class="text-danger">
                        Rp {{ number_format($kasKeluar->kredit, 0, ',', '.') }}
                    </span><br>
                    @php $totalKredit += $kasKeluar->kredit; @endphp
                @empty
                    <i>Tidak ada kas keluar</i><br>
                @endforelse
                <hr style="width:50%; border: 0.5px solid #555; margin:5px 0;">
                <b>Total Kas Keluar:</b> Rp {{ number_format($totalKredit, 0, ',', '.') }}
            </div>

            {{-- Selisih Kas --}}
            <div class="mt-2">
                <b>Sisa Kas (Debit - Kredit):</b>
                Rp {{ number_format($totalDebit - $totalKredit, 0, ',', '.') }}
            </div>

        </td>
        <td>
            <b>Hutang Dagang</b><br>
            @forelse ($bukuBesarHutang as $hutang)
                {{ $hutang->pelanggan->nama ?? 'Tanpa Nama' }} :
                Rp {{ number_format($hutang->saldo, 0, ',', '.') }}<br>
            @empty
                <i>Tidak ada hutang</i>
            @endforelse

        </td>
    </tr>

    <tr>
        <td>
            <b>Piutang Dagang</b><br>
            @forelse ($bukuBesarPiutang as $piutang)
                {{ $piutang->pelanggan->nama ?? 'Tanpa Nama' }} :
                Rp {{ number_format($piutang->saldo, 0, ',', '.') }}<br>
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
