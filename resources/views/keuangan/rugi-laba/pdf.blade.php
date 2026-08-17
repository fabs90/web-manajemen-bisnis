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

        .bg-dark {
            background-color: #333;
            color: #fff;
        }

        .indent {
            padding-left: 18px;
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

    {{-- LAPORAN LABA RUGI - 5 KOLOM --}}
    <table>
        <thead>
            <tr class="bg-dark">
                <th class="text-center" width="5%">NO</th>
                <th width="35%">URAIAN</th>
                <th class="text-center" colspan=2 width="40%">JUMLAH</th>
                <th class="text-center" width="20%">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            {{-- Sub heading: Pendapatan --}}
            <tr class="bg-light">
                <td></td>
                <td class="fw-bold" colspan="4">Pendapatan</td>
            </tr>

            {{-- 1-3. Rincian Penjualan --}}
            <tr>
                <td class="text-center">1</td>
                <td class="indent">Penjualan Kredit</td>
                <td class="text-right">Rp {{ number_format($penjualanKredit, 0, ',', '.') }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td class="indent">Penjualan Tunai</td>
                <td class="text-right">Rp {{ number_format($penjualanTunai, 0, ',', '.') }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td class="indent">Bunga Penjualan</td>
                <td class="text-right">Rp {{ number_format($bungaPenjualan, 0, ',', '.') }}</td>
                <td></td>
                <td></td>
            </tr>

            {{-- 4. Total Penjualan (jumlah dari 1-3) --}}
            <tr class="bg-light">
                <td class="text-center">4</td>
                <td class="fw-bold">Total Penjualan</td>
                <td></td>
                <td class="text-right fw-bold">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</td>
                <td></td>
            </tr>

            {{-- 5-6. Retur & Potongan --}}
            <tr>
                <td class="text-center">5</td>
                <td>Retur Penjualan</td>
                <td></td>
                <td class="text-right">(Rp {{ number_format($returPenjualan, 0, ',', '.') }})</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">6</td>
                <td>Potongan Penjualan</td>
                <td></td>
                <td class="text-right">(Rp {{ number_format($potonganPenjualan, 0, ',', '.') }})</td>
                <td></td>
            </tr>

            {{-- 7. Penjualan Bersih --}}
            <tr class="bg-light">
                <td class="text-center">7</td>
                <td class="fw-bold">Penjualan Bersih</td>
                <td></td>
                <td></td>
                <td class="text-right fw-bold">Rp {{ number_format($penjualanBersih, 0, ',', '.') }}</td>
            </tr>

            {{-- Sub heading: HPP --}}
            <tr class="bg-light">
                <td></td>
                <td class="fw-bold" colspan="4">Harga Pokok Penjualan</td>
            </tr>

            {{-- 8-11 --}}
            <tr>
                <td class="text-center">8</td>
                <td>Persediaan Awal</td>
                <td></td>
                <td class="text-right">Rp {{ number_format($persediaanAwal, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">9</td>
                <td>Pembelian Bersih</td>
                <td></td>
                <td class="text-right">Rp {{ number_format($pembelianBersih, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            <tr class="bg-light">
                <td class="text-center">10</td>
                <td class="fw-bold">Barang Tersedia Dijual</td>
                <td></td>
                <td class="text-right fw-bold">Rp
                    {{ number_format($persediaanAwal + $pembelianBersih, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">11</td>
                <td>Persediaan Akhir</td>
                <td></td>
                <td class="text-right">Rp {{ number_format($persediaanAkhir, 0, ',', '.') }}</td>
                <td></td>
            </tr>

            {{-- 12. HPP --}}
            <tr class="bg-light">
                <td class="text-center">12</td>
                <td class="fw-bold">HPP</td>
                <td></td>
                <td></td>
                <td class="text-right fw-bold">Rp {{ number_format($hpp, 0, ',', '.') }}</td>
            </tr>

            {{-- 13. Laba Kotor --}}
            <tr class="bg-light">
                <td class="text-center">13</td>
                <td class="text-center fw-bold">{{ $labaKotor >= 0 ? 'Laba Kotor' : 'Rugi Kotor' }}</td>
                <td></td>
                <td></td>
                <td class="text-right fw-bold">Rp {{ number_format($labaKotor, 0, ',', '.') }}</td>
            </tr>

            {{-- Sub heading: Biaya Operasional --}}
            <tr class="bg-light">
                <td></td>
                <td class="fw-bold" colspan="4">Biaya Operasional</td>
            </tr>

            {{-- 14. Total Biaya Operasional --}}
            <tr>
                <td class="text-center">14</td>
                <td>Total Biaya Operasional</td>
                <td></td>
                <td class="text-right">Rp {{ number_format($biayaOperasional, 0, ',', '.') }}</td>
                <td></td>
            </tr>

            {{-- 15. Laba/Rugi Operasional --}}
            <tr class="bg-light">
                <td class="text-center">15</td>
                <td class="text-center fw-bold">
                    {{ $labaOperasional >= 0 ? 'Laba Operasional' : 'Rugi Operasional' }}
                </td>
                <td></td>
                <td></td>
                <td class="text-right fw-bold">Rp {{ number_format($labaOperasional, 0, ',', '.') }}</td>
            </tr>

            {{-- Sub heading: Lain-lain --}}
            <tr class="bg-light">
                <td></td>
                <td class="fw-bold" colspan="4">Pendapatan &amp; Biaya Lain-lain</td>
            </tr>

            {{-- 16-17 --}}
            <tr>
                <td class="text-center">16</td>
                <td>Pendapatan Lain</td>
                <td></td>
                <td class="text-right">Rp {{ number_format($pendapatanLain, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">17</td>
                <td>Biaya Administrasi &amp; Bank</td>
                <td></td>
                <td class="text-right">Rp {{ number_format($biayaAdministrasiBank, 0, ',', '.') }}</td>
                <td></td>
            </tr>

            {{-- 18. Total Pendapatan & Biaya Lain-lain --}}
            <tr class="bg-light">
                <td class="text-center">18</td>
                <td class="fw-bold">Total Pendapatan &amp; Biaya Lain-lain</td>
                <td></td>
                <td></td>
                <td class="text-right fw-bold">Rp {{ number_format($totalPendapatanBiayaLain, 0, ',', '.') }}</td>
            </tr>

            {{-- 19. Laba Sebelum Pajak --}}
            <tr class="bg-light">
                <td class="text-center">19</td>
                <td class="fw-bold">Laba Sebelum Pajak</td>
                <td></td>
                <td></td>
                <td class="text-right fw-bold">Rp {{ number_format($labaSebelumPajak, 0, ',', '.') }}</td>
            </tr>

            {{-- 20. Pajak --}}
            <tr>
                <td class="text-center">20</td>
                <td>Pajak (15%)</td>
                <td></td>
                <td></td>
                <td class="text-right">Rp {{ number_format($pajak, 0, ',', '.') }}</td>
            </tr>

            {{-- 21. Laba Setelah Pajak --}}
            <tr class="bg-light">
                <td class="text-center">21</td>
                <td class="text-center fw-bold">Laba Bersih</td>
                <td></td>
                <td></td>
                <td class="text-right fw-bold">Rp {{ number_format($labaSetelahPajak, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

</body>

</html>
