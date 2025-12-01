<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Memo Kredit - {{ $memo->nomor_memo }}</title>
    <style>
        body { font-family: "DejaVu Sans", sans-serif; font-size: 12px; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        .bordered, .bordered th, .bordered td {
            border: 1px solid black;
            padding: 6px;
        }
        .text-center { text-align: center; }
        .mt-1 { margin-top: 5px; }
        .mt-2 { margin-top: 10px; }
        .mt-3 { margin-top: 15px; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .w-50 { width: 50%; }
    </style>
</head>
<body>

    <!-- Kop Surat -->
    <div class="text-center bold uppercase" style="font-size:16px;">
        {{ $userProfile->name ?? 'Nama Perusahaan' }}
    </div>
    <div class="text-center" style="font-size: 11px;">
        {{ $userProfile->alamat ?? 'Alamat Perusahaan' }}
    </div>
    <hr class="mt-2">

    <h3 class="text-center mt-2 bold">MEMO KREDIT</h3>

    <!-- Informasi Memo Kredit -->
    <table class="mt-3">
        <tr>
            <td class="w-50">Nomor Memo</td>
            <td>: {{ $memo->nomor_memo }}</td>
        </tr>
        <tr>
            <td>Tanggal Memo</td>
            <td>: {{ \Carbon\Carbon::parse($memo->tanggal)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Nomor Faktur</td>
            <td>: {{ $faktur->kode_faktur }}</td>
        </tr>
        <tr>
            <td>Pembeli</td>
            <td>: {{ $faktur->pelanggan->nama }}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>: {{ $faktur->pelanggan->alamat }}</td>
        </tr>
    </table>

    <!-- Data Barang -->
    <h4 class="mt-3 bold">Rincian Barang Dikembalikan</h4>

    <table class="bordered">
        <thead class="text-center">
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Kuantitas</th>
                <th>Harga Satuan</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
        @foreach($memo->memoKreditDetail as $index => $detail)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $detail->nama_barang }}</td>
                <td class="text-center">{{ $detail->kuantitas }}</td>
                <td class="text-center">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td class="text-center">Rp {{ number_format($detail->jumlah, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr class="bold">
                <td colspan="4" class="text-center">TOTAL</td>
                <td class="text-center">Rp {{ number_format($memo->total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Alasan -->
    <div class="mt-3">
        <strong>Alasan Pengembalian:</strong><br>
        {{ $memo->alasan_pengembalian }}
    </div>

    <!-- Tanda Tangan -->
    <br><br><br>
    <table>
        <tr class="text-center">
            <td class="w-50">Pembeli</td>
            <td class="w-50">Disetujui Oleh</td>
        </tr>
        <tr><td colspan="2"><br><br><br><br></td></tr>
        <tr class="text-center">
            <td>{{ $faktur->pelanggan->nama }}</td>
            <td>{{ $userProfile->name }}</td>
        </tr>
    </table>

</body>
</html>
