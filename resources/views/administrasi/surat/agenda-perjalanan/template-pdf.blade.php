<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Agenda Perjalanan - {{ $agendaPerjalanan->nama_pelaksana ?? '' }}</title>
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
        
        .text-right {
            text-align: right;
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
                @if (isset($userProfile->logo_perusahaan) && $userProfile->logo_perusahaan)
                    @php
                        $logoPath = storage_path('app/public/' . $userProfile->logo_perusahaan);
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
                    {{ $userProfile->name ?? config('app.name') }}</div>
                <div style="font-size:11px;">{{ $userProfile->alamat ?? '' }}</div>
                <div style="font-size:11px;">Telp: {{ $userProfile->nomor_telepon ?? '-' }} | Email:
                    {{ $userProfile->email ?? '-' }}</div>
            </td>
            <td width="15%"></td>
        </tr>
    </table>

    <div class="line"></div>

    {{-- HEADER TABEL --}}
    <table>
        <tr class="bg-light">
            <td class="text-center fw-bold" style="font-size: 13px; padding: 8px;">AGENDA PERJALANAN / TRAVEL ITINERARY</td>
        </tr>
    </table>

    {{-- INFORMASI UTAMA --}}
    <table>
        <tr>
            <td width="18%" style="border-right: none;">Nama Pelaksana</td>
            <td width="32%" style="border-left: none;">: {{ $agendaPerjalanan->nama_pelaksana ?? '-' }}</td>
            <td width="18%" style="border-right: none;">Disiapkan Oleh</td>
            <td width="32%" style="border-left: none;">: {{ $agendaPerjalanan->disiapkan_oleh ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border-right: none;">Jabatan</td>
            <td style="border-left: none;">: {{ $agendaPerjalanan->jabatan ?? '-' }}</td>
            <td style="border-right: none;">Tgl Disiapkan</td>
            <td style="border-left: none;">: {{ optional($agendaPerjalanan->tanggal_disiapkan) ? \Carbon\Carbon::parse($agendaPerjalanan->tanggal_disiapkan)->format('d M Y') : '-' }}</td>
        </tr>
        <tr>
            <td style="border-right: none;">Tujuan</td>
            <td style="border-left: none;">: {{ $agendaPerjalanan->tujuan ?? '-' }}</td>
            <td style="border-right: none;">Disetujui Oleh</td>
            <td style="border-left: none;">: {{ $agendaPerjalanan->disetujui_oleh ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border-right: none;">Keperluan</td>
            <td style="border-left: none;">: {{ $agendaPerjalanan->keperluan ?? '-' }}</td>
            <td style="border-right: none;">Tgl Disetujui</td>
            <td style="border-left: none;">: {{ optional($agendaPerjalanan->tanggal_disetujui) ? \Carbon\Carbon::parse($agendaPerjalanan->tanggal_disetujui)->format('d M Y') : '-' }}</td>
        </tr>
        <tr>
            <td style="border-right: none;">Tanggal Perjalanan</td>
            <td style="border-left: none;">: {{ optional($agendaPerjalanan)->tanggal_mulai ? \Carbon\Carbon::parse($agendaPerjalanan->tanggal_mulai)->format('d M Y') . ' s/d ' . \Carbon\Carbon::parse($agendaPerjalanan->tanggal_selesai)->format('d M Y') : '-' }}</td>
            <td style="border-right: none;"></td>
            <td style="border-left: none;"></td>
        </tr>
    </table>

    {{-- JADWAL DETAIL --}}
    <table>
        <tr class="bg-light">
            <td class="fw-bold" colspan="5">JADWAL DETAIL:</td>
        </tr>
        <tr class="text-center fw-bold">
            <td width="8%">Hari</td>
            <td width="15%">Tanggal</td>
            <td width="12%">Waktu</td>
            <td>Kegiatan</td>
            <td width="20%">Lokasi / PIC</td>
        </tr>
        @if($agendaPerjalanan->agendaPerjalananDetail && $agendaPerjalanan->agendaPerjalananDetail->count())
            @foreach($agendaPerjalanan->agendaPerjalananDetail as $dIndex => $detail)
                <tr>
                    <td class="text-center">{{ $dIndex + 1 }}</td>
                    <td class="text-center">{{ optional($detail->tanggal) ? \Carbon\Carbon::parse($detail->tanggal)->format('d M Y') : '-' }}</td>
                    <td class="text-center">{{ $detail->waktu ?? '-' }}</td>
                    <td>{{ $detail->kegiatan ?? '-' }}</td>
                    <td>{{ $detail->lokasi ?? '-' }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="5" class="text-center">Tidak ada jadwal detail.</td>
            </tr>
        @endif
    </table>

    {{-- INFORMASI TRANSPORTASI --}}
    <table>
        <tr class="bg-light">
            <td class="fw-bold" colspan="4">INFORMASI TRANSPORTASI:</td>
        </tr>
        <tr class="text-center fw-bold">
            <td>Penerbangan Pergi</td>
            <td>Penerbangan Pulang</td>
            <td>Kode Booking</td>
            <td>Transportasi Lokal</td>
        </tr>
        @php $transport = $agendaPerjalanan->agendaPerjalananTransportasi->first() ?? null; @endphp
        <tr>
            <td class="text-center">{{ $transport?->penerbangan_pergi ?? '-' }}</td>
            <td class="text-center">{{ $transport?->penerbangan_pulang ?? '-' }}</td>
            <td class="text-center">{{ $transport?->kode_booking ?? '-' }}</td>
            <td class="text-center">{{ $transport?->transportasi_lokal ?? '-' }}</td>
        </tr>
    </table>

    {{-- AKOMODASI --}}
    <table>
        <tr class="bg-light">
            <td class="fw-bold" colspan="6">INFORMASI AKOMODASI:</td>
        </tr>
        <tr class="text-center fw-bold">
            <td>Hotel</td>
            <td>Alamat</td>
            <td>Telepon</td>
            <td>Check-In</td>
            <td>Check-Out</td>
            <td>Booking No.</td>
        </tr>
        @php $akom = $agendaPerjalanan->agendaPerjalananAkomodasi->first() ?? null; @endphp
        <tr>
            <td class="text-center">{{ $akom?->hotel ?? '-' }}</td>
            <td class="text-center">{{ $akom?->alamat ?? '-' }}</td>
            <td class="text-center">{{ $akom?->telepon ?? '-' }}</td>
            <td class="text-center">{{ optional($akom?->check_in) ? \Carbon\Carbon::parse($akom->check_in)->format('d M Y') : '-' }}</td>
            <td class="text-center">{{ optional($akom?->check_out) ? \Carbon\Carbon::parse($akom->check_out)->format('d M Y') : '-' }}</td>
            <td class="text-center">{{ $akom?->booking_number ?? '-' }}</td>
        </tr>
    </table>

    {{-- KONTAK PENTING --}}
    <table>
        <tr class="bg-light">
            <td class="fw-bold" colspan="3">KONTAK PENTING:</td>
        </tr>
        <tr class="text-center fw-bold">
            <td>Nama</td>
            <td>Telepon</td>
            <td>Jenis</td>
        </tr>
        @forelse($agendaPerjalanan->agendaPerjalananKontak as $kontak)
            <tr>
                <td class="text-center">{{ $kontak->nama ?? '-' }}</td>
                <td class="text-center">{{ $kontak->telepon ?? '-' }}</td>
                <td class="text-center">{{ $kontak->jenis ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center">Tidak ada data kontak.</td></tr>
        @endforelse
    </table>

    {{-- RINCIAN BIAYA --}}
    <table>
        <tr class="bg-light">
            <td class="fw-bold" colspan="2">RINCIAN BIAYA:</td>
        </tr>
        <tr>
            <td width="50%">Transport</td>
            <td class="text-right">Rp {{ number_format($agendaPerjalanan->transport ?? 0, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Akomodasi</td>
            <td class="text-right">Rp {{ number_format($agendaPerjalanan->akomodasi ?? 0, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Konsumsi</td>
            <td class="text-right">Rp {{ number_format($agendaPerjalanan->konsumsi ?? 0, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Lain-lain</td>
            <td class="text-right">Rp {{ number_format($agendaPerjalanan->lain_lain ?? 0, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Total Biaya</td>
            <td class="fw-bold text-right">Rp {{ number_format($agendaPerjalanan->total_biaya ?? ($agendaPerjalanan->transport + $agendaPerjalanan->akomodasi + $agendaPerjalanan->konsumsi + $agendaPerjalanan->lain_lain ?? 0), 2, ',', '.') }}</td>
        </tr>
    </table>

    {{-- CATATAN --}}
    <table>
        <tr class="bg-light">
            <td class="fw-bold">CATATAN:</td>
        </tr>
        <tr>
            <td style="min-height: 50px;">{!! nl2br(e($agendaPerjalanan->catatan ?? '-')) !!}</td>
        </tr>
    </table>

    {{-- FOOTER / TTD --}}
    <table style="margin-top: 20px; page-break-inside: avoid;">
        <tr>
            <td width="50%" class="text-center" style="height:110px;">
                <br>
                <div class="fw-bold text-center">Disiapkan Oleh,</div>
                <div style="height: 50px;">
                </div>
                <div class="text-center fw-bold">{{ $agendaPerjalanan->disiapkan_oleh ?? '-' }}</div>
                <div>{{ optional($agendaPerjalanan->tanggal_disiapkan) ? \Carbon\Carbon::parse($agendaPerjalanan->tanggal_disiapkan)->format('d M Y') : '-' }}</div>
            </td>

            <td width="50%" class="text-center" style="height: 110px;">
                <br>
                <div class="fw-bold text-center">Disetujui Oleh,</div>
                <div style="height: 50px;">
                </div>
                <div class="text-center fw-bold">{{ $agendaPerjalanan->disetujui_oleh ?? '-' }}</div>
                <div>{{ optional($agendaPerjalanan->tanggal_disetujui) ? \Carbon\Carbon::parse($agendaPerjalanan->tanggal_disetujui)->format('d M Y') : '-' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
