<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000; padding: 6px;
        }
        .no-border td { border: none !important; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mt-2 { margin-top: 10px; }
        .mt-4 { margin-top: 25px; }
    </style>
</head>
<body>

    <table class="text-center no-border mb-4">
        <tr>
            <td class="fw-bold" style="font-size: 22px; ">
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

    <h4 class="text-center">FAKTUR</h4>
    <p class="text-center">
        Nomor: {{ $faktur->kode_faktur }}
    </p>

    <table class="no-border" style="margin-top: 10px;">
        <tr>
            <td width="20%">Kepada</td>
            <td>: {{ $faktur->suratPengirimanBarang->pesananPembelian->pelanggan->nama }}</td>
            <td width="20%">Nomor Pesanan</td>
            <td>: {{ $faktur->suratPengirimanBarang->pesananPembelian->nomor_pesanan_pembelian ?? '-' }}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>: {{ $faktur->suratPengirimanBarang->pesananPembelian->pelanggan->alamat }}</td>
            <td>Nomor SPB</td>
            <td>: {{ $faktur->suratPengirimanBarang->nomor_pengiriman_barang }}</td>
        </tr>
        <tr>
            <td></td><td></td>
            <td>Tanggal</td>
            <td>: {{ \Carbon\Carbon::parse($faktur->tanggal_faktur)->format('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="table-bordered mt-2">
        <tr class="text-center">
            <th>No</th>
            <th>JUMLAH YANG DIPESAN</th>
            <th>JUMLAH YANG DIKIRIM</th>
            <th>NAMA BARANG</th>
            <th>HARGA/KEMAS</th>
            <th>JUMLAH</th>
        </tr>

        @php $no=1; $grandTotal = 0; @endphp

        @foreach($faktur->fakturPenjualanDetail as $item)
            @php
                $qty = $item->suratPengirimanBarangDetail->pesananPembelianDetail->kuantitas ?? 0;
                $grandTotal += $item->total;
            @endphp
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td class="text-center">{{ $qty }} K</td>
                <td class="text-center">{{ $qty }} K</td>
                <td>{{ $item->suratPengirimanBarangDetail->PesananPembelianDetail->nama_barang }}</td>
                <td class="text-right">Rp. {{ number_format($item->harga, 0, ',', '.') }}</td>
                <td class="text-right">Rp. {{ number_format($item->total, 0, ',', '.') }}</td>
            </tr>
        @endforeach

        <tr>
            <td colspan="5" class="text-right"><b>Total</b></td>
            <td class="text-right"><b>Rp. {{ number_format($grandTotal, 0, ',', '.') }}</b></td>
        </tr>
    </table>

    <p style="margin-top: 10px;">
        Barang dikirim via: ({{ $faktur->suratPengirimanBarang->jenis_pengiriman ?? '-' }})
    </p>

    <table class="no-border mt-4">
        <tr>
            <td width="70%"></td>
            <td class="text-center">
                Mengetahui,<br>
                Bagian Penjualan<br><br><br><br>
                ({{ $faktur->nama_bagian_penjualan }})
            </td>
        </tr>
    </table>

</body>
</html>
