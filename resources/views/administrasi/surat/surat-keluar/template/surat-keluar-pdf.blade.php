<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Surat Keluar - {{ $surat->nomor_surat }}</title>
    <style>
        @page {
            margin: 1cm 1.5cm;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            font-size: 12px;
            color: #000;
        }

        /* STYLING KOP SURAT (Style Center sesuai SPP) */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
        }

        .kop-table td {
            vertical-align: middle;
        }

        .logo-cell {
            text-align: left;
        }

        .text-cell {
            text-align: center;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .line {
            border-top: 4px solid #000;
            /* Tebal 4px, silakan ganti ke 3px kalau kemanisan */
            margin-top: 5px;
            margin-bottom: 20px;
        }

        /* TABEL INFO (Nomor & Tanggal Sejajar) */
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .content {
            margin-bottom: 20px;
        }

        .ttd-area {
            margin-top: 40px;
            width: 100%;
        }

        .ttd-table {
            width: 100%;
            margin-top: 40px;
        }

        .text-center {
            text-align: center;
        }

        .tembusan {
            margin-top: 30px;
            font-size: 11px;
        }
    </style>
</head>

<body>

    {{-- KOP SURAT --}}
    @php
        $logoPath = $user->logo_perusahaan ? public_path('storage/' . $user->logo_perusahaan) : null;
        $hasLogo = $logoPath && file_exists($logoPath);
        if ($hasLogo) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
            $logoMime = mime_content_type($logoPath);
        }
    @endphp

    <table class="kop-table">
        <tr>
            @if ($hasLogo)
                {{-- Cell Logo --}}
                <td width="15%" class="logo-cell">
                    <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}" width="70">
                </td>

                {{-- Cell Teks Tengah --}}
                <td width="70%" class="text-cell">
                    <strong style="font-size: 16px;" class="uppercase">{{ $user->name }}</strong><br>
                    <span style="font-size: 11px;">
                        {{ $user->alamat }}<br>
                        Telp: {{ $user->nomor_telepon }} | Email: {{ $user->email }}
                    </span>
                </td>

                {{-- Cell Penyeimbang --}}
                <td width="15%"></td>
            @else
                {{-- Full width centered text when no logo --}}
                <td width="100%" class="text-cell">
                    <strong style="font-size: 18px;" class="uppercase">{{ $user->name }}</strong><br>
                    <span style="font-size: 12px;">
                        {{ $user->alamat }}<br>
                        Telp: {{ $user->nomor_telepon }} | Email: {{ $user->email }}
                    </span>
                </td>
            @endif
        </tr>
    </table>

    <div class="line"></div>

    {{-- NOMOR SURAT SEJAJAR TANGGAL (ADM-001) --}}
    <table class="info-table">
        <tr>
            <td width="60%">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td width="70"><strong>Nomor</strong></td>
                        <td width="10"><strong>:</strong></td>
                        <td>{{ $surat->nomor_surat }}</td>
                    </tr>
                    <tr>
                        <td><strong>Lampiran</strong></td>
                        <td><strong>:</strong></td>
                        <td>{{ $surat->lampiran ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Perihal</strong></td>
                        <td><strong>:</strong></td>
                        <td>{{ $surat->perihal }}</td>
                    </tr>
                </table>
            </td>
            <td width="40%" class="text-right">
                {{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') }}
            </td>
        </tr>
    </table>

    {{-- TUJUAN --}}
    <div class="content">
        Kepada Yth:<br>
        <strong>{{ $surat->nama_penerima }}</strong><br>
        {{ $surat->jabatan_penerima }}<br>
        {{ $surat->alamat_penerima }}
    </div>

    {{-- ISI SURAT --}}
    <div class="content">
        <p style="text-align: justify;">{!! nl2br(e($surat->paragraf_pembuka)) !!}</p>
        <p style="text-align: justify;">{!! nl2br(e($surat->paragraf_isi)) !!}</p>
        <p style="text-align: justify;">{!! nl2br(e($surat->paragraf_penutup)) !!}</p>
    </div>

    {{-- AREA TANDA TANGAN --}}
    <table class="ttd-table" border="0">
        <tr>
            <td width="25%" class="text-right">
                Hormat kami,<br>
                @php
                    $ttdBase64 = null;
                    if (isset($user->ttd_pemimpin) && $user->ttd_pemimpin) {
                        $ttdPath = public_path('storage/' . $user->ttd_pemimpin);
                        if (file_exists($ttdPath) && is_file($ttdPath)) {
                            $ttdBase64 = base64_encode(file_get_contents($ttdPath));
                            $ttdMime = mime_content_type($ttdPath);
                        }
                    }
                @endphp
                @if ($ttdBase64)
                    <img src="data:{{ $ttdMime }};base64,{{ $ttdBase64 }}" style="height:70px;">
                @else
                    <br><br><br><br>
                @endif
                <br>
                <strong><u>{{ $surat->nama_pengirim }}</u></strong><br>
                {{ $surat->jabatan_pengirim }}
            </td>
        </tr>
    </table>

    {{-- TEMBUSAN --}}
    @if ($surat->tembusan)
        <div class="tembusan">
            <strong><u>Tembusan:</u></strong><br>
            {!! nl2br(e($surat->tembusan)) !!}
        </div>
    @endif

</body>

</html>
