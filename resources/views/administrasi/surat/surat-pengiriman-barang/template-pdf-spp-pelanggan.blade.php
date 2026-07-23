{{--
    Template ini digunakan untuk menghasilkan salinan PDF Surat Pesanan Pembelian dari Pelanggan.
--}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Pesanan Pembelian - {{ $data->nomor_pesanan_penjualan }}</title>
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

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #000;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 8px;
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

        .mb-3 {
            margin-bottom: 15px;
        }

        .mb-4 {
            margin-bottom: 25px;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .line {
            border-bottom: 3px solid #000;
            margin: 10px 0 15px;
        }
    </style>
</head>

<body>

    {{-- KOP SURAT (Milik Perusahaan yang Menerima Pesanan, bisa diubah sesuai kebutuhan) --}}
    <table class="table-no-border" style="margin-bottom: 10px;">
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
                <div style="font-size:16px; font-weight:bold; text-transform:uppercase;">
                    {{ $profileUser->name ?? 'Nama Perusahaan' }}</div>
                <div style="font-size:11px;">{{ $profileUser->alamat ?? 'Alamat Perusahaan' }}</div>
                <div style="font-size:11px;">Telp: {{ $profileUser->nomor_telepon ?? '-' }} | Email:
                    {{ $profileUser->email ?? '-' }}</div>
            </td>
            <td width="15%"></td>
        </tr>
    </table>

    <div class="line"></div>
    <h3 class="text-center fw-bold mb-3 uppercase">SURAT PESANAN PEMBELIAN</h3>
    {{-- Nomor dan Tanggal Surat --}}
    <table width="100%" class="table-no-border mb-4">
        <tr>
            <td width="70%"></td>
            <td width="30%">
                <strong>Nomor:</strong>
                ({{ $data->nomor_pesanan_penjualan ?? '___' }})
                <br>
                <strong>Tanggal:</strong>
                {{ \Carbon\Carbon::parse($data->tanggal_pesanan_penjualan ?? now())->translatedFormat('d F Y') ?? ($data->tanggal_pesanan_penjualan ?? date('d/m/Y')) }}
            </td>
        </tr>
    </table>

    {{-- Kepada & Rencana Pengiriman --}}
    <p class="mb-3">
        Kepada Yth.<br>
        <strong>
            {{ $profileUser->name ?? '-' }}
        </strong><br>
        {{ $profileUser->alamat ?? '-' }}
        <br>
        {{ $profileUser->nomor_telepon ?? '-' }}
    </p>

    <p class="mb-4">
        Dengan hormat,<br>
        Melalui surat ini, kami dari pihak pemesan bermaksud melakukan pemesanan barang kepada perusahaan Bapak/Ibu. Berikut adalah rincian
        barang yang kami pesan dan kami harapkan pengirimannya paling lambat pada tanggal
        <strong>{{ $data->tanggal_kirim_pesanan_penjualan ? \Carbon\Carbon::parse($data->tanggal_kirim_pesanan_penjualan)->translatedFormat('d F Y') : '-' }}</strong>:
    </p>

    {{-- Tabel Detail Barang --}}
    <table class="table">
        <thead>
            <tr class="text-center">
                <th width="5%">No</th>
                <th width="15%">Kuantitas</th>
                <th width="45%">Nama Barang</th>
                <th width="15%">Harga Satuan</th>
                <th width="20%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data->details as $detail)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $detail->kuantitas }} {{ $detail->satuan ?? '' }}</td>
                    <td>{{ $detail->nama_barang }}</td>
                    <td class="text-right">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada item</td>
                </tr>
            @endforelse

            {{-- Baris kosong tetap memiliki border karena CSS di atas --}}
            @for ($i = count($data->details) + 1; $i <= 5; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor

            {{-- Total --}}
            <tr>
                <td colspan="4" class="text-right fw-bold" style="font-size: 13px;">TOTAL</td>
                <td class="text-right fw-bold" style="font-size: 13px;">
                    Rp {{ number_format($data->details->sum('total'), 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>
    {{-- Catatan (opsional) --}}
    @if (isset($data->catatan) && $data->catatan)
        <p><strong>Catatan:</strong><br>{{ nl2br(e($data->catatan)) }}</p>
    @endif

    {{-- Tanda Tangan --}}
    <table width="100%" class="table-no-border" style="margin-top: 50px;">
        <tr>
            <td width="70%" class="text-center">

            </td>
            <td width="30%" class="text-center">
                Hormat Kami,<br>
                <strong>{{ $data->pelanggan->nama ?? '_________' }}</strong><br>
                <div style="height:60px;"></div>
                <strong style="text-decoration: underline;">{{ $data->nama_bagian_pembelian ?? '__________________' }}</strong><br>
                Pelanggan / Pemesan
            </td>
        </tr>
    </table>

</body>

</html>
