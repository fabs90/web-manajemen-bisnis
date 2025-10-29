<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rugi Laba</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        h2, h3 {
            text-align: center;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #444;
            padding: 6px 10px;
            text-align: right;
        }

        th {
            background-color: #f0f0f0;
        }

        td.text-left {
            text-align: left;
        }

        .section-title {
            background-color: #ddd;
            font-weight: bold;
            text-align: left;
        }

        .total {
            font-weight: bold;
            border-top: 2px solid #000;
        }
    </style>
</head>
<body>
    <h2>Laporan Rugi Laba</h2>
    <h3>Periode: {{ $startDate }} s/d {{ $endDate }}</h3>

    <table>
        <tr class="section-title">
            <td class="text-left" colspan="2">Pendapatan</td>
        </tr>
        <tr>
            <td class="text-left">Penjualan Kredit</td>
            <td>{{ number_format($penjualanKredit, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Penjualan Tunai</td>
            <td>{{ number_format($penjualanTunai, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Bunga Penjualan</td>
            <td>{{ number_format($bungaPenjualan, 0, ',', '.') }}</td>
        </tr>
        <tr class="total">
            <td class="text-left">Total Penjualan</td>
            <td>{{ number_format($totalPenjualan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Retur Penjualan</td>
            <td>{{ number_format($returPenjualan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Potongan Penjualan</td>
            <td>{{ number_format($potonganPenjualan, 0, ',', '.') }}</td>
        </tr>
        <tr class="total">
            <td class="text-left">Penjualan Bersih</td>
            <td>{{ number_format($penjualanBersih, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table>
        <tr class="section-title">
            <td class="text-left" colspan="2">Harga Pokok Penjualan</td>
        </tr>
        <tr>
            <td class="text-left">Persediaan Awal</td>
            <td>{{ number_format($persediaanBarangDaganganAwal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Pembelian Bersih</td>
            <td>{{ number_format($pembelianBersih, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Barang Tersedia Dijual</td>
            <td>{{ number_format($barangTersediaDijual, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Persediaan Akhir</td>
            <td>{{ number_format($persediaanBarangDagangan, 0, ',', '.') }}</td>
        </tr>
        <tr class="total">
            <td class="text-left">HPP</td>
            <td>{{ number_format($hpp, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table>
        <tr class="section-title">
            <td class="text-left" colspan="2">Laba Kotor</td>
        </tr>
        <tr class="total">
            <td class="text-left">Laba Kotor</td>
            <td>{{ number_format($labaKotor, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table>
        <tr class="section-title">
            <td class="text-left" colspan="2">Biaya Operasional</td>
        </tr>
        <tr>
            <td class="text-left">Biaya Operasional</td>
            <td>{{ number_format($biayaOperasional, 0, ',', '.') }}</td>
        </tr>
        <tr class="total">
            <td class="text-left">Laba Operasional</td>
            <td>{{ number_format($labaOperasional, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table>
        <tr class="section-title">
            <td class="text-left" colspan="2">Pendapatan & Biaya Lain-lain</td>
        </tr>
        <tr>
            <td class="text-left">Pendapatan Lain</td>
            <td>{{ number_format($pendapatanLain, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Biaya Administrasi & Bank</td>
            <td>{{ number_format($biayaAdministrasiBank, 0, ',', '.') }}</td>
        </tr>
        <tr class="total">
            <td class="text-left">Laba Sebelum Pajak</td>
            <td>{{ number_format($labaSebelumPajak, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Pajak (15%)</td>
            <td>{{ number_format($pajak, 0, ',', '.') }}</td>
        </tr>
        <tr class="total">
            <td class="text-left">Laba Setelah Pajak</td>
            <td>{{ number_format($labaSetelahPajak, 0, ',', '.') }}</td>
        </tr>
    </table>
</body>
</html>
