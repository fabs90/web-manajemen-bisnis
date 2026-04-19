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

    {{-- KOP SURAT CENTERED --}}
    <table class="kop-table">
        <tr>
            {{-- Cell Logo --}}
            <td width="15%" class="logo-cell">
                @if (isset($user->logo_perusahaan) && $user->logo_perusahaan)
                    @php
                        // Pakai path direktori lokal untuk DomPDF
                        $logoPath = public_path('storage/' . $user->logo_perusahaan);
                        if (file_exists($logoPath)) {
                            $logoBase64 = base64_encode(file_get_contents($logoPath));
                            $logoMime = mime_content_type($logoPath);
                        }
                    @endphp
                    @if (isset($logoBase64))
                        <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}" width="70">
                    @endif
                @endif
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
        </tr>
    </table>

    <div class="line"></div>

    {{-- NOMOR SURAT SEJAJAR TANGGAL (ADM-001) --}}
    <table class="info-table">
        <tr>
            <td width="60%">
                <strong>Nomor:</strong> {{ $surat->nomor_surat }}<br>
                <strong>Lampiran:</strong> {{ $surat->lampiran ?? '-' }}<br>
                <strong>Perihal:</strong> {{ $surat->perihal }}
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

                {{-- Validasi TTD --}}
                @if ($surat->ttd)
                    @php
                        $ttdPath = public_path('storage/' . $surat->ttd);
                        if (file_exists($ttdPath)) {
                            $ttdBase64 = base64_encode(file_get_contents($ttdPath));
                            $ttdMime = mime_content_type($ttdPath);
                        }
                    @endphp
                    @if (isset($ttdBase64))
                        <img src="data:{{ $ttdMime }};base64,{{ $ttdBase64 }}" width="110">
                    @else
                        <br><br><br><br>
                    @endif
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
