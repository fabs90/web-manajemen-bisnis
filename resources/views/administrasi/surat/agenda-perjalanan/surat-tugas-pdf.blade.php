<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Surat Tugas - {{ $agendaPerjalanan->nama_pelaksana ?? '' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-no-border td {
            border: none !important;
            padding: 2px 0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .line {
            border-bottom: 3px solid #000;
            margin: 10px 0 20px;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: underline;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .subtitle {
            text-align: center;
            margin-bottom: 30px;
        }

        .content {
            margin-top: 20px;
            margin-bottom: 30px;
        }

        .signature {
            margin-top: 50px;
        }
    </style>
</head>

<body>

    {{-- KOP SURAT --}}
    <table class="table-no-border" style="margin-bottom: 10px;">
        <tr>
            <td width="15%">
                @if (isset($userProfile->logo_perusahaan) && $userProfile->logo_perusahaan)
                    @php
                        $logoPath = storage_path('app/public/' . $userProfile->logo_perusahaan);
                        if (file_exists($logoPath)) {
                            $logoBase64 = base64_encode(file_get_contents($logoPath));
                            $logoMime = mime_content_type($logoPath);
                        }
                    @endphp
                    @if (isset($logoBase64))
                        <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}" style="height:70px;">
                    @endif
                @endif
            </td>
            <td width="70%" class="text-center">
                <div style="font-size:16px; font-weight:bold; text-transform:uppercase;">
                    {{ $userProfile->name ?? config('app.name') }}</div>
                <div style="font-size:11px;">{{ $userProfile->alamat ?? '' }}</div>
                <div style="font-size:11px;">Telp: {{ $userProfile->nomor_telepon ?? '-' }} | Email:
                    {{ $userProfile->email ?? '-' }}</div>
            </td>
            <td width="15%"></td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="title">SURAT TUGAS</div>
    <div class="subtitle">Nomor: ST-{{ str_pad($agendaPerjalanan->id, 4, '0', STR_PAD_LEFT) }}/{{ date('Y') }}</div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini:</p>
        <table class="table-no-border" style="margin-left: 20px; margin-bottom: 20px;">
            <tr>
                <td width="20%">Nama</td>
                <td width="2%">:</td>
                <td><strong>{{ $userProfile->nama_pimpinan ?? '-' }}</strong></td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td>Pimpinan Perusahaan</td>
            </tr>
        </table>

        <p>Dengan ini menugaskan kepada:</p>
        <table class="table-no-border" style="margin-left: 20px; margin-bottom: 20px;">
            <tr>
                <td width="20%">Nama</td>
                <td width="2%">:</td>
                <td><strong>{{ $agendaPerjalanan->nama_pelaksana ?? '-' }}</strong></td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td>{{ $agendaPerjalanan->jabatan ?? '-' }}</td>
            </tr>
        </table>

        <p>Untuk melaksanakan tugas perjalanan dinas dengan rincian sebagai berikut:</p>
        <table class="table-no-border" style="margin-left: 20px; margin-bottom: 20px;">
            <tr>
                <td width="20%">Tujuan</td>
                <td width="2%">:</td>
                <td>{{ $agendaPerjalanan->tujuan ?? '-' }}</td>
            </tr>
            <tr>
                <td>Waktu Pelaksanaan</td>
                <td>:</td>
                <td>{{ optional($agendaPerjalanan)->tanggal_mulai ? \Carbon\Carbon::parse($agendaPerjalanan->tanggal_mulai)->translatedFormat('d F Y') . ' s/d ' . \Carbon\Carbon::parse($agendaPerjalanan->tanggal_selesai)->translatedFormat('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top;">Keperluan</td>
                <td style="vertical-align: top;">:</td>
                <td style="vertical-align: top;">{{ $agendaPerjalanan->keperluan ?? '-' }}</td>
            </tr>
        </table>

        <p>Demikian surat tugas ini diberikan agar dapat dilaksanakan dengan penuh tanggung jawab. Setelah selesai melaksanakan tugas, harap segera membuat laporan hasil perjalanan dinas.</p>
    </div>

    {{-- TANDA TANGAN --}}
    <table class="signature table-no-border">
        <tr>
            <td width="60%"></td>
            <td width="40%" class="text-center">
                <br>
                {{ optional($agendaPerjalanan->tanggal_disiapkan) ? \Carbon\Carbon::parse($agendaPerjalanan->tanggal_disiapkan)->translatedFormat('d F Y') : date('d F Y') }}<br>
                <br>
                <br>
                <div style="height: 80px;">
                    @if (isset($userProfile->ttd_pemimpin) && $userProfile->ttd_pemimpin)
                        @php
                            $ttdPemimpinPath = storage_path('app/public/' . $userProfile->ttd_pemimpin);
                            if (file_exists($ttdPemimpinPath)) {
                                $ttdPemimpinBase64 = base64_encode(file_get_contents($ttdPemimpinPath));
                                $ttdPemimpinMime = mime_content_type($ttdPemimpinPath);
                            }
                        @endphp
                        @if (isset($ttdPemimpinBase64))
                            <img src="data:{{ $ttdPemimpinMime }};base64,{{ $ttdPemimpinBase64 }}"
                                style="max-width: 150px; max-height: 80px; width: auto; height: auto;">
                        @endif
                    @endif
                </div>
                <br>
                <strong><u>{{ $userProfile->nama_pimpinan ?? 'Nama Pimpinan' }}</u></strong><br>
                Pimpinan Perusahaan
            </td>
        </tr>
    </table>

</body>

</html>
