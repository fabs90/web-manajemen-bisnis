<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Hasil Keputusan Rapat - {{ $result->nomor_surat }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            line-height: 1.4;
            color: #000;
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
        .line {
            border-bottom: 3px solid #000;
            margin: 10px 0 15px;
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
                    Telp: {{ $user->nomor_telepon ?? '-' }} |
                    Email: {{ $user->email ?? '-' }}
                </div>
            </td>
            <td style="width:70px;"></td>
        </tr>
    </table>
     <div class="line"></div>
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

    {{-- Tanda Tangan di Kanan --}}
    <table style="width: 100%; margin-top: 30px; border-collapse: collapse;">
        <tr>

            <td style="width: 60%;"></td>


            <td style="width: 40%; text-align: center;">
                {{ $result->nama_kota }}, {{ \Carbon\Carbon::parse($result->tanggal)->format('d-m-Y') }}<br>
                <span class="bold">Pemimpin Rapat</span>

                <div class="signature" style="margin-top: 10px;">
                    {{-- Bagian TTD --}}
                    @if (!empty($result->ttd_pemimpin))
                        @php
                            $path = public_path('storage/' . $result->ttd_pemimpin);
                        @endphp

                        @if (file_exists($path))
                            @php
                                $type = pathinfo($path, PATHINFO_EXTENSION);
                                $data = file_get_contents($path);
                                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            @endphp
                            <img src="{{ $base64 }}" alt="Tanda Tangan" style="max-height: 80px; display: block; margin: 0 auto;">
                        @endif
                    @else
                        {{-- Jarak jika tidak ada gambar --}}
                        <div style="height: 60px;"></div>
                    @endif

                    <div style="margin-top: 5px;">
                        <span class="bold" style="text-decoration: underline;">{{ $result->pemimpin_rapat }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
