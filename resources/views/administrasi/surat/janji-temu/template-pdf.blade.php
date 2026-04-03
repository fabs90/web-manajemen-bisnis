<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 1cm; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
            background-color: #fff;
        }

        /* Accent Color */
        :root { --primary-color: #2c3e50; --accent-color: #f8f9fa; }

        .container { border: 2px solid #2c3e50; padding: 20px; position: relative; }

        /* Header Styling */
        .main-title {
            text-align: center;
            background-color: #2c3e50;
            color: #ffffff;
            padding: 10px;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: bold;
            font-size: 14px;
        }

        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }

        /* Modern Table Style */
        .data-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .label-column {
            width: 35%;
            font-weight: bold;
            color: #555;
            background-color: #fcfcfc;
        }

        .section-header {
            background-color: #f8f9fa;
            padding: 8px 10px;
            font-weight: bold;
            border-left: 4px solid #2c3e50;
            margin: 15px 0 5px;
            text-transform: uppercase;
            font-size: 10px;
            color: #2c3e50;
        }

        /* Keperluan Box */
        .content-box {
            border: 1px solid #eee;
            padding: 15px;
            background-color: #fff;
            min-height: 50px;
            margin-bottom: 15px;
        }

        /* Status Checkboxes */
        .status-container {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .status-item {
            margin-right: 25px;
            font-weight: bold;
        }
        .check-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #2c3e50;
            text-align: center;
            line-height: 12px;
            margin-right: 5px;
            background: #fff;
        }

        .footer-note {
            margin-top: 30px;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="main-title">Agenda Janji Temu</div>

    <div class="section-header">Informasi Dasar</div>
    <table class="data-table">
        <tr>
            <td class="label-column">a. Tgl membuat janji temu</td>
            <td>{{ \Carbon\Carbon::parse($agendaJanjiTemu->created_at)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label-column">b. Nama Pembuat Janji Temu</td>
            <td class="fw-bold">{{ $agendaJanjiTemu->nama_pembuat ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-column">c. Perusahaan</td>
            <td>{{ $agendaJanjiTemu->perusahaan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-column">d. Nomor Telpon</td>
            <td>{{ $agendaJanjiTemu->nomor_telepon ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-header">Jadwal Pertemuan</div>
    <table class="data-table">
        <tr>
            <td class="label-column">e. Tanggal</td>
            <td style="color: #c0392b; font-weight: bold;">
                {{ \Carbon\Carbon::parse($agendaJanjiTemu->tgl_panggilan)->translatedFormat('d F Y') }}
            </td>
        </tr>
        <tr>
            <td class="label-column">f. Waktu</td>
            <td>{{ \Carbon\Carbon::parse($agendaJanjiTemu->waktu_panggilan)->format('H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="label-column">g. Bertemu dengan</td>
            <td>{{ $agendaJanjiTemu->bertemu_dengan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-column">h. Tempat pertemuan</td>
            <td>{{ $agendaJanjiTemu->tempat_pertemuan ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-header">Keperluan Pertemuan</div>
    <div class="content-box">
        {{ $agendaJanjiTemu->keperluan ?? '-' }}
    </div>

    <div class="section-header">Status Konfirmasi</div>
    <div class="status-container">
        <span class="status-item">
            <span class="check-box">{!! $agendaJanjiTemu->status == 'terkonfirmasi' ? 'v' : '&nbsp;' !!}</span> Terkonfirmasi
        </span>
        <span class="status-item">
            <span class="check-box">{!! $agendaJanjiTemu->status == 'reschedule' ? 'v' : '&nbsp;' !!}</span> Reschedule
        </span>
        <span class="status-item">
            <span class="check-box">{!! $agendaJanjiTemu->status == 'dibatalkan' ? 'v' : '&nbsp;' !!}</span> Dibatalkan
        </span>
    </div>

    <table style="margin-top: 40px; border: none;">
        <tr style="border: none;">
            <td style="border: none; color: #888;">
                Dicatat oleh: <span style="color: #333;">{{ auth()->user()->name ?? 'Admin' }}</span>
            </td>
            <td style="border: none; text-align: right; color: #888;">
                Tanggal Cetak: <span style="color: #333;">{{ date('d/m/Y H:i') }}</span>
            </td>
        </tr>
    </table>
</div>

</body>
</html>