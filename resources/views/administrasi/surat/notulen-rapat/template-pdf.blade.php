<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Notulen Rapat - {{ $agendaJanjiTemu->judul_rapat }}</title>

    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #000; padding: 6px; }
        .table-no-border td { border: none !important; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .line { border-bottom: 3px solid #000; margin-top: 10px; margin-bottom: 15px; }
        .mt-2 { margin-top: 10px; }
        .mt-4 { margin-top: 20px; }
    </style>
</head>

<body>

    {{-- Header / Kop Surat --}}
    <div style="display: flex; align-items: center; min-height: 50px;">
        <div class="kop" style="flex: 4; text-align:center;">
            <div class="fw-bold">{{ $profileUser->name }}</div>
            <div>{{ $profileUser->alamat }}</div>
            <div>{{ $profileUser->email }}</div>
            <div>{{ $profileUser->nomor_telepon }}</div>
        </div>
    </div>

    <div class="line"></div>

    <h3 class="text-center fw-bold">NOTULEN RAPAT</h3>

    {{-- Informasi Rapat --}}
    <table class="table-no-border">
        <tr><td width="35%">Judul Rapat</td><td>: {{ $agendaJanjiTemu->judul_rapat }}</td></tr>
        <tr><td>Tempat</td><td>: {{ $agendaJanjiTemu->tempat }}</td></tr>
        <tr><td>Tanggal</td><td>: {{ \Carbon\Carbon::parse($agendaJanjiTemu->tanggal)->translatedFormat('d F Y') }}</td></tr>
        <tr><td>Waktu</td><td>: {{ $agendaJanjiTemu->waktu }}</td></tr>
        <tr><td>Pimpinan Rapat</td><td>: {{ $agendaJanjiTemu->pimpinan_rapat }}</td></tr>
        <tr><td>Notulis</td><td>: {{ $agendaJanjiTemu->notulis }}</td></tr>
    </table>

    {{-- Peserta Rapat --}}
    <h4 class="mt-4 fw-bold">Daftar Hadir Peserta</h4>
    <table>
        <thead>
            <tr class="text-center fw-bold">
                <th width="5%">No</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th width="20%">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($agendaJanjiTemu->pesertaRapat as $i => $peserta)
                <tr>
                    <td class="text-center">{{ $i+1 }}</td>
                    <td>{{ $peserta->nama }}</td>
                    <td>{{ $peserta->jabatan }}</td>
                    <td class="text-center">
                        @php
                            $ttdPath = storage_path('app/public/' . $peserta->tanda_tangan);
                        @endphp

                        @if($peserta->tanda_tangan && file_exists($ttdPath))
                            <img src="{{ $ttdPath }}" style="width: 65px; height: auto;">
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Pembahasan --}}
    @if($agendaJanjiTemu->rapatDetails && $agendaJanjiTemu->rapatDetails->count())
        <h4 class="mt-4 fw-bold">Pembahasan Rapat</h4>
        <table>
            <thead>
                <tr class="text-center fw-bold">
                    <th width="5%">No</th>
                    <th>Agenda</th>
                    <th width="20%">Pembicara</th>
                    <th>Pembahasan</th>
                </tr>
            </thead>
            <tbody>
            @foreach($agendaJanjiTemu->rapatDetails as $i => $detail)
                <tr>
                    <td class="text-center">{{ $i+1 }}</td>
                    <td>{{ $detail->judul_agenda }}</td>
                    <td>{{ $detail->pembicara }}</td>
                    <td>{{ $detail->pembahasan }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    {{-- Keputusan Rapat --}}
    <h4 class="mt-4 fw-bold">Keputusan Rapat</h4>
    <div style="border: 1px solid #000; padding: 8px;">
        {!! nl2br(e($agendaJanjiTemu->keputusan_rapat)) !!}
    </div>

    {{-- Tindak Lanjut --}}
    @if($agendaJanjiTemu->tindakLanjut && $agendaJanjiTemu->tindakLanjut->count())
        <h4 class="mt-4 fw-bold">Tindak Lanjut</h4>
        <table>
            <thead>
                <tr class="text-center fw-bold">
                    <th width="5%">No</th>
                    <th>Tindakan</th>
                    <th>Pelaksana</th>
                    <th width="18%">Target Selesai</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($agendaJanjiTemu->tindakLanjut as $i => $tl)
                <tr>
                    <td class="text-center">{{ $i+1 }}</td>
                    <td>{{ $tl->tindakan }}</td>
                    <td>{{ $tl->pelaksana }}</td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($tl->target_selesai)->translatedFormat('d-m-Y') }}
                    </td>
                    <td>{{ $tl->status }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Rapat Selanjutnya --}}
    @if($agendaJanjiTemu->tanggal_rapat_berikutnya || $agendaJanjiTemu->agenda_rapat_berikutnya)
        <h4 class="mt-4 fw-bold">Rapat Berikutnya</h4>
        <table class="table-no-border">
            <tr>
                <td width="35%">Tanggal</td>
                <td>: {{ $agendaJanjiTemu->tanggal_rapat_berikutnya
                        ? \Carbon\Carbon::parse($agendaJanjiTemu->tanggal_rapat_berikutnya)->translatedFormat('d F Y')
                        : '-' }}
                </td>
            </tr>
            <tr>
                <td>Agenda</td>
                <td>: {{ $agendaJanjiTemu->agenda_rapat_berikutnya ?? '-' }}</td>
            </tr>
            <tr>
                <td>Nama Kota</td>
                <td>: {{ $agendaJanjiTemu->nama_kota ?? '-' }}</td>
            </tr>
        </table>
    @endif

    {{-- TTD --}}
    <div style="margin-top: 50px; width: 100%; text-align:right;">
        <div>{{ $agendaJanjiTemu->nama_kota ?? '' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
        <div class="fw-bold">{{ $agendaJanjiTemu->pimpinan_rapat }}</div>
    </div>

</body>
</html>
