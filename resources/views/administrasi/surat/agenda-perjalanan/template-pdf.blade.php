<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Agenda Perjalanan - {{ $agendaPerjalanan->nama_pelaksana ?? '' }}</title>
    <style>
        @page { margin: 20mm; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color: #222; }
        .header { width: 100%; display: flex; align-items: center; margin-bottom: 8px; }
        .logo { width: 110px; }
        .company { margin-left: 12px; }
        .title { text-align: center; margin: 8px 0 12px 0; }
        .title h2 { margin: 0; font-size: 16px; }
        .meta { width: 100%; margin-bottom: 12px; }
        .meta .left, .meta .right { display: inline-block; vertical-align: top; }
        .meta .left { width: 60%; }
        .meta .right { width: 38%; text-align: right; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 6px 8px; border: 1px solid #ccc; }
        table th { background: #f3f3f3; font-weight: 700; }
        .section-title { background: #f7f7f7; padding: 6px 8px; margin-top: 12px; margin-bottom: 6px; font-weight: 700; }
        .no-border td { border: none; padding: 2px 0; }
        .small { font-size: 11px; }
        .right { text-align: right; }
        .signature { width: 48%; display: inline-block; text-align: center; margin-top: 28px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">
            <div style="font-weight:700">{{ $userProfile->name ?? config('app.name') }}</div>
            <div class="small">{{ $userProfile->alamat ?? '' }}</div>
            <div class="small">{{ $userProfile->nomor_telepon ?? '' }}</div>
        </div>
    </div>

    <div class="title">
        <h2>AGENDA PERJALANAN / TRAVEL ITINERARY</h2>
        <div class="small">Tanggal: {{
            optional($agendaPerjalanan)->tanggal_mulai ?

                \Carbon\Carbon::parse($agendaPerjalanan->tanggal_mulai)->format('d M Y') . ' - ' .
                \Carbon\Carbon::parse($agendaPerjalanan->tanggal_selesai)->format('d M Y')
            : '-' }}</div>
    </div>

    <table class="no-border" style="margin-bottom:8px;">
        <tr>
            <td style="width:60%; vertical-align: top;">
                <table style="width:100%">
                    <tr>
                        <td style="width:35%;"><strong>Nama Pelaksana</strong></td>
                        <td>{{ $agendaPerjalanan->nama_pelaksana ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Jabatan</strong></td>
                        <td>{{ $agendaPerjalanan->jabatan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tujuan</strong></td>
                        <td>{{ $agendaPerjalanan->tujuan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Keperluan</strong></td>
                        <td>{{ $agendaPerjalanan->keperluan ?? '-' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width:38%; vertical-align: top;">
                <table style="width:100%">
                    <tr>
                        <td style="width:40%;"><strong>Disiapkan Oleh</strong></td>
                        <td>{{ $agendaPerjalanan->disiapkan_oleh ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Disiapkan</strong></td>
                        <td>{{ optional($agendaPerjalanan->tanggal_disiapkan) ? \Carbon\Carbon::parse($agendaPerjalanan->tanggal_disiapkan)->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Disetujui Oleh</strong></td>
                        <td>{{ $agendaPerjalanan->disetujui_oleh ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Disetujui</strong></td>
                        <td>{{ optional($agendaPerjalanan->tanggal_disetujui) ? \Carbon\Carbon::parse($agendaPerjalanan->tanggal_disetujui)->format('d M Y') : '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="section-title">JADWAL DETAIL</div>

    @if($agendaPerjalanan->agendaPerjalananDetail && $agendaPerjalanan->agendaPerjalananDetail->count())
        @foreach($agendaPerjalanan->agendaPerjalananDetail as $dIndex => $detail)
            <table style="margin-bottom:8px;">
                <thead>
                    <tr>
                        <th style="width:12%;">Hari</th>
                        <th style="width:18%;">Tanggal</th>
                        <th style="width:12%;">Waktu</th>
                        <th>Kegiatan</th>
                        <th style="width:20%;">Lokasi / PIC</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="small">{{ $dIndex + 1 }}</td>
                        <td class="small">{{ optional($detail->tanggal) ? \Carbon\Carbon::parse($detail->tanggal)->format('d M Y') : '-' }}</td>
                        <td class="small">{{ $detail->waktu ?? '-' }}</td>
                        <td class="small">{{ $detail->kegiatan ?? '-' }}</td>
                        <td class="small">{{ $detail->lokasi ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    @else
        <div class="small">Tidak ada jadwal detail.</div>
    @endif

    <div class="section-title">INFORMASI TRANSPORTASI & AKOMODASI</div>

    <table style="margin-bottom:8px;">
        <thead>
            <tr>
                <th>Penerbangan Pergi</th>
                <th>Penerbangan Pulang</th>
                <th>Kode Booking</th>
                <th>Transportasi Lokal</th>
            </tr>
        </thead>
        <tbody>
            @php $transport = $agendaPerjalanan->agendaPerjalananTransportasi->first() ?? null; @endphp
            <tr>
                <td class="small">{{ $transport?->penerbangan_pergi ?? '-' }}</td>
                <td class="small">{{ $transport?->penerbangan_pulang ?? '-' }}</td>
                <td class="small">{{ $transport?->kode_booking ?? '-' }}</td>
                <td class="small">{{ $transport?->transportasi_lokal ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    <table style="margin-bottom:8px;">
        <thead>
            <tr>
                <th>Hotel</th>
                <th>Alamat</th>
                <th>Telepon</th>
                <th>Check-In</th>
                <th>Check-Out</th>
                <th>Booking No.</th>
            </tr>
        </thead>
        <tbody>
            @php $akom = $agendaPerjalanan->agendaPerjalananAkomodasi->first() ?? null; @endphp
            <tr>
                <td class="small">{{ $akom?->hotel ?? '-' }}</td>
                <td class="small">{{ $akom?->alamat ?? '-' }}</td>
                <td class="small">{{ $akom?->telepon ?? '-' }}</td>
                <td class="small">{{ optional($akom?->check_in) ? \Carbon\Carbon::parse($akom->check_in)->format('d M Y') : '-' }}</td>
                <td class="small">{{ optional($akom?->check_out) ? \Carbon\Carbon::parse($akom->check_out)->format('d M Y') : '-' }}</td>
                <td class="small">{{ $akom?->booking_number ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">KONTAK PENTING</div>
    <table style="margin-bottom:8px;">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Telepon</th>
                <th>Jenis</th>
            </tr>
        </thead>
        <tbody>
            @forelse($agendaPerjalanan->agendaPerjalananKontak as $kontak)
                <tr>
                    <td class="small">{{ $kontak->nama ?? '-' }}</td>
                    <td class="small">{{ $kontak->telepon ?? '-' }}</td>
                    <td class="small">{{ $kontak->jenis ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="small">-</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">RINCIAN BIAYA</div>
    <table style="width:50%; margin-bottom:8px;">
        <tbody>
            <tr>
                <td style="width:50%">Transport</td>
                <td class="right">{{ number_format($agendaPerjalanan->transport ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Akomodasi</td>
                <td class="right">{{ number_format($agendaPerjalanan->akomodasi ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Konsumsi</td>
                <td class="right">{{ number_format($agendaPerjalanan->konsumsi ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Lain-lain</td>
                <td class="right">{{ number_format($agendaPerjalanan->lain_lain ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Total Biaya</th>
                <th class="right">{{ number_format($agendaPerjalanan->total_biaya ?? ($agendaPerjalanan->transport + $agendaPerjalanan->akomodasi + $agendaPerjalanan->konsumsi + $agendaPerjalanan->lain_lain ?? 0), 2, ',', '.') }}</th>
            </tr>
        </tbody>
    </table>

    <div class="section-title">CATATAN</div>
    <div class="small" style="margin-bottom:18px;">{{ $agendaPerjalanan->catatan ?? '-' }}</div>

    <div style="margin-top:24px;">
        <div class="signature">
            <div>Disiapkan Oleh</div>
            <div style="height:60px"></div>
            <div style="font-weight:700">{{ $agendaPerjalanan->disiapkan_oleh ?? '-' }}</div>
            <div class="small">{{ optional($agendaPerjalanan->tanggal_disiapkan) ? \Carbon\Carbon::parse($agendaPerjalanan->tanggal_disiapkan)->format('d M Y') : '-' }}</div>
        </div>

        <div style="width:4%; display:inline-block;"></div>

        <div class="signature">
            <div>Disetujui Oleh</div>
            <div style="height:60px"></div>
            <div style="font-weight:700">{{ $agendaPerjalanan->disetujui_oleh ?? '-' }}</div>
            <div class="small">{{ optional($agendaPerjalanan->tanggal_disetujui) ? \Carbon\Carbon::parse($agendaPerjalanan->tanggal_disetujui)->format('d M Y') : '-' }}</div>
        </div>
    </div>

</body>
</html>
