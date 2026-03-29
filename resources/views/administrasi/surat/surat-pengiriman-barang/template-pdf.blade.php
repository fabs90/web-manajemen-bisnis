<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Pengiriman Barang - {{ $data->nomor_pengiriman_barang }}</title>
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
                <div style="font-size:11px;">{{ $profileUser->alamat ?? 'Alamat Perusahaan' }}</div>
                <div style="font-size:11px;">
                    Telp: {{ $profileUser->nomor_telepon ?? '-' }} | Email: {{ $profileUser->email ?? '-' }}
                </div>
            </td>
            <td width="15%"></td>
        </tr>
    </table>

    <div class="line"></div>

    <h3 class="text-center fw-bold uppercase mb-3">SURAT PENGIRIMAN BARANG</h3>

    {{-- Nomor --}}
    @php
        $nomorFormatted = str_pad($data->id, 3, '0', STR_PAD_LEFT);
        $bulan = date('m', strtotime($data->created_at ?? now()));
        $tahun = date('Y', strtotime($data->created_at ?? now()));
        $tanggalKirim = $data->tanggal_terima ? \Carbon\Carbon::parse($data->tanggal_terima)->format('d/m/Y') : '-';
    @endphp

    <table class="table-no-border mb-4">
        <tr>
            <td width="60%"></td>
            <td width="40%">
                <strong>Nomor:</strong>
                {{ $nomorFormatted }}/SPB/{{ $profileUser->name }}/{{ $bulan }}/{{ $tahun }}<br>
                <strong>Tanggal:</strong>
                {{ \Carbon\Carbon::parse($data->created_at ?? now())->format('d/m/Y') }}
            </td>
        </tr>
    </table>

    {{-- Tujuan --}}
    <p class="mb-3">
        Kepada Yth.<br>
        <strong>
            @if ($data->pesananPembelian->jenis == 'transaksi_keluar')
                {{ $data->pesananPembelian->supplier->nama ?? '-' }}
            @else
                {{ $data->pesananPembelian->pelanggan->nama ?? '-' }}
            @endif
        </strong><br>
        @if ($data->pesananPembelian->jenis == 'transaksi_keluar')
            {{ $data->pesananPembelian->supplier->alamat ?? '-' }}
        @else
            {{ $data->pesananPembelian->pelanggan->alamat ?? '-' }}
        @endif
    </p>

    <p class="mb-4">
        Bersama ini kami kirimkan barang dengan rincian sebagai berikut:
    </p>

    {{-- Info Tambahan --}}
    <table class="table-no-border mb-3">
        <tr>
            <td width="30%">Jenis Pengiriman</td>
            <td>: {{ $data->jenis_pengiriman ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tanggal Diterima</td>
            <td>: {{ $tanggalKirim }}</td>
        </tr>
        <tr>
            <td>Keadaan</td>
            <td>: {{ ucfirst($data->keadaan ?? 'baik') }}</td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td>: {{ $data->keterangan ?? '-' }}</td>
        </tr>
    </table>

    {{-- Tabel Barang --}}
    <table class="table">
        <thead>
            <tr class="text-center">
                <th width="5%">No</th>
                <th width="15%">Kuantitas</th>
                <th width="40%">Nama Barang</th>
                <th width="20%">Harga</th>
                <th width="20%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp

            @foreach ($data->suratPengirimanBarangDetail as $item)
                @php
                    $detail = $item->pesananPembelianDetail;
                    $subtotal = $item->jumlah_dikirim * $detail->harga;
                    $total += $subtotal;
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $item->jumlah_dikirim }}</td>
                    <td>{{ $detail->nama_barang }}</td>
                    <td class="text-right">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            {{-- Spacer biar rapih --}}
            @for ($i = count($data->suratPengirimanBarangDetail) + 1; $i <= 8; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor

            {{-- Total --}}
            <tr>
                <td colspan="4" class="text-right fw-bold">TOTAL</td>
                <td class="text-right fw-bold">
                    Rp {{ number_format($total, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Tanda tangan --}}
    <table width="100%" class="table-no-border" style="margin-top:50px;">
        <tr>
            <td width="50%" class="text-center">
                Yang Menerima,<br><br><br><br><br>
                <strong>( {{ $data->nama_penerima ?? '_________' }} )</strong>
            </td>
            <td width="50%" class="text-center">
                Pengirim,<br><br><br><br><br>
                <strong>( {{ $data->nama_pengirim ?? '_________' }} )</strong>
            </td>
        </tr>
    </table>

</body>

</html>
