<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Hasil Keputusan Rapat - {{ $result->nomor_surat }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 0;
        }

        .content {
            padding: 5px;
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
    <!-- Kop Surat -->
    <table style="width:100%; margin-bottom:10px; border-collapse:collapse;">
        <tr>
            <td style="width:70px; vertical-align:middle;">
                @if (isset($user->logo_perusahaan) && $user->logo_perusahaan)
                    @php
                        $logoPath = public_path('storage/' . $user->logo_perusahaan);
                        $logoBase64 = base64_encode(file_get_contents($logoPath));
                        $logoMime = mime_content_type($logoPath);
                    @endphp
                    <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}" alt="Logo" style="height:60px;">
                @endif
            </td>
            <td style="vertical-align:middle; text-align:center;">
                <div style="font-size:15px; font-weight:bold; text-transform:uppercase;">
                    {{ $user->name ?? 'Nama Perusahaan' }}
                </div>
                <div style="font-size:11px; margin-top:2px;">
                    {{ $user->alamat ?? 'Alamat Perusahaan' }}
                </div>
                <div style="font-size:11px;">
                    Telp: {{ $user->nomor_telepon ?? '-' }} •
                    Email: {{ $user->email ?? '-' }}
                </div>
            </td>
            <td style="width:70px;"></td>
        </tr>
    </table>
    <div class="content center">
        <hr style="border:0; border-top:2px solid #000;">
    </div>
    {{-- Judul --}}
    <div class="content center title-block">
        <span class="bold" style="text-decoration: underline;">KEPUTUSAN RAPAT</span><br>
        <span class="bold">Nomor: {{ $result->nomor_surat }}</span>
    </div>
    <div class="content center">
        <hr style="border:0; border-top:1px solid #000; margin:0 0 8px 0;">
    </div>
    {{-- Isi Keputusan --}}
    <div class="content" style="text-align: justify;">
        Pada hari ini,
        {{ \Carbon\Carbon::parse($result->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }},
        telah dilaksanakan rapat yang menghasilkan keputusan sebagai berikut:
        <br><br>
        {!! nl2br(e($result->keputusan_rapat)) !!}
        <br><br>
        <hr style="border:0; border-top:1px solid #000; margin:8px 0;">
        Demikianlah keputusan rapat ini dibuat untuk dilaksanakan sebagaimana mestinya.
    </div>

    <div class="content center">
        <hr style="border:0; border-top:1px solid #000; margin:6px 0 0 0;">
    </div>

    {{-- Tanda Tangan --}}
    <div class="content center">
        {{ $result->nama_kota }},
        {{ \Carbon\Carbon::parse($result->tanggal)->format('d-m-Y') }}<br>
        <span class="bold">Pemimpin Rapat</span>

        <div class="signature">
            <span class="bold">{{ $result->pemimpin_rapat }}</span>
        </div>
    </div>
</body>

</html>
