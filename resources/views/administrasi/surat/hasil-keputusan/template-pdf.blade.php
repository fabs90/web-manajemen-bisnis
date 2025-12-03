<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keputusan Rapat</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 0;
        }
        .bordered {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        .content {
            padding: 15px;
        }
        .center {
            text-align: center;
        }
        .title-block {
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .bold {
            font-weight: bold;
        }
        .signature {
            margin-top: 50px;
            text-align: center;
        }
    </style>
</head>
<body>

<table class="bordered">
    <tr>
        <td class="content center">
            <span class="bold">{{ $profileUser->name ?? 'NAMA PERUSAHAAN' }}</span><br>
            {{ $profileUser->alamat ?? 'ALAMAT PERUSAHAAN' }}
        </td>
    </tr>

    {{-- Judul --}}
    <tr>
        <td class="content center title-block">
            <span class="bold" style="text-decoration: underline;">KEPUTUSAN RAPAT</span><br>
            <span class="bold">Nomor: {{ $hasil->nomor_surat }}</span>
        </td>
    </tr>

    {{-- Isi Keputusan --}}
    <tr>
        <td class="content" style="text-align: justify;">
            Pada hari ini,
            {{ \Carbon\Carbon::parse($hasil->tanggal_tujuan)->locale('id')->isoFormat('dddd, D MMMM Y') }},
            telah dilaksanakan rapat yang menghasilkan keputusan sebagai berikut:
            <br><br>

            {!! nl2br(e($hasil->keputusan_rapat)) !!}
            <br><br>

            Demikianlah keputusan rapat ini dibuat untuk dilaksanakan sebagaimana mestinya.
        </td>
    </tr>

    {{-- Tanda Tangan --}}
    <tr>
        <td class="content center">
            {{ $hasil->kota_tujuan }},
            {{ \Carbon\Carbon::parse($hasil->tanggal_tujuan)->format('d-m-Y') }}<br>
            <span class="bold">{{ $hasil->jabatan_penanggung_jawab }}</span>

            <div class="signature">
                <span class="bold">{{ $hasil->nama_penanggung_jawab }}</span>
            </div>
        </td>
    </tr>
</table>

</body>
</html>
