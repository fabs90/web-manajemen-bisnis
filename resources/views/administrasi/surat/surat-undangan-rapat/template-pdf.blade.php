<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Undangan Rapat - {{ $agendaJanjiTemu->nomor_surat ?? '-' }}</title>
    <style>
        @page {
            margin: 1cm 1.5cm;
        }

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

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table-no-border {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-no-border td,
        .table-no-border th {
            border: none !important;
            padding: 2px 0;
            vertical-align: middle;
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

        /* INFO SURAT */
        .table-info td {
            padding: 2px 0;
            vertical-align: top;
        }

        /* AGENDA TABLE */
        .table-agenda {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .table-agenda th,
        .table-agenda td {
            border: 1px solid #000;
            padding: 6px;
        }

        .table-agenda th {
            background-color: #f0f0f0;
        }

        .mb-4 {
            margin-bottom: 25px;
        }
    </style>
</head>

<body>

    {{-- KOP SURAT --}}
    <table class="table-no-border" style="margin-bottom: 10px;">
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
                <div style="font-size:16px; font-weight:bold; text-transform:uppercase;">
                    {{ $profileUser->name ?? 'Nama Perusahaan' }}</div>
                <div style="font-size:11px;">{{ $profileUser->alamat ?? 'Alamat Perusahaan' }}</div>
                <div style="font-size:11px;">Telp: {{ $profileUser->nomor_telepon ?? '-' }} | Email:
                    {{ $profileUser->email ?? '-' }}</div>
            </td>
            <td width="15%"></td>
        </tr>
    </table>

    <div class="line"></div>
    {{-- Nomor, Lampiran, Perihal --}}
    <table class="table-info" width="100%">
        <tr>
            <td width="15%">Nomor</td>
            <td>: {{ $agendaJanjiTemu->nomor_surat ?? '-' }}</td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>: {{ $agendaJanjiTemu->lampiran ?? '-' }}</td>
        </tr>
        <tr>
            <td>Perihal</td>
            <td>:
                <strong>{{ strtoupper($agendaJanjiTemu->perihal ?? ($agendaJanjiTemu->judul_rapat ?? 'UNDANGAN RAPAT')) }}</strong>
            </td>
        </tr>
    </table>

    <br>

    {{-- Penerima --}}
    <div class="mb-4">
        Kepada Yth.<br>
        <strong>{{ $agendaJanjiTemu->nama_penerima ?? '-' }}</strong><br>
        {{ $agendaJanjiTemu->jabatan_penerima ?? '' }}<br>
        Di {{ $agendaJanjiTemu->kota_penerima ?? 'Tempat' }}
    </div>

    <p>
        Dengan hormat,<br>
        Sehubungan dengan akan diadakannya rapat <strong>{{ $agendaJanjiTemu->judul_rapat ?? '-' }}</strong>, kami
        mengundang Bapak/Ibu untuk hadir pada:
    </p>

    {{-- Detail Waktu --}}
    <table class="table-info" width="100%" style="margin-left: 20px;">
        <tr>
            <td width="20%">Hari</td>
            <td>: {{ $agendaJanjiTemu->hari ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: {{ \Carbon\Carbon::parse($agendaJanjiTemu->tanggal_rapat)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>:
                {{ $agendaJanjiTemu->waktu_mulai ? \Carbon\Carbon::parse($agendaJanjiTemu->waktu_mulai)->format('H:i') : '-' }}
                -
                {{ $agendaJanjiTemu->waktu_selesai ? \Carbon\Carbon::parse($agendaJanjiTemu->waktu_selesai)->format('H:i') : '-' }}
                WIB
            </td>
        </tr>
        <tr>
            <td>Tempat</td>
            <td>: {{ $agendaJanjiTemu->tempat ?? '-' }}</td>
        </tr>
    </table>

    <p style="margin-top: 15px;">Adapun agenda rapat adalah sebagai berikut:</p>

    {{-- Tabel Agenda --}}
    <table class="table-agenda">
        <thead>
            <tr>
                <th width="8%">No</th>
                <th>Agenda Rapat</th>
            </tr>
        </thead>
        <tbody>
            @if (isset($agendaJanjiTemu->details) && count($agendaJanjiTemu->details) > 0)
                @foreach ($agendaJanjiTemu->details as $detail)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $detail->agenda }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="2" class="text-center"><em>Agenda terlampir dalam judul rapat.</em></td>
                </tr>
            @endif
        </tbody>
    </table>

    <p>
        Demikian undangan ini kami sampaikan. Atas perhatian dan kehadirannya, kami ucapkan terima kasih.
    </p>

    {{-- Footer Tanda Tangan --}}
    <table class="ttd-table" border="0">
        <tr>
            <td width="25%" class="text-right">
                Hormat kami,<br>

                {{-- Validasi TTD --}}
                @if ($agendaJanjiTemu->ttd)
                    @php
                        $ttdPath = public_path('storage/' . $agendaJanjiTemu->ttd);
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
                <strong><u>{{ $agendaJanjiTemu->nama_penandatangan }}</u></strong><br>
                {{ $agendaJanjiTemu->jabatan_penandatangan }}
            </td>
        </tr>
    </table>

</body>

</html>
