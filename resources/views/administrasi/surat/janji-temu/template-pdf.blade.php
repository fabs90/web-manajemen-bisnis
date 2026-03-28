<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Agenda Janji Temu - {{ $agendaJanjiTemu->id }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 0; }
        .container { padding: 30px; }
        
        /* Header / KOP */
        .header-table { width: 100%; border-bottom: 2px solid #444; margin-bottom: 20px; padding-bottom: 10px; }
        .company-name { font-size: 18px; font-weight: bold; text-transform: uppercase; color: #1a5c96; }
        .company-address { font-size: 10px; color: #666; line-height: 1.4; }
        
        /* Document Title */
        .doc-title { text-align: center; margin: 20px 0; }
        .doc-title h2 { margin: 0; font-size: 16px; text-transform: uppercase; letter-spacing: 1px; color: #222; }
        .doc-title p { margin: 5px 0 0; font-size: 11px; color: #777; }

        /* Info Table */
        .info-table { width: 100%; margin-top: 10px; border: 1px solid #eee; }
        .info-table td { padding: 12px 10px; border-bottom: 1px solid #eee; }
        .label { width: 30%; background-color: #f9f9f9; font-weight: bold; color: #555; text-transform: uppercase; font-size: 10px; }
        .value { width: 70%; color: #000; font-size: 12px; }

        /* Badge Status */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
            background-color: #e2e2e2;
        }
        .status-active { background-color: #d1e7dd; color: #0f5132; }
        .status-reschedule { background-color: #fff3cd; color: #856404; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 30px;
            right: 30px;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>

<body>
    <div class="container">
        {{-- KOP SURAT (Opsional: Tambahkan Logo jika ada) --}}
        <table class="header-table">
            <tr>
                <td width="70%">
                    <div class="company-name">DIGITRANS</div>
                    <div class="company-address">
                        Sistem Pengelolaan Administrasi & Transaksi Bisnis Terpadu<br>
                        Email: support@digitrans.id | Web: www.digitrans.id
                    </div>
                </td>
                <td width="30%" style="text-align: right; vertical-align: middle;">
                    <div style="font-size: 14px; font-weight: bold; color: #666;">#{{ $agendaJanjiTemu->id }}</div>
                </td>
            </tr>
        </table>

        <div class="doc-title">
            <h2>Agenda Janji Temu</h2>
            <p>ID Transaksi: AT-{{ date('Y') }}-{{ str_pad($agendaJanjiTemu->id, 5, '0', STR_PAD_LEFT) }}</p>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Nama Pemohon</td>
                <td class="value">{{ $agendaJanjiTemu->nama_pembuat ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jabatan / Instansi</td>
                <td class="value">{{ $agendaJanjiTemu->jabatan ?? '-' }} - {{ $agendaJanjiTemu->perusahaan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Lokasi Pertemuan</td>
                <td class="value">{{ $agendaJanjiTemu->tempat_pertemuan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Keperluan</td>
                <td class="value">{{ $agendaJanjiTemu->keperluan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jadwal Panggilan</td>
                <td class="value">
                    <strong>{{ \Carbon\Carbon::parse($agendaJanjiTemu->tgl_panggilan)->translatedFormat('d F Y') }}</strong> 
                    <span style="color: #888;">|</span> 
                    Pukul {{ \Carbon\Carbon::parse($agendaJanjiTemu->waktu_panggilan)->format('H:i') }} WIB
                </td>
            </tr>
            <tr>
                <td class="label">Status Konfirmasi</td>
                <td class="value">
                    <span class="status-badge {{ $agendaJanjiTemu->status == 'reschedule' ? 'status-reschedule' : 'status-active' }}">
                        {{ $agendaJanjiTemu->status }}
                    </span>
                </td>
            </tr>

            {{-- JIKA ADA RESCHEDULE --}}
            @if($agendaJanjiTemu->status == 'reschedule')
            <tr style="background-color: #fffdf5;">
                <td class="label" style="color: #856404;">Jadwal Baru (Re-schedule)</td>
                <td class="value" style="font-weight: bold;">
                    {{ \Carbon\Carbon::parse($agendaJanjiTemu->tgl_reschedule)->translatedFormat('d F Y') }} 
                    pukul {{ \Carbon\Carbon::parse($agendaJanjiTemu->waktu_reschedule)->format('H:i') }} WIB
                </td>
            </tr>
            @endif

            @if($agendaJanjiTemu->catatan)
            <tr>
                <td class="label">Catatan Tambahan</td>
                <td class="value" style="font-style: italic; color: #666;">"{{ $agendaJanjiTemu->catatan }}"</td>
            </tr>
            @endif
        </table>

        {{-- <div style="margin-top: 30px; font-size: 11px;">
            <p>* Mohon hadir 15 menit sebelum waktu yang ditentukan.<br>
            * Tunjukkan dokumen PDF ini saat tiba di lokasi pertemuan.</p>
        </div> --}}

        <div class="footer">
            <div>Dicetak otomatis oleh Sistem Digitrans pada: {{ now()->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }}</div>
            <div style="text-align: right;">Halaman 1 dari 1</div>
        </div>
    </div>
</body>
</html>