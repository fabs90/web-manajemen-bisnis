<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pengiriman Barang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 30px;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .text-left   { text-align: left; }
        .uppercase   { text-transform: uppercase; }
        .bold        { font-weight: bold; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.header td {
            padding: 4px 0;
        }
        table.items {
            margin-top: 20px;
            border: 1px solid #000;
        }
        table.items th,
        table.items td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        table.items th {
            background-color: #f0f0f0;
        }
        .no-border { border: none; }
        .mt-20 { margin-top: 20px; }
        .mt-40 { margin-top: 40px; }
        .underline { text-decoration: underline; }
        .signature {
            width: 300px;
            text-align: center;
            display: inline-block;
            margin-top: 50px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <div class="text-center bold uppercase" style="font-size:14px;">
        {{$userProfile->name ?? 'Nama Perusahaan'}}
    </div>
    <div class="text-center" style="margin-bottom:20px;">
        {{$userProfile->alamat ?? ''}} • {{$userProfile->telepon ?? ''}} • {{$userProfile->email ?? ''}}
    </div>
    <hr style="border-top:3px double #000; margin:15px 0;">

    <div class="text-center bold" style="font-size:16px; margin-bottom:20px;">
        SURAT PENGIRIMAN BARANG
    </div>

    <table class="header no-border">
        <tr>
            <td width="15%">Nomor</td>
            <td width="35%">: {{ $data->nomor_surat ?? '(001)/SPB/'.date('m').'/'.date('Y') }}</td>
            <td width="20%">Nomor Faktur</td>
            <td width="30%">: {{ $data->fakturPenjualan?->kode_faktur ?? '-' }}</td>
        </tr>
        <tr>
            <td>Kepada</td>
            <td>: {{ $data->fakturPenjualan?->nama_pembeli ?? '-' }}<br>
                &nbsp;&nbsp;&nbsp;{{ $data->fakturPenjualan?->alamat_pembeli ?? '' }}</td>
            <td>Nomor Pesanan</td>
            <td>: {{ $data->fakturPenjualan?->nomor_pesanan ?? '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>Tanggal</td>
            <td>: {{ $data->fakturPenjualan?->tanggal ? \Carbon\Carbon::parse($data->fakturPenjualan->tanggal)->format('d-m-Y') : '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>Barang dikirim via</td>
            <td>: {{ $data->fakturPenjualan?->jenis_pengiriman ?? '-' }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">JUMLAH YANG<br>DIPESAN</th>
                <th width="25%">JUMLAH YANG<br>DIKIRIM</th>
                <th width="45%">NAMA BARANG</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data->fakturPenjualan?->fakturPenjualanDetail ?? [] as $index => $detail)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $detail->qty ?? $detail->jumlah }}</td>
                    <td>{{ $detail->qty ?? $detail->jumlah }}</td>
                    <td class="text-left">{{ $detail->nama_barang ?? $detail->produk?->nama ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Tidak ada detail barang</td>
                </tr>
            @endforelse

            @for($i = count($data->fakturPenjualan?->fakturPenjualanDetail ?? []) + 1; $i <= 8; $i++)
                <tr style="height:35px;">
                    <td>{{ $i }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    <table class="no-border mt-20">
        <tr>
            <td width="20%">Barang Diterima:</td>
            <td>
                Tanggal : {{ $data->tanggal_barang_diterima ? \Carbon\Carbon::parse($data->tanggal_barang_diterima)->format('d/m/Y') : '__/__/____' }}
            </td>
        </tr>
        <tr>
            <td>Keadaan</td>
            <td>: {{ $data->keadaan == 'baik' ? 'Baik' : 'Rusak' }} </td>
        </tr>
        <tr>
         <td>Keterangan</td>
        <td>: {{ $data->keterangan ?? '-'}}</td>
        </tr>
    </table>

    <div class="mt-40" style="position:relative;">
        <table width="100%">
            <tr>
                <td width="50%" class="text-center">
                    <div class="signature">
                        Yang Menerima<br><br><br><br><br>
                        <span class="underline">{{ $data->nama_penerima ?? '(Nama)' }}</span><br>
                        Bagian Penerimaan
                    </div>
                </td>
                <td width="50%" class="text-center">
                    <div class="signature">
                        Mengetahui,<br><br><br><br><br>
                        <span class="underline">{{ $data->nama_pengirim ?? $data->user?->name ?? '(Nama)' }}</span><br>
                        Bagian Pengiriman
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
