<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        .fw-bold {
            font-weight: bold;
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

        .text-underline {
            text-decoration: underline;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .mb-3 {
            margin-bottom: 15px;
        }

        .mb-4 {
            margin-bottom: 25px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
            border: 1px solid #000;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 6px;
            word-wrap: break-word;
            vertical-align: middle;
        }

        .table thead {
            background-color: #f0f0f0;
        }

        .table-no-border {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-no-border td,
        .table-no-border th {
            border: none !important;
            padding: 2px 0;
            vertical-align: middle;
        }

        .bg-light {
            background-color: #f2f2f2;
        }

        .indent {
            padding-left: 18px !important;
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
                @php
                    $logoBase64 = image_to_base64($userProfile->logo_perusahaan ?? null);
                @endphp
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" style="height:70px;">
                @endif
            </td>
            <td width="70%" class="text-center">
                <div style="font-size:16px; font-weight:bold;" class="uppercase">
                    {{ $userProfile->name ?? config('app.name') }}
                </div>
                <div style="font-size:11px;">{{ $userProfile->alamat ?? '' }}</div>
                <div style="font-size:11px;">Telp: {{ $userProfile->nomor_telepon ?? '-' }} | Email:
                    {{ $userProfile->email ?? '-' }}</div>
            </td>
            <td width="15%"></td>
        </tr>
    </table>

    <div class="line"></div>

    {{-- JUDUL --}}
    <h3 class="text-center fw-bold uppercase" style="margin-bottom: 4px;">LAPORAN LABA RUGI</h3>
    <div class="text-center mb-3" style="font-size: 11px;">Periode: {{ $startDate }} s/d {{ $endDate }}</div>

    {{-- LAPORAN LABA RUGI - 5 KOLOM --}}
    <table class="table">
        <thead>
            <tr>
                <th class="text-center" width="5%">NO</th>
                <th class="text-left" width="35%">URAIAN</th>
                <th class="text-center" colspan="2" width="40%">JUMLAH</th>
                <th class="text-center" width="20%">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            {{-- 1. Penjualan --}}
            <tr>
                <td class="text-center">1</td>
                <td class="fw-bold">Penjualan</td>
                <td></td>
                <td class="text-right">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td class="indent">Retur Penjualan</td>
                <td></td>
                <td class="text-right">Rp {{ number_format($returPenjualan, 0, ',', '.') }} -</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td class="indent">Potongan Penjualan</td>
                <td></td>
                <td class="text-right text-underline">Rp {{ number_format($potonganPenjualan, 0, ',', '.') }} -</td>
                <td></td>
            </tr>
            <tr class="bg-light">
                <td class="text-center">4</td>
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

            {{-- 5. Persediaan Awal --}}
            <tr>
                <td class="text-center">5</td>
                <td class="fw-bold">
                    {{ ($userProfile->role ?? auth()->user()?->role) === 'nelayan' ? 'Persediaan Hasil Tangkapan Awal' : 'Persediaan Barang Dagang Awal' }}
                </td>
                <td></td>
                <td class="text-right">Rp {{ number_format($persediaanAwal, 0, ',', '.') }}</td>
                <td></td>
            </tr>

            {{-- 6-8. Rincian Pembelian --}}
            <tr>
                <td class="text-center">6</td>
                <td class="indent">Pembelian secara kredit dan tunai</td>
                <td class="text-right">Rp {{ number_format($pembelianKredit + $pembelianTunai, 0, ',', '.') }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">7</td>
                <td class="indent">Retur Pembelian</td>
                <td class="text-right">Rp {{ number_format($returPembelian, 0, ',', '.') }} -</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">8</td>
                <td class="indent">Potongan Pembelian</td>
                <td class="text-right text-underline">Rp {{ number_format($potonganPembelian, 0, ',', '.') }} -</td>
                <td></td>
                <td></td>
            </tr>

            {{-- 9. Pembelian Bersih --}}
            <tr class="bg-light">
                <td class="text-center">9</td>
                <td class="fw-bold">Pembelian Bersih</td>
                <td></td>
                <td class="text-right fw-bold text-underline">Rp {{ number_format($pembelianBersih, 0, ',', '.') }} +
                </td>
                <td></td>
            </tr>

            {{-- 10-11 --}}
            <tr>
                <td class="text-center">10</td>
                <td>Barang yang Tersedia untuk Dijual</td>
                <td></td>
                <td class="text-right">Rp {{ number_format($persediaanAwal + $pembelianBersih, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">11</td>
                <td>
                    {{ ($userProfile->role ?? auth()->user()?->role) === 'nelayan' ? 'Persediaan Hasil Tangkapan Akhir' : 'Persediaan Barang Dagang Akhir' }}
                </td>
                <td></td>
                <td class="text-right text-underline">Rp {{ number_format($persediaanAkhir, 0, ',', '.') }} -</td>
                <td></td>
            </tr>

            {{-- 12. HPP --}}
            <tr class="bg-light">
                <td class="text-center">12</td>
                <td class="fw-bold">HPP (Harga Pokok Penjualan)</td>
                <td></td>
                <td></td>
                <td class="text-right fw-bold text-underline">Rp {{ number_format($hpp, 0, ',', '.') }} -</td>
            </tr>

            {{-- 13. Laba Kotor --}}
            <tr class="bg-light">
                <td class="text-center">13</td>
                <td class="text-center fw-bold">{{ $labaKotor >= 0 ? 'Laba Kotor' : 'Rugi Kotor' }}</td>
                <td></td>
                <td></td>
                <td class="text-right fw-bold">Rp {{ number_format($labaKotor, 0, ',', '.') }}</td>
            </tr>

            {{-- 14. Pendapatan Lain-lain --}}
            <tr>
                <td class="text-center">14</td>
                <td>Pendapatan Lain-lain</td>
                <td></td>
                <td class="text-right">Rp {{ number_format($pendapatanLain, 0, ',', '.') }}</td>
                <td></td>
            </tr>

            {{-- 15. Biaya Operasional --}}
            <tr>
                <td class="text-center">15</td>
                <td>Biaya Operasional</td>
                <td></td>
                <td class="text-right">Rp {{ number_format($biayaOperasional, 0, ',', '.') }} -</td>
                <td></td>
            </tr>

            {{-- 16. Biaya Administrasi Bank --}}
            <tr>
                <td class="text-center">16</td>
                <td>Biaya Administrasi Bank</td>
                <td></td>
                <td class="text-right text-underline">Rp {{ number_format($biayaAdministrasiBank, 0, ',', '.') }} -</td>
                <td></td>
            </tr>

            {{-- 17. Total Pendapatan dan Biaya Lain-lain --}}
            <tr class="bg-light">
                <td class="text-center">17</td>
                <td class="fw-bold">Total Pendapatan dan Biaya Lain-lain</td>
                <td></td>
                <td></td>
                <td class="text-right fw-bold text-underline">Rp
                    {{ number_format(abs($totalPendapatanBiayaLain), 0, ',', '.') }}
                    {{ $totalPendapatanBiayaLain >= 0 ? '+' : '-' }}</td>
            </tr>

            {{-- 18. Laba Sebelum Pajak --}}
            <tr class="bg-light">
                <td class="text-center">18</td>
                <td class="text-center fw-bold">Laba Sebelum Pajak</td>
                <td></td>
                <td></td>
                <td class="text-right fw-bold">Rp {{ number_format($labaSebelumPajak, 0, ',', '.') }}</td>
            </tr>

            {{-- 19. Pajak --}}
            @php
                $userRole = $userProfile?->role ?? auth()->user()?->role ?? 'ukm';
                $isUmkmOrNelayan = in_array($userRole, ['ukm', 'nelayan'], true);
                $pajakLabel = $isUmkmOrNelayan
                    ? ($pajak > 0
                        ? 'Pajak (0,5%)'
                        : 'Pajak (0% - Bebas Pajak / PTKP)')
                    : 'Pajak (15%)';
            @endphp
            <tr>
                <td class="text-center">19</td>
                <td>
                    {{ $pajakLabel }}
                    @if ($isUmkmOrNelayan)
                        <br><span style="font-size: 10px; color: #6c757d;">Keterangan: Omzet &le; Rp500.000.000 tidak
                            dikenakan pajak (0% / bebas pajak), omzet &gt; Rp500.000.000 dikenakan pajak 0,5%.</span>
                    @endif
                </td>
                <td></td>
                <td></td>
                <td class="text-right text-underline">Rp {{ number_format($pajak, 0, ',', '.') }} -</td>
            </tr>

            {{-- 20. Laba Bersih --}}
            <tr class="bg-light">
                <td class="text-center">20</td>
                <td class="text-center fw-bold">Laba Bersih</td>
                <td></td>
                <td></td>
                <td class="text-right fw-bold text-underline">Rp {{ number_format($labaSetelahPajak, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

</body>

</html>
