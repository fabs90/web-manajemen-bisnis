<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPB - {{ $data->nomor_pengiriman_barang ?? $data->id }}</title>
    <style>
        body {
            font-family: "DejaVu Sans", "Arial", sans-serif;
            font-size: 11pt;
            color: #2d2d2d;
            margin: 0;
            padding: 30px 40px;
            background: #fff;
        }
        .container { max-width: 800px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; }
        .no-border { border: none !important; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 16px; }
        .mb-4 { margin-bottom: 24px; }
        .mt-4 { margin-top: 24px; }
        .bordered { border: 2px solid #1e3a8a; padding: 20px; border-radius: 8px; }
        .header-title {
            font-size: 20pt;
            color: #1e3a8a;
            letter-spacing: 2px;
            margin: 0;
        }
        .info-label { font-weight: bold; width: 180px; }
        .table-main th {
            background: #1e3a8a;
            color: white;
            padding: 12px 8px;
            font-weight: bold;
        }
        .table-main td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
        }
        .table-main tbody tr:last-child td { border-bottom: 2px solid #1e3a8a; }
        .total-row {
            background: #eef2ff;
            font-size: 13pt;
        }
        .signature-box {
            margin-top: 50px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 220px;
            margin: 60px auto 8px auto;
            padding-top: 5px;
        }
        .bg-header {
            background: linear-gradient(90deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 12px;
            border-radius: 6px 6px 0 0;
            margin: -40px -40px 30px -40px;
        }
    </style>
</head>
<body>
    <div class="container">

        <!-- Kop Surat -->
        <table class="text-center no-border mb-4">
            <tr>
                <td class="fw-bold" style="font-size: 22px; color: #1e3a8a;">
                    {{ strtoupper($profileUser->name ?? 'NAMA PERUSAHAAN') }}
                </td>
            </tr>
            <tr>
                <td style="font-size: 12pt;">
                    {{ $profileUser->alamat ?? '-' }}
                </td>
            </tr>
            <tr>
                <td style="font-size: 11pt; color: #555;">
                    {{ $profileUser->email ?? 'email@perusahaan.com' }} |
                    {{ $profileUser->nomor_telepon ?? '-' }}
                </td>
            </tr>
        </table>

        <!-- Header Biru -->
        <div class="bg-header text-center">
            <h1 class="header-title">SURAT PENGIRIMAN BARANG</h1>
        </div>

        <!-- Informasi SPB -->
        @php
            $nomorFormatted = str_pad($data->id, 3, '0', STR_PAD_LEFT);
            $bulan = date('m', strtotime($data->created_at ?? now()));
            $tahun = date('Y', strtotime($data->created_at ?? now()));
            $tanggalKirim = $data->tanggal_terima ? \Carbon\Carbon::parse($data->tanggal_terima)->format('d/m/Y') : '-';
        @endphp

        <table class="mb-4 no-border" style="font-size: 11pt;">
            <tr>
                <td class="info-label">Nomor SPB</td>
                <td>: <strong>{{ $nomorFormatted }}/SPB/{{ $profileUser->name }}/{{ $bulan }}/{{ $tahun }}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Tanggal</td>
                <td>: {{ \Carbon\Carbon::parse($data->created_at ?? now())->format('d F Y') }}</td>
            </tr>
            <tr>
                <td class="info-label">Kepada</td>
                <td>: {{ $data->pesananPembelian->pelanggan->nama ?? 'Nama Pelanggan' }}</td>
            </tr>
            <tr>
                <td class="info-label">Alamat</td>
                <td>: {{ $data->pesananPembelian->pelanggan->alamat ?? 'Alamat Pelanggan' }}</td>
            </tr>
            <tr>
                <td class="info-label">J. Melalui</td>
                <td>: {{ $data->jenis_pengiriman ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Barang diterima pada</td>
                <td>: {{ $tanggalKirim }}</td>
            </tr>
            <tr>
                <td class="info-label">Keadaan</td>
                <td>: {{ ucfirst($data->keadaan ?? 'baik') }}</td>
            </tr>
            <tr>
                <td class="info-label">Keterangan</td>
                <td>: {{ $data->keterangan ?? '-' }}</td>
            </tr>
        </table>

        <!-- Tabel Barang -->
        <table class="table-main" cellspacing="0">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Kuantitas</th>
                    <th width="40%">Nama Barang</th>
                    <th width="15%">Harga/Kemas</th>
                    <th width="20%">Rp.</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach($data->suratPengirimanBarangDetail as $i => $item)
                    @php
                        $detail = $item->pesananPembelianDetail;
                        $subtotal = $item->jumlah_dikirim * $detail->harga;
                        $total += $subtotal;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td class="text-center">{{ number_format($item->jumlah_dikirim) }}</td>
                        <td>{{ $detail->nama_barang }}</td>
                        <td class="text-right">Rp. {{ number_format($detail->harga, 0, ',', '.') }}</td>
                        <td class="text-right">Rp. {{ number_format($subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                <!-- Total -->
                <tr class="total-row fw-bold">
                    <td colspan="4" class="text-right" style="padding-right: 20px;">Total</td>
                    <td class="text-right" style="font-size: 14pt; color: #1e3a8a;">
                        Rp. {{ number_format($total, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Tanda Tangan -->
        <table width="100%" class="mt-4">
            <tr>
                <td width="50%" class="text-center">
                    <div class="signature-box">
                        <p class="mb-3">Yang Menerima,</p>
                        <div class="signature-line"></div>
                        <p><strong>( {{ $data->nama_penerima ?? '_________________' }} )</strong></p>
                    </div>
                </td>
                <td width="50%" class="text-center">
                    <div class="signature-box">
                        <p class="mb-3">Bagian Pengirim,</p>
                        <div class="signature-line"></div>
                        <p><strong>( {{ $data->nama_pengirim ?? '_________________' }} )</strong></p>
                    </div>
                </td>
            </tr>
        </table>

    </div>
</body>
</html>
