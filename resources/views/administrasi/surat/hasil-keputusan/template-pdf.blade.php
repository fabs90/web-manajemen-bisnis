<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keputusan Rapat</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .bordered {
            border: 1px solid #000;
        }
        .center {
            text-align: center;
        }
        .content {
            padding: 8px;
            vertical-align: top;
        }
        .signature-space {
            height: 80px;
        }
    </style>
</head>
<body>

<table class="bordered">
    <tr>
        <td class="content center" width="25%">
            <img src="{{ public_path('storage/' . $profileUser->logo_perusahaan) }}" width="100">
        </td>
        <td class="content center">
            <strong>{{ $profileUser->name ?? 'NAMA PERUSAHAAN' }}</strong><br>
            {{ $profile->alamat ?? 'ALAMAT PERUSAHAAN' }}
        </td>
    </tr>

    {{-- Judul --}}
    <tr>
        <td colspan="2" class="content center">
            <strong>KEPUTUSAN RAPAT</strong><br>
            Nomor: {{ $hasil->nomor_surat }}
        </td>
    </tr>

    {{-- Isi Keputusan --}}
    <tr>
        <td colspan="2" class="content">
            Pada hari ini, {{ \Carbon\Carbon::parse($hasil->tanggal_tujuan)->isoFormat('dddd, D MMMM Y') }},
            kami telah membuat beberapa keputusan sebagai berikut:
            <br><br>

            {!! nl2br(e($hasil->keputusan_rapat)) !!}
            <br><br>

            Demikianlah keputusan rapat ini telah kami buat untuk dilaksanakan.
        </td>
    </tr>

    {{-- Tanda Tangan --}}
    <tr>
        <td colspan="2" class="content center">

            {{ $hasil->kota_tujuan }}, {{ \Carbon\Carbon::parse($hasil->tanggal_tujuan)->format('d-m-Y') }}<br>
            ({{ $hasil->jabatan_penanggung_jawab }})
            <br><br><br><br> {{-- Space tanda tangan --}}
            <strong>{{ $hasil->nama_penanggung_jawab }}</strong>
        </td>
    </tr>

</table>

</body>
</html>
