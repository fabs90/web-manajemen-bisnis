<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pernyataan Piutang</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            line-height: 1.5;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .content { margin: 0 30px; }
        .ttd { margin-top: 60px; }
        .bordered {
            border: 1px solid #000;
            padding: 20px;
        }
    </style>
</head>

<body>

<div class="bordered">
    <h3 class="center bold">{{ $profileUser->name ?? 'NAMA PERUSAHAAN' }}</h3>
    <p class="center">{{ $profileUser->alamat ?? 'ALAMAT PERUSAHAAN' }}</p>
    <br>

    <h4 class="center bold">SURAT PERNYATAAN PIUTANG</h4>
    <br>

    <div class="content">
        Saya yang bertanda tangan di bawah ini menyatakan bahwa pelanggan:

        <br><br>
        <table>
            <tr>
                <td>Nama Pelanggan</td>
                <td>:</td>
                <td>{{ $pelanggan->nama }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>:</td>
                <td>{{ $pelanggan->alamat ?? '-' }}</td>
            </tr>
        </table>

        <br><br>
        Memiliki jumlah <b>piutang</b> kepada perusahaan kami sebesar:

        <h3 class="bold center">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h3>

        <br>
        Demikian surat pernyataan ini dibuat dengan sebenar-benarnya untuk digunakan sebagaimana mestinya.
    </div>

    <br><br>

    <div class="content center">
        {{ $profileUser->kota ?? 'Kota' }}, {{ date('d-m-Y') }} <br>
        <br><br><br>
        <strong>{{ $profileUser->name }}</strong> <br>
        ({{ $profileUser->jabatan ?? 'Pimpinan Perusahaan' }})
    </div>
</div>

</body>
</html>
