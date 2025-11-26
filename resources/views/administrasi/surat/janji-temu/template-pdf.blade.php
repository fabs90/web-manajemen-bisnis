<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Agenda Janji Temu - {{ $agendaJanjiTemu->id }}</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .title {
            text-align: center;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 25px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 6px 4px;
            vertical-align: top;
        }

        .label {
            width: 30%;
            font-weight: bold;
        }

        .status {
            text-transform: capitalize;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="title">
        Agenda Janji Temu<br>
        Nomor: {{ $agendaJanjiTemu->id }}
    </div>

    <table>
        <tr>
            <td class="label">Nama Pemohon</td>
            <td>: {{ $agendaJanjiTemu->nama_pemohon ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td>: {{ $agendaJanjiTemu->jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Perusahaan</td>
            <td>: {{ $agendaJanjiTemu->perusahaan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Keperluan</td>
            <td>: {{ $agendaJanjiTemu->keperluan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Janji Temu</td>
            <td>: {{ \Carbon\Carbon::parse($agendaJanjiTemu->tgl_panggilan)->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td class="label">Waktu</td>
            <td>: {{ \Carbon\Carbon::parse($agendaJanjiTemu->waktu_panggilan)->format('H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td>:
                <span class="status">
                    {{ $agendaJanjiTemu->status }}
                </span>
            </td>
        </tr>

        @if($agendaJanjiTemu->status == 'reschedule')
        <tr>
            <td class="label">Tanggal Re-schedule</td>
            <td>: {{ \Carbon\Carbon::parse($agendaJanjiTemu->tgl_reschedule)->format('d-m-Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Waktu Re-schedule</td>
            <td>: {{ \Carbon\Carbon::parse($agendaJanjiTemu->waktu_reschedule)->format('H:i') ?? '-' }} WIB</td>
        </tr>
        @endif

        @if($agendaJanjiTemu->catatan)
        <tr>
            <td class="label">Catatan</td>
            <td>: {{ $agendaJanjiTemu->catatan }}</td>
        </tr>
        @endif
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->timezone('Asia/Jakarta')->format('d-m-Y H:i') }}
    </div>
</body>
</html>
