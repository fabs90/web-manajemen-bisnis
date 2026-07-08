<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            word-wrap: break-word;
            vertical-align: top;
        }

        .table-no-border td {
            border: none !important;
            padding: 2px 0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .fw-bold {
            font-weight: bold;
        }

        .bg-light {
            background-color: #f2f2f2;
        }

        .line {
            border-bottom: 3px solid #000;
            margin: 10px 0 15px;
        }
    </style>
</head>

<body>

    {{-- KOP SURAT --}}
    <table class="table-no-border" style="margin-bottom: 10px;">
        <tr>
            <td width="15%">
                @if (isset($userProfile->logo_perusahaan) && $userProfile->logo_perusahaan)
                    @php
                        $logoPath = storage_path('app/public/' . $userProfile->logo_perusahaan);
                        if (file_exists($logoPath)) {
                            $logoBase64 = base64_encode(file_get_contents($logoPath));
                            $logoMime = mime_content_type($logoPath);
                        }
                    @endphp
                    @if (isset($logoBase64))
                        <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}" style="height:70px;">
                    @endif
                @endif
            </td>
            <td width="70%" class="text-center">
                <div style="font-size:16px; font-weight:bold; text-transform:uppercase;">
                    {{ $userProfile->name ?? config('app.name') }}</div>
                <div style="font-size:11px;">{{ $userProfile->alamat ?? '' }}</div>
                <div style="font-size:11px;">Telp: {{ $userProfile->nomor_telepon ?? '-' }} | Email:
                    {{ $userProfile->email ?? '-' }}</div>
            </td>
            <td width="15%"></td>
        </tr>
    </table>

    <div class="line"></div>

    {{-- HEADER TABEL --}}
    <table class="table-no-border" style="margin-top: 0; margin-bottom: 15px;">
        <tr>
            <td class="text-center fw-bold" style="font-size: 14px;">LAPORAN LABA RUGI</td>
        </tr>
        <tr>
            <td class="text-center" style="font-size: 12px;">Periode: {{ $startDate }} s/d {{ $endDate }}</td>
        </tr>
    </table>

    {{-- PENDAPATAN --}}
    <table>
        <tr class="bg-light">
            <td class="fw-bold" colspan="2">Pendapatan</td>
        </tr>
        <tr>
            <td class="text-left">Penjualan Kredit</td>
            <td class="text-right">Rp {{ number_format($penjualanKredit, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Penjualan Tunai</td>
            <td class="text-right">Rp {{ number_format($penjualanTunai, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Bunga Penjualan</td>
            <td class="text-right">Rp {{ number_format($bungaPenjualan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left fw-bold">Total Penjualan</td>
            <td class="text-right fw-bold">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Retur Penjualan</td>
            <td class="text-right">(Rp {{ number_format($returPenjualan, 0, ',', '.') }})</td>
        </tr>
        <tr>
            <td class="text-left">Potongan Penjualan</td>
            <td class="text-right">(Rp {{ number_format($potonganPenjualan, 0, ',', '.') }})</td>
        </tr>
        <tr class="bg-light">
            <td class="text-left fw-bold">Penjualan Bersih</td>
            <td class="text-right fw-bold">Rp {{ number_format($penjualanBersih, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- HPP --}}
    <table>
        <tr class="bg-light">
            <td class="fw-bold" colspan="2">Harga Pokok Penjualan</td>
        </tr>
        <tr>
            <td class="text-left">Persediaan Awal</td>
            <td class="text-right">Rp {{ number_format($persediaanAwal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Pembelian Bersih</td>
            <td class="text-right">Rp {{ number_format($pembelianBersih, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left fw-bold">Barang Tersedia Dijual</td>
            <td class="text-right fw-bold">Rp {{ number_format($persediaanAwal + $pembelianBersih, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Persediaan Akhir</td>
            <td class="text-right">Rp {{ number_format($persediaanAkhir, 0, ',', '.') }}</td>
        </tr>
        <tr class="bg-light">
            <td class="text-left fw-bold">HPP</td>
            <td class="text-right fw-bold">Rp {{ number_format($hpp, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- LABA KOTOR --}}
    <table>
        <tr class="bg-light">
            <td class="text-left fw-bold" width="50%">Laba Kotor</td>
            <td class="text-right fw-bold">Rp {{ number_format($labaKotor, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- BIAYA OPERASIONAL --}}
    <table>
        <tr class="bg-light">
            <td class="fw-bold" colspan="2">Biaya Operasional</td>
        </tr>
        <tr>
            <td class="text-left" width="50%">Total Biaya Operasional</td>
            <td class="text-right">Rp {{ number_format($biayaOperasional, 0, ',', '.') }}</td>
        </tr>
        <tr class="bg-light">
            <td class="text-left fw-bold">Laba Operasional</td>
            <td class="text-right fw-bold">Rp {{ number_format($labaOperasional, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- LAIN-LAIN --}}
    <table>
        <tr class="bg-light">
            <td class="fw-bold" colspan="2">Pendapatan & Biaya Lain-lain</td>
        </tr>
        <tr>
            <td class="text-left" width="50%">Pendapatan Lain</td>
            <td class="text-right">Rp {{ number_format($pendapatanLain, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Biaya Administrasi & Bank</td>
            <td class="text-right">Rp {{ number_format($biayaAdministrasiBank, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left fw-bold">Laba Sebelum Pajak</td>
            <td class="text-right fw-bold">Rp {{ number_format($labaSebelumPajak, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Pajak (15%)</td>
            <td class="text-right">Rp {{ number_format($pajak, 0, ',', '.') }}</td>
        </tr>
        <tr class="bg-light">
            <td class="text-left fw-bold">Laba Setelah Pajak</td>
            <td class="text-right fw-bold">Rp {{ number_format($labaSetelahPajak, 0, ',', '.') }}</td>
        </tr>
    </table>

</body>

</html>
