<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Undangan Rapat</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 30px;
        }

        .kop {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .line {
            border-top: 2px solid #000;
            margin-top: -5px;
            margin-bottom: 10px;
        }

        .table-info td {
            padding: 2px 0;
        }

        .agenda-table,
        .agenda-table th,
        .agenda-table td {
            border: 1px solid #000;
            border-collapse: collapse;
        }

        .agenda-table th,
        .agenda-table td {
            padding: 6px;
        }

        .footer {
            margin-top: 40px;
            text-align: right;
        }
    </style>
</head>

<body>

    {{-- Header / Kop Surat --}}
    <div style="display: flex; align-items: center; min-height: 100px;">
        <div style="flex: 1; display: flex; justify-content: center;">
            <img src="{{ public_path('storage/' . $profileUser->logo_perusahaan) }}" alt="Logo" style="height: 80px;">
        </div>

        <div class="kop" style="flex: 4; text-align:center;">
            <div>{{ $profileUser->name }}</div>
            <div>{{ $profileUser->alamat }}</div>
            <div>{{ $profileUser->email ?? 'Email...' }}</div>
            <div>{{ $profileUser->nomor_telepon }}</div>
        </div>
    </div>

    <div class="line"></div>



    {{-- Nomor surat dan tanggal --}}
    <table class="table-info" width="100%">
        <tr>
            <td width="18%">Nomor</td>
            <td>: {{ $agendaJanjiTemu->nomor_surat ?? '-' }}</td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>: {{ $agendaJanjiTemu->lampiran ?? '-' }}</td>
        </tr>
        <tr>
            <td>Perihal</td>
            <td>: <strong>{{ $agendaJanjiTemu->perihal ?? $agendaJanjiTemu->judul_rapat }}</strong></td>
        </tr>
    </table>

    <br>

    {{-- Alamat Tujuan --}}
    <p>
        Kepada Yth:<br>
        <strong>{{ $agendaJanjiTemu->nama_penerima ?? '-' }}</strong><br>
        {{ $agendaJanjiTemu->jabatan_penerima ?? '' }}<br>
        Di {{ $agendaJanjiTemu->kota_penerima ?? '' }}
    </p>

    <p>
        Dengan hormat,
        Sehubungan dengan akan diadakannya rapat dengan agenda
        <strong>{{ $agendaJanjiTemu->judul_rapat ?? '-' }}</strong>,
        kami mengundang Bapak/Ibu untuk hadir pada:
    </p>

    {{-- Informasi rapat --}}
    <table class="table-info" width="100%">
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

    <br>

    {{-- Agenda Rapat --}}
    <p>Adapun agenda rapat sebagai berikut :</p>

    <table class="agenda-table" width="100%">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Agenda Rapat</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($agendaJanjiTemu->details as $detail)
                <tr>
                    <td class="text-center" align="center">{{ $loop->iteration }}</td>
                    <td>{{ $detail->agenda }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>
        Demikian undangan ini kami sampaikan, atas perhatian dan kehadirannya kami ucapkan terima kasih.
    </p>

    {{-- Penandatangan --}}
    <div class="footer">
        {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->translatedFormat('d F Y') }}<br>
        <strong>{{ $agendaJanjiTemu->jabatan_penandatangan }}</strong><br><br><br><br>
        <strong style="text-decoration: underline;">
            {{ $agendaJanjiTemu->nama_penandatangan }}
        </strong>
    </div>

</body>

</html>
