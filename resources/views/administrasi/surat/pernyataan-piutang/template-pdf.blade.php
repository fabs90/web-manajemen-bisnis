<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Pernyataan Piutang</title>
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

        .table-no-border {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-no-border td {
            border: none;
            padding: 2px 0;
            vertical-align: top;
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

        .content-box {
            border: 1px solid #000;
            padding: 20px;
            margin-top: 20px;
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
                <div style="font-size:11px;">
                    {{ $profileUser->alamat ?? 'Alamat Perusahaan' }}
                </div>
                <div style="font-size:11px;">
                    Telp: {{ $profileUser->nomor_telepon ?? '-' }} |
                    Email: {{ $profileUser->email ?? '-' }}
                </div>
            </td>
            <td width="15%"></td>
        </tr>
    </table>

    <div class="line"></div>

    {{-- JUDUL --}}
    <h3 class="text-center fw-bold uppercase mb-3">
        SURAT PERNYATAAN PIUTANG
    </h3>

    {{-- KONTEN --}}
    <div class="content-box">
        <p>
            Saya yang bertanda tangan di bawah ini menyatakan bahwa pelanggan:
        </p>

        <table class="table-no-border mb-4">
            <tr>
                <td width="25%">Nama Pelanggan</td>
                <td width="5%">:</td>
                <td>{{ $pelanggan->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>:</td>
                <td>{{ $pelanggan->alamat ?? '-' }}</td>
            </tr>
        </table>

        <p>
            Memiliki jumlah <strong>piutang</strong> kepada perusahaan kami sebesar:
        </p>

        <h2 class="text-center fw-bold" style="margin:20px 0;">
            Rp {{ number_format($totalPiutang, 0, ',', '.') }}
        </h2>

        <p>
            Demikian surat pernyataan ini dibuat dengan sebenar-benarnya untuk digunakan
            sebagaimana mestinya.
        </p>
    </div>

    {{-- TANDA TANGAN --}}
    <table width="100%" class="table-no-border" style="margin-top:60px;">
        <tr>
            <td width="60%"></td>
            <td width="40%" class="text-center">
                {{ $profileUser->kota ?? 'Kota' }},
                {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                <br><br>
                Yang Menyatakan,
                <br><br><br><br><br>
                <strong>{{ $profileUser->name ?? 'Nama Pimpinan' }}</strong>
                <br>
                ({{ $profileUser->jabatan ?? 'Pimpinan Perusahaan' }})
            </td>
        </tr>
    </table>

</body>

</html>
