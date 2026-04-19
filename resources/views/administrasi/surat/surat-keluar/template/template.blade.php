<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            width: 90%;
            margin: 20px auto;
            border: 1px solid #eee;
            padding: 20px;
            border-radius: 8px;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
            pt: 10px;
        }

        .button {
            background-color: #00468c;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <p>Yth. <strong>{{ $surat->nama_penerima }}</strong>,</p>

        <p>Bersama email ini, kami sampaikan dokumen resmi dari <strong>{{ $user->name }}</strong> dengan rincian
            sebagai berikut:</p>

        <table style="width: 100%; margin-bottom: 20px;">
            <tr>
                <td width="120"><strong>Nomor Surat</strong></td>
                <td>: {{ $surat->nomor_surat }}</td>
            </tr>
            <tr>
                <td><strong>Perihal</strong></td>
                <td>: {{ $surat->perihal }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal</strong></td>
                <td>: {{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') }}</td>
            </tr>
        </table>

        <p>Detail isi surat dan lampiran pendukung telah kami sertakan dalam format PDF yang terlampir pada email ini.
        </p>

        <p>Mohon kiranya Bapak/Ibu dapat menerima dan menindaklanjuti dokumen tersebut. Jika ada pertanyaan lebih
            lanjut, silakan hubungi kami melalui balas email ini atau telepon ke {{ $user->nomor_telepon }}.</p>

        <p>Atas perhatian dan kerja samanya, kami ucapkan terima kasih.</p>

        <div class="footer">
            <p>Hormat kami,<br>
                <strong>{{ $surat->nama_pengirim }}</strong><br>
                {{ $user->name }}
            </p>
            <hr>
            <p><em>Email ini dikirimkan secara otomatis melalui sistem Digitrans. Mohon tidak membalas langsung jika
                    tidak diperlukan.</em></p>
        </div>
    </div>
</body>

</html>
