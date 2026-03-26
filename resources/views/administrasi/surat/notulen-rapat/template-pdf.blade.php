<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Notulen Rapat - {{ $agendaJanjiTemu->judul_rapat }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background-color: #f2f2f2;
        }

        .table-no-border td {
            border: none !important;
            padding: 3px 0;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }

        .kop {
            text-align: center;
        }

        .kop-title {
            font-size: 16px;
            font-weight: bold;
        }

        .kop-sub {
            font-size: 11px;
        }

        .line {
            border-bottom: 2px solid #000;
            margin: 10px 0 15px;
        }

        .section-title {
            margin-top: 20px;
            font-weight: bold;
            font-size: 13px;
        }

        .box {
            border: 1px solid #000;
            padding: 8px;
            margin-top: 5px;
        }
    </style>
</head>

<body>

    {{-- KOP SURAT --}}
    <div class="kop">
        {{-- Optional Logo --}}
        {{-- <img src="{{ public_path('logo.png') }}" style="height:60px;"> --}}

        <div class="kop-title">{{ $profileUser->name }}</div>
        <div class="kop-sub">{{ $profileUser->alamat }}</div>
        <div class="kop-sub">
            Email: {{ $profileUser->email }} 
        </div>
    </div>

    <div class="line"></div>

    {{-- JUDUL --}}
    <h3 class="text-center fw-bold">NOTULEN RAPAT</h3>

    {{-- INFORMASI RAPAT --}}
    <table class="table-no-border">
        <tr><td width="30%">Judul Rapat</td><td>: {{ $agendaJanjiTemu->judul_rapat }}</td></tr>
        <tr><td>Tanggal</td><td>: {{ \Carbon\Carbon::parse($agendaJanjiTemu->tanggal)->translatedFormat('d F Y') }}</td></tr>
        <tr><td>Waktu</td><td>: {{ $agendaJanjiTemu->waktu }}</td></tr>
        <tr><td>Tempat</td><td>: {{ $agendaJanjiTemu->tempat }}</td></tr>
        <tr><td>Pimpinan</td><td>: {{ $agendaJanjiTemu->pimpinan_rapat }}</td></tr>
        <tr><td>Notulis</td><td>: {{ $agendaJanjiTemu->notulis }}</td></tr>
    </table>

    {{-- PESERTA --}}
    <div class="section-title">Daftar Hadir Peserta</div>
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
                            <img src="{{ $ttdPath }}" style="width: 60px;">
                        @else
                            -
                        @endif
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

    {{-- KEPUTUSAN --}}
    <div class="section-title">Keputusan Rapat</div>
    <div class="box">
        {!! nl2br(e($agendaJanjiTemu->keputusan_rapat)) !!}
    </div>

    {{-- TINDAK LANJUT --}}
    @if($agendaJanjiTemu->tindakLanjut && $agendaJanjiTemu->tindakLanjut->count())
        <div class="section-title">Tindak Lanjut</div>
        <table>
            <thead>
                <tr class="text-center fw-bold">
                    <th width="5%">No</th>
                    <th>Tindakan</th>
                    <th>Pelaksana</th>
                    <th width="18%">Target</th>
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

    {{-- FOOTER / TTD --}}
    <div style="margin-top: 50px; width: 100%;">
        <div class="text-right">
            {{ $agendaJanjiTemu->nama_kota ?? '' }},
            {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
        </div>

        <br><br><br>

        <div class="text-right fw-bold">
            {{ $agendaJanjiTemu->pimpinan_rapat }}
        </div>
    </div>

</body>
</html>