<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Faktur Penjualan - {{ $faktur->id }}</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 12px;
        }
        table { width: 100%; border-collapse: collapse; }
        .table-border td, .table-border th {
            border: 1px solid #000;
            padding: 5px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .mt-10 { margin-top: 10px; }
        .no-border { border: none !important; }
    </style>
</head>
<body>

    {{-- Header / Kop Surat --}}
    <div style="display: flex; align-items: center; min-height: 50px; margin-bottom: 10px;">
        <div class="kop" style="flex: 4; text-align: center;">
            <div class="fw-bold">{{ $profileUser->name }}</div>
            <div>{{ $profileUser->alamat }}</div>
            <div>{{ $profileUser->email }}</div>
            <div>{{ $profileUser->nomor_telepon }}</div>
        </div>
    </div>

    {{-- Judul Faktur --}}
    <table class="table-border">
        <tr>
            <td colspan="4" class="text-center fw-bold">FAKTUR PENJUALAN</td>
        </tr>
        <tr>
            <td colspan="4" class="text-center">
                Nomor: ({{ $faktur->kode_faktur ?? '---' }}/F/{{ now()->format('m/Y') }})
            </td>
        </tr>
        <tr>
            <td class="fw-bold" width="25%">Kepada</td>
            <td colspan="3">
                : {{ $faktur->nama_pembeli }}<br>{{ $faktur->alamat_pembeli }}
            </td>
        </tr>
        <tr>
            <td class="fw-bold">Nomor Pesanan</td>
            <td>: {{ $faktur->nomor_pesanan }}</td>
            <td class="fw-bold">Nomor SPB</td>
            <td>: {{ $faktur->nomor_spb }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Tanggal</td>
            <td colspan="3">
                : {{ \Carbon\Carbon::parse($faktur->tanggal)->format('d/m/Y') }}
            </td>
        </tr>
    </table>

    {{-- Detail Barang --}}
    <table class="table-border mt-10">
        <thead>
            <tr class="text-center fw-bold">
                <th width="5%">No</th>
                <th width="15%">JUMLAH DIPESAN</th>
                <th width="15%">JUMLAH DIKIRIM</th>
                <th>NAMA BARANG</th>
                <th width="20%">HARGA/KEMAS</th>
                <th width="20%">JUMLAH</th>
            </tr>
        </thead>
        <tbody>
            @php $totalAkhir = 0; @endphp
            @foreach ($faktur->fakturPenjualanDetail as $i => $detail)
                @php
                    $subTotal = $detail->harga * $detail->jumlah_dikirim;
                    $totalAkhir += $subTotal;
                @endphp
                <tr>
                    <td class="text-center">{{ $i+1 }}</td>
                    <td class="text-center">{{ $detail->jumlah_dipesan }}</td>
                    <td class="text-center">{{ $detail->jumlah_dikirim }}</td>
                    <td>{{ strtoupper($detail->nama_barang) }}</td>
                    <td class="text-right">Rp. {{ number_format($detail->harga,0,',','.') }}</td>
                    <td class="text-right">Rp. {{ number_format($subTotal,0,',','.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5" class="text-right fw-bold">Total</td>
                <td class="text-right fw-bold">Rp. {{ number_format($totalAkhir,0,',','.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="mt-10">
        Barang dikirim via: {{ $faktur->jenis_pengiriman }}
    </div>

    {{-- Tanda Tangan --}}
    <table class="no-border mt-10">
        <tr>
            <td width="60%"></td>
            <td class="fw-bold">Mengetahui,</td>
        </tr>
        <tr>
            <td></td>
            <td>Bagian Penjualan</td>
        </tr>
        <tr>
            <td height="55px"></td>
            <td class="text-center">
                ({{ $profileUser->name }})
            </td>
        </tr>
    </table>

</body>
</html>
