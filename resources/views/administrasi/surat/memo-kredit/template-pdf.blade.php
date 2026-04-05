<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Memo Kredit - {{ $memo->nomor_memo }}</title>
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

        .uppercase {
            text-transform: uppercase;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 6px;
        }

        .table-no-border {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-no-border td {
            border: none;
            padding: 2px 0;
            vertical-align: top;
        }

        .mb-3 {
            margin-bottom: 15px;
        }

        .mb-4 {
            margin-bottom: 25px;
        }

        .line {
            border-bottom: 3px solid #000;
            margin: 10px 0 15px;
        }
    </style>
</head>

<body>

    {{-- KOP SURAT --}}
    <table class="table-no-border">
        <tr>
            <td width="15%">
                @if (isset($profileUser->logo_perusahaan) && $profileUser->logo_perusahaan)
                    @php
                        $logoPath = public_path('storage/' . $profileUser->logo_perusahaan);
                        $logoBase64 = base64_encode(file_get_contents($logoPath));
                        $logoMime = mime_content_type($logoPath);
                    @endphp
                    <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}" style="height:70px;">
                @endif
            </td>
            <td width="70%" class="text-center">
                <div style="font-size:16px; font-weight:bold;" class="uppercase">
                    {{ $profileUser->name ?? 'Nama Perusahaan' }}
                </div>
                <div style="font-size:11px;">
                    {{ $profileUser->alamat ?? 'Alamat Perusahaan' }}
                </div>
                <div style="font-size:11px;">
                    Telp: {{ $profileUser->nomor_telepon ?? '-' }} |
                    Email: {{ $profileUser->email ?? '-' }}
                </div>
            </td>
            <td width="15%"></td>
        </tr>
    </table>

    <div class="line"></div>

    {{-- Judul --}}
    <h3 class="text-center fw-bold uppercase mb-3">MEMO KREDIT</h3>

    @php
        $po = $faktur->suratPengirimanBarang?->pesananPembelian;
        $isMasuk = $po?->jenis == 'transaksi_masuk';
        $pihak = $isMasuk ? $po?->pelanggan : $po?->supplier;
    @endphp

    {{-- Info Header --}}
    <table class="table-no-border mb-4">
        <tr>
            <td width="55%">
                Kepada Yth.<br>
                <strong>{{ $pihak?->nama ?? '-' }}</strong><br>
                {{ $pihak?->alamat ?? '-' }}
            </td>
            <td width="45%">
                <strong>Nomor Memo</strong> :
                {{ $memo->nomor_memo ?? '-' }}<br>

                <strong>Tanggal Memo</strong> :
                {{ \Carbon\Carbon::parse($memo->tanggal)->format('d/m/Y') }}<br>

                <strong>Nomor Faktur</strong> :
                {{ $faktur->kode_faktur ?? '-' }}<br>

                <strong>Tanggal Faktur</strong> :
                {{ \Carbon\Carbon::parse($faktur->tanggal_faktur)->format('d/m/Y') }}<br>

                <strong>No. PO</strong> :
                {{ $po?->nomor_pesanan_pembelian ?? '-' }}
            </td>
        </tr>
    </table>

    <p class="mb-3">
        Bersama ini kami sampaikan rincian barang yang dikembalikan sebagai berikut:
    </p>

    {{-- Tabel Barang --}}
    <table class="table">
        <thead>
            <tr class="text-center">
                <th width="5%">No</th>
                <th width="45%">Nama Barang</th>
                <th width="10%">Qty</th>
                <th width="20%">Harga Satuan</th>
                <th width="20%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($memo->memoKreditDetail as $detail)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $detail->nama_barang }}</td>
                    <td class="text-center">{{ $detail->kuantitas }}</td>
                    <td class="text-right">
                        Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                    </td>
                    <td class="text-right">
                        Rp {{ number_format($detail->jumlah, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach

            {{-- spacer --}}
            @for ($i = count($memo->memoKreditDetail) + 1; $i <= 8; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor

            <tr>
                <td colspan="4" class="text-right fw-bold">TOTAL</td>
                <td class="text-right fw-bold">
                    Rp {{ number_format($memo->total, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Alasan --}}
    <table class="table-no-border mb-4">
        <tr>
            <td width="25%" class="fw-bold">Alasan Pengembalian:</td>
            <td width="75%"> {{ $memo->alasan_pengembalian ?? '-' }}</td>
        </tr>
    </table>

    {{-- Tanda Tangan --}}
    <table width="100%" class="table-no-border" style="margin-top:60px;">
        <tr>
            <td width="50%" class="text-center">
            </td>
            <td width="50%" class="text-center">
                {{ \Carbon\Carbon::parse($memo->tanggal)->format('d/m/Y') }}<br>
                Bagian Penjualan<br><br><br><br><br>
                <strong>( {{ $faktur->nama_bagian_penjualan ?? '_________' }} )</strong>
            </td>
        </tr>
    </table>

</body>

</html>
