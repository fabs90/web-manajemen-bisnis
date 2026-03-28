<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Notulen Rapat - {{ $agendaJanjiTemu->judul_rapat }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.6; color: #000; margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 6px; word-wrap: break-word; vertical-align: top; }
        
        /* Utility Classes */
        .table-no-border td { border: none !important; padding: 2px 0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .bg-light { background-color: #f2f2f2; }
        
        /* Header & Layout */
        .line { border-bottom: 3px solid #000; margin: 10px 0 15px; }
        .section-title { margin-top: 20px; margin-bottom: 5px; font-weight: bold; font-size: 13px; text-transform: uppercase; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
        .box { border: 1px solid #000; padding: 10px; margin-top: 5px; min-height: 40px; background-color: #fdfdfd; }
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
                <div style="font-size:16px; font-weight:bold; text-transform:uppercase;">{{ $profileUser->name ?? 'Nama Perusahaan' }}</div>
                <div style="font-size:11px;">{{ $profileUser->alamat ?? 'Alamat Perusahaan' }}</div>
                <div style="font-size:11px;">Telp: {{ $profileUser->nomor_telepon ?? '-' }} | Email: {{ $profileUser->email ?? '-' }}</div>
            </td>
            <td width="15%"></td>
        </tr>
    </table>

    <div class="line"></div>
    <h3 class="text-center fw-bold" style="margin-bottom: 20px;">NOTULEN RAPAT</h3>

    {{-- INFORMASI RAPAT --}}
    <table class="table-no-border">
        <tr><td width="20%">Judul Rapat</td><td width="30%">: {{ $agendaJanjiTemu->judul_rapat }}</td><td width="20%">Tempat</td><td>: {{ $agendaJanjiTemu->tempat }}</td></tr>
        <tr><td>Tanggal</td><td>: {{ \Carbon\Carbon::parse($agendaJanjiTemu->tanggal)->translatedFormat('d F Y') }}</td><td>Pimpinan</td><td>: {{ $agendaJanjiTemu->pimpinan_rapat ?? $agendaJanjiTemu->pemimpin_rapat }}</td></tr>
        <tr><td>Waktu</td><td>: {{ $agendaJanjiTemu->waktu }}</td><td>Notulis</td><td>: {{ $agendaJanjiTemu->notulis ?? $agendaJanjiTemu->nama_notulis }}</td></tr>
    </table>

    {{-- PESERTA --}}
    <div class="section-title">Daftar Hadir Peserta</div>
    <table>
        <thead>
            <tr class="text-center fw-bold bg-light">
                <th width="5%">No</th>
                <th width="40%">Nama</th>
                <th width="35%">Jabatan</th>
                <th width="20%">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($agendaJanjiTemu->pesertaRapat as $i => $peserta)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $peserta->nama }}</td>
                    <td>{{ $peserta->jabatan }}</td>
                    <td class="text-center">
                        @if($peserta->tanda_tangan)
                            <img src="{{ storage_path('app/public/' . $peserta->tanda_tangan) }}" style="width: 60px;">
                        @else - @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- PEMBAHASAN --}}
    @if($agendaJanjiTemu->rapatDetails && $agendaJanjiTemu->rapatDetails->count())
        <div class="section-title">Pembahasan Rapat</div>
        <table>
            <thead>
                <tr class="text-center fw-bold bg-light">
                    <th width="5%">No</th>
                    <th width="25%">Agenda/Topik</th>
                    <th width="20%">Pembicara</th>
                    <th width="50%">Isi Pembahasan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($agendaJanjiTemu->rapatDetails as $i => $detail)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $detail->judul_agenda }}</td>
                        <td>{{ $detail->pembicara }}</td>
                        <td>{!! nl2br(e($detail->pembahasan)) !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- KEPUTUSAN --}}
    <div class="section-title">Keputusan Rapat</div>
    <div class="box">
        {!! nl2br(e($agendaJanjiTemu->keputusan_rapat)) !!}
    </div>

    {{-- TINDAK LANJUT --}}
    @if($agendaJanjiTemu->tindakLanjut && $agendaJanjiTemu->tindakLanjut->count())
        <div class="section-title">Tindak Lanjut (Action Plan)</div>
        <table>
            <thead>
                <tr class="text-center fw-bold bg-light">
                    <th width="5%">No</th>
                    <th>Tindakan</th>
                    <th width="20%">Pelaksana</th>
                    <th width="15%">Target</th>
                    <th width="15%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($agendaJanjiTemu->tindakLanjut as $i => $tl)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $tl->tindakan }}</td>
                        <td>{{ $tl->pelaksana }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($tl->target_selesai)->format('d-m-Y') }}</td>
                        <td class="text-center">{{ $tl->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- RAPAT BERIKUTNYA --}}
    @if ($agendaJanjiTemu->tanggal_rapat_berikutnya || $agendaJanjiTemu->agenda_rapat_berikutnya)
        <div class="section-title">Rencana Rapat Berikutnya</div>
        <table class="table-no-border">
            <tr><td width="20%">Tanggal</td><td>: {{ $agendaJanjiTemu->tanggal_rapat_berikutnya ? \Carbon\Carbon::parse($agendaJanjiTemu->tanggal_rapat_berikutnya)->translatedFormat('d F Y') : '-' }}</td></tr>
            <tr><td>Agenda</td><td>: {{ $agendaJanjiTemu->agenda_rapat_berikutnya ?? '-' }}</td></tr>
        </table>
    @endif

    {{-- TANDA TANGAN --}}
    <table class="table-no-border" style="margin-top: 50px; page-break-inside: avoid;">
        <tr>
            <td width="50%">
                <div>{{ $agendaJanjiTemu->nama_kota ?? 'Jakarta' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                <div class="fw-bold">Notulis,</div>
                <div style="margin-top: 60px;">( {{ $agendaJanjiTemu->notulis ?? $agendaJanjiTemu->nama_notulis }} )</div>
            </td>
            <td width="50%" class="text-center">
                <br>
                <div class="fw-bold">Pemimpin Rapat,</div>
                <div style="margin-top: 60px;">( {{ $agendaJanjiTemu->pimpinan_rapat ?? $agendaJanjiTemu->pemimpin_rapat }} )</div>
            </td>
        </tr>
    </table>

</body>
</html>