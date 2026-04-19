<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Notulen Rapat - {{ $agendaJanjiTemu->judul_rapat ?? 'Dokumen' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: -1px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            word-wrap: break-word;
            vertical-align: top;
        }

        .table-no-border td {
            border: none !important;
            padding: 2px 0;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .bg-light {
            background-color: #f2f2f2;
        }

        .line {
            border-bottom: 3px solid #000;
            margin: 10px 0 15px;
        }
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
                    {{ $profileUser->name ?? 'Nama Perusahaan' }}</div>
                <div style="font-size:11px;">{{ $profileUser->alamat ?? 'Alamat Perusahaan' }}</div>
                <div style="font-size:11px;">Telp: {{ $profileUser->nomor_telepon ?? '-' }} | Email:
                    {{ $profileUser->email ?? '-' }}</div>
            </td>
            <td width="15%"></td>
        </tr>
    </table>

    <div class="line"></div>

    {{-- HEADER TABEL --}}
    <table>
        <tr class="bg-light">
            <td class="text-center fw-bold" style="font-size: 13px; padding: 8px;">NOTULEN RAPAT</td>
        </tr>
    </table>

    {{-- INFORMASI RAPAT --}}
    <table>
        <tr>
            <td width="18%" style="border-right: none;">Judul Rapat</td>
            <td width="32%" style="border-left: none;">: {{ $agendaJanjiTemu->judul_rapat }}</td>
            <td width="15%" style="border-right: none;">Tempat</td>
            <td width="35%" style="border-left: none;">: {{ $agendaJanjiTemu->tempat }}</td>
        </tr>
        <tr>
            <td style="border-right: none;">Tanggal</td>
            <td style="border-left: none;">:
                {{ \Carbon\Carbon::parse($agendaJanjiTemu->tanggal)->translatedFormat('d F Y') }}</td>
            <td style="border-right: none;">Pimpinan</td>
            <td style="border-left: none;">: {{ $agendaJanjiTemu->pemimpin_rapat }}</td>
        </tr>
        <tr>
            <td style="border-right: none;">Waktu</td>
            <td style="border-left: none;">: {{ $agendaJanjiTemu->waktu }}</td>
            <td style="border-right: none;">Notulis</td>
            <td style="border-left: none;">: {{ $agendaJanjiTemu->nama_notulis }}</td>
        </tr>
    </table>

    {{-- DAFTAR HADIR PESERTA --}}
    <table>
        <tr class="bg-light">
            <td class="fw-bold">Daftar Hadir Peserta:</td>
        </tr>
    </table>
    <table>
        <tr class="text-center fw-bold">
            <td width="8%">No</td>
            <td>Nama</td>
            <td width="30%">Jabatan</td>
            <td width="20%">Tanda Tangan</td>
        </tr>
        @if (isset($agendaJanjiTemu->pesertaRapat) && count($agendaJanjiTemu->pesertaRapat) > 0)
            @foreach ($agendaJanjiTemu->pesertaRapat as $i => $peserta)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $peserta->nama }}</td>
                    <td>{{ $peserta->jabatan }}</td>
                    <td class="text-center">
                        @if ($peserta->tanda_tangan)
                            @php
                                $path = storage_path('app/public/' . $peserta->tanda_tangan);
                            @endphp
                            @if (file_exists($path))
                                @php
                                    $data = base64_encode(file_get_contents($path));
                                    $mime = mime_content_type($path);
                                @endphp
                                <img src="data:{{ $mime }};base64,{{ $data }}" style="height: 40px;">
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="4" class="text-center">Tidak ada data peserta</td>
            </tr>
        @endif
    </table>

    {{-- AGENDA RAPAT --}}
    <table>
        <tr class="bg-light">
            <td class="fw-bold">AGENDA RAPAT:</td>
        </tr>
    </table>
    <table>
        <tr>
            <td>
                @if (isset($agendaJanjiTemu->rapatDetails) && count($agendaJanjiTemu->rapatDetails) > 0)
                    @foreach ($agendaJanjiTemu->rapatDetails as $i => $detail)
                        {{ $i + 1 }}. {{ $detail->judul_agenda }}<br>
                    @endforeach
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    {{-- PEMBAHASAN --}}
    <table>
        <tr class="bg-light">
            <td class="fw-bold">PEMBAHASAN:</td>
        </tr>
    </table>
    @if (isset($agendaJanjiTemu->rapatDetails) && count($agendaJanjiTemu->rapatDetails) > 0)
        @foreach ($agendaJanjiTemu->rapatDetails as $i => $detail)
            <table>
                <tr>
                    <td>
                        <div class="fw-bold">(Agenda {{ $i + 1 }})</div>
                        <div>Pembicara: {{ $detail->pembicara }}</div>
                        <div style="margin-top: 5px;">{!! nl2br(e($detail->pembahasan)) !!}</div>
                    </td>
                </tr>
            </table>
        @endforeach
    @endif

    {{-- KEPUTUSAN --}}
    <table>
        <tr class="bg-light">
            <td class="fw-bold">KEPUTUSAN RAPAT:</td>
        </tr>
    </table>
    <table>
        <tr>
            <td style="min-height: 50px;">{!! nl2br(e($agendaJanjiTemu->keputusan_rapat)) !!}</td>
        </tr>
    </table>

    {{-- TINDAK LANJUT --}}
    <table>
        <tr class="bg-light">
            <td class="fw-bold">TINDAK LANJUT (ACTION PLAN):</td>
        </tr>
    </table>
    <table>
        <tr class="text-center fw-bold">
            <td width="8%">No</td>
            <td>Tindakan</td>
            <td width="20%">Pelaksana</td>
            <td width="15%">Target</td>
            <td width="15%">Status</td>
        </tr>
        @if (isset($agendaJanjiTemu->tindakLanjutRapat) && count($agendaJanjiTemu->tindakLanjutRapat) > 0)
            @foreach ($agendaJanjiTemu->tindakLanjutRapat as $i => $tl)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $tl->tindakan }}</td>
                    <td>{{ $tl->pelaksana }}</td>
                    <td class="text-center">
                        {{ $tl->target_selesai ? \Carbon\Carbon::parse($tl->target_selesai)->format('d-m-Y') : '-' }}
                    </td>
                    <td class="text-center">{{ $tl->status }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="5" class="text-center">Data tindak lanjut tidak ditemukan.</td>
            </tr>
        @endif
    </table>

    {{-- FOOTER / TTD --}}
    <table style="margin-top: 20px; page-break-inside: avoid;">
        <tr>
            <td width="50%" class="text-center" style="height:110px;">
                <br>
                <div class="fw-bold text-center">Pemimpin Rapat,</div>

                {{-- Bagian TTD Pemimpin --}}
                <div class="text-center">
                    @if (isset($agendaJanjiTemu->ttd_pemimpin) && $agendaJanjiTemu->ttd_pemimpin)
                        @php
                            $ttdPath = storage_path('app/public/' . $agendaJanjiTemu->ttd_pemimpin);
                        @endphp
                        @if (file_exists($ttdPath))
                            @php
                                $ttdData = base64_encode(file_get_contents($ttdPath));
                                $ttdMime = mime_content_type($ttdPath);
                            @endphp
                            <img src="data:{{ $ttdMime }};base64,{{ $ttdData }}" style="height: 50px;">
                        @endif
                    @endif
                </div>

                <div class="text-center fw-bold">{{ $agendaJanjiTemu->pemimpin_rapat }}</div>
            </td>

            <td width="50%" class="text-center" style="height: 110px;">
                <div>{{ $agendaJanjiTemu->nama_kota ?? 'Jakarta' }},
                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                <div class="fw-bold">Notulis,</div>
                @if (isset($agendaJanjiTemu->ttd_notulis) && $agendaJanjiTemu->ttd_notulis)
                    @php
                        $ttdPath = storage_path('app/public/' . $agendaJanjiTemu->ttd_notulis);
                    @endphp
                    @if (file_exists($ttdPath))
                        @php
                            $ttdData = base64_encode(file_get_contents($ttdPath));
                            $ttdMime = mime_content_type($ttdPath);
                        @endphp
                        <img src="data:{{ $ttdMime }};base64,{{ $ttdData }}" style="height: 50px;">
                    @endif
                @endif
                <br>
                <div class="fw-bold">{{ $agendaJanjiTemu->nama_notulis }}</div>
            </td>
        </tr>
    </table>

</body>

</html>
