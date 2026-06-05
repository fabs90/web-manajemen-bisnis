<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Agenda Janji Temu</title>
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

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 6px;
        }

        .table-no-border {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-no-border td {
            border: none;
            padding: 2px 0;
        }

        h3 {
            margin: 0;
        }

        .mb-1 {
            margin-bottom: 0;
        }

        .mb-2 {
            margin-bottom: 10px;
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
    </style>
</head>

<body>

    @php
        $profileUser = auth()->user();
    @endphp

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
                <div style="font-size:11px;">{{ $profileUser->alamat ?? 'Alamat Perusahaan' }}</div>
                <div style="font-size:11px;">
                    Telp: {{ $profileUser->nomor_telepon ?? '-' }} | Email: {{ $profileUser->email ?? '-' }}
                </div>
            </td>
            <td width="15%"></td>
        </tr>
    </table>

    <div class="line"></div>

    <h3 class="text-center fw-bold uppercase mb-1">AGENDA JANJI TEMU</h3>
    <div class="text-center mb-3">
        <strong>Tanggal Pencatatan:</strong> {{ \Carbon\Carbon::parse($agendaJanjiTemu->created_at)->translatedFormat('d F Y') }}<br>
    </div>

    <table class="table-no-border mb-3">
        <tr>
            <td width="30%">Nama Pembuat Janji</td>
            <td width="70%">: <b>{{ $agendaJanjiTemu->nama_pembuat ?? '-' }}</b></td>
        </tr>
        <tr>
            <td>Perusahaan</td>
            <td>: {{ $agendaJanjiTemu->perusahaan ?? '-' }}</td>
        </tr>
        <tr>
            <td>Nomor Telepon</td>
            <td>: {{ $agendaJanjiTemu->nomor_telepon ?? '-' }}</td>
        </tr>
    </table>

    <p class="fw-bold mb-2">Rincian Pertemuan:</p>
    <table class="table mb-4">
        <tr>
            <td width="30%" class="fw-bold">Tanggal Pertemuan</td>
            <td>{{ \Carbon\Carbon::parse($agendaJanjiTemu->tgl_panggilan)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Waktu Pertemuan</td>
            <td>{{ \Carbon\Carbon::parse($agendaJanjiTemu->waktu_panggilan)->format('H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="fw-bold">Bertemu Dengan</td>
            <td>{{ $agendaJanjiTemu->bertemu_dengan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Jabatan</td>
            <td>{{ $agendaJanjiTemu->jabatan_title ?? '-' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Tempat Pertemuan</td>
            <td>{{ $agendaJanjiTemu->tempat_pertemuan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Keperluan</td>
            <td>{{ $agendaJanjiTemu->keperluan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Status Konfirmasi</td>
            <td>{{ ucfirst($agendaJanjiTemu->status) ?? '-' }}</td>
        </tr>
    </table>

    {{-- Tanda tangan / Pengesahan --}}
    <table width="100%" class="table-no-border" style="margin-top:30px;">
        <tr>
            <td width="60%"></td>
            <td width="40%" class="text-center">
                Pencatat,<br>
                <div style="height:70px;"></div>
                <strong>( {{ $profileUser->name ?? 'Admin' }} )</strong><br>
                <span>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</span>
            </td>
        </tr>
    </table>

</body>

</html>
