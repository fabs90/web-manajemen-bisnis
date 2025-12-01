<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        h3, h4 { text-align: center; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        td { padding: 6px 8px; }
        td.text-left { text-align: left; }
        td.text-right { text-align: right; }
        .section-title { background: #dfe6e9; font-weight: bold; }
        .total td { border-top: 1px solid #000; font-weight: bold; }
    </style>
</head>
<body>
    <!-- KOP SURAT -->
    <div style="text-align:center; margin-bottom:10px;">
        @if(isset($userProfile->logo_perusahaan) && $userProfile->logo_perusahaan)
            <img src="{{ public_path('storage/' . $userProfile->logo) }}"
                 alt="Logo"
                 style="height:60px; margin-bottom:5px;">
        @endif

        <div style="font-size:15px; font-weight:bold; text-transform:uppercase;">
            {{ $userProfile->name ?? 'Nama Perusahaan' }}
        </div>

        <div style="font-size:11px; margin-top:2px;">
            {{ $userProfile->alamat ?? 'Alamat Perusahaan' }}
        </div>

        <div style="font-size:11px;">
            Telp: {{ $userProfile->nomor_telepon ?? '-' }} •
            Email: {{ $userProfile->email ?? '-' }}
        </div>
    </div>

    <hr style="border:0; border-top:2px solid #000; margin:8px 0;">
    <hr style="border:0; border-top:1px solid #000; margin:2px 0 15px 0;">

    <h3><strong>LAPORAN LABA RUGI</strong></h3>
    <h4>Periode: {{ $startDate }} s/d {{ $endDate }}</h4>
    <br>

    {{-- PENDAPATAN --}}
    <table>
        <tr class="section-title"><td class="text-left" colspan="2">Pendapatan</td></tr>
        <tr><td class="text-left">Penjualan Kredit</td><td class="text-right">Rp {{ number_format($penjualanKredit) }}</td></tr>
        <tr><td class="text-left">Penjualan Tunai</td><td class="text-right">Rp {{ number_format($penjualanTunai) }}</td></tr>
        <tr><td class="text-left">Bunga Penjualan</td><td class="text-right">Rp {{ number_format($bungaPenjualan) }}</td></tr>
        <tr class="total"><td class="text-left">Total Penjualan</td><td class="text-right">Rp {{ number_format($totalPenjualan) }}</td></tr>

        <tr><td class="text-left">Retur Penjualan</td><td class="text-right">(Rp {{ number_format($returPenjualan) }})</td></tr>
        <tr><td class="text-left">Potongan Penjualan</td><td class="text-right">(Rp {{ number_format($potonganPenjualan) }})</td></tr>

        <tr class="total"><td class="text-left">Penjualan Bersih</td><td class="text-right">Rp {{ number_format($penjualanBersih) }}</td></tr>
    </table>

    {{-- HPP --}}
    <table>
        <tr class="section-title"><td class="text-left" colspan="2">Harga Pokok Penjualan</td></tr>

        <tr><td class="text-left">Persediaan Awal</td><td class="text-right">Rp {{ number_format($persediaanAwal) }}</td></tr>
        <tr><td class="text-left">Pembelian Bersih</td><td class="text-right">Rp {{ number_format($pembelianBersih) }}</td></tr>

        <tr class="total"><td class="text-left">Barang Tersedia Dijual</td><td class="text-right">Rp {{ number_format($persediaanAwal + $pembelianBersih) }}</td></tr>

        <tr><td class="text-left">Persediaan Akhir</td><td class="text-right">Rp {{ number_format($persediaanAkhir) }}</td></tr>

        <tr class="total"><td class="text-left">HPP</td><td class="text-right">Rp {{ number_format($hpp) }}</td></tr>
    </table>

    {{-- LABA KOTOR --}}
    <table>
        <tr class="section-title"><td class="text-left" colspan="2">Laba Kotor</td></tr>
        <tr class="total"><td class="text-left">Laba Kotor</td><td class="text-right">Rp {{ number_format($labaKotor) }}</td></tr>
    </table>

    {{-- BIAYA OPERASIONAL --}}
    <table>
        <tr class="section-title"><td class="text-left" colspan="2">Biaya Operasional</td></tr>
        <tr><td class="text-left">Total Biaya Operasional</td><td class="text-right">Rp {{ number_format($biayaOperasional) }}</td></tr>
        <tr class="total"><td class="text-left">Laba Operasional</td><td class="text-right">Rp {{ number_format($labaOperasional) }}</td></tr>
    </table>

    {{-- LAIN-LAIN --}}
    <table>
        <tr class="section-title"><td class="text-left" colspan="2">Pendapatan & Biaya Lain-lain</td></tr>
        <tr><td class="text-left">Pendapatan Lain</td><td class="text-right">Rp {{ number_format($pendapatanLain) }}</td></tr>
        <tr><td class="text-left">Biaya Administrasi & Bank</td><td class="text-right">Rp {{ number_format($biayaAdministrasiBank) }}</td></tr>

        <tr class="total"><td class="text-left">Laba Sebelum Pajak</td><td class="text-right">Rp {{ number_format($labaSebelumPajak) }}</td></tr>
        <tr><td class="text-left">Pajak (15%)</td><td class="text-right">Rp {{ number_format($pajak) }}</td></tr>
        <tr class="total"><td class="text-left">Laba Setelah Pajak</td><td class="text-right">Rp {{ number_format($labaSetelahPajak) }}</td></tr>
    </table>

</body>
</html>
