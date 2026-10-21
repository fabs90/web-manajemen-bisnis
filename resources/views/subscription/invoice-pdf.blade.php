<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kuitansi / Bukti Pembayaran Langganan - {{ $payment?->reference ?? 'SUB-' . $subscription->id }}</title>
    <style>
        @page {
            margin: 1cm 1.5cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #000;
        }

        /* STYLING KOP SURAT */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
        }

        .kop-table td {
            vertical-align: middle;
        }

        .logo-cell {
            text-align: left;
        }

        .text-cell {
            text-align: center;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .line {
            border-top: 3px solid #000;
            margin-top: 5px;
            margin-bottom: 20px;
        }

        .title-doc {
            font-size: 16px;
            letter-spacing: 2px;
            margin-bottom: 3px;
            color: #000;
            text-align: center;
            font-weight: bold;
        }

        .subtitle-doc {
            font-size: 11px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        /* TABEL INFO & DATA */
        .table-no-border {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .table-no-border td {
            border: none !important;
            padding: 3px 0;
            vertical-align: top;
            font-size: 11px;
        }

        .fw-bold {
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* TABEL BORDER SEMUA (Sesuai Gaya Administrasi) */
        .border-all {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .border-all th,
        .border-all td {
            border: 1px solid #000;
            padding: 7px 8px;
            font-size: 11px;
            vertical-align: middle;
        }

        .header-bg {
            background-color: #f2f2f2;
        }

        /* BADGE STATUS */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            font-weight: bold;
            font-size: 10px;
            border-radius: 3px;
            border: 1px solid #000;
            text-transform: uppercase;
        }

        .status-paid {
            background-color: #e6f4ea;
            color: #137333;
            border-color: #137333;
        }

        .status-pending {
            background-color: #fef7e0;
            color: #b06000;
            border-color: #b06000;
        }

        /* TANDA TANGAN (Sesuai Gaya Administrasi Surat) */
        .footer-signature {
            margin-top: 40px;
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .footer-signature td {
            border: none;
            vertical-align: top;
        }

        .notes-box {
            border: 1px dashed #666;
            padding: 8px 12px;
            font-size: 10px;
            color: #333;
            background-color: #fafafa;
            margin-top: 15px;
        }
    </style>
</head>

<body>

    @php
        $latestPayment = $payment ?? $subscription->payments?->sortByDesc('id')->first();
        $isPaid = $latestPayment?->status?->value === 'paid' || $subscription->status?->value === 'active';
        $statusValue = $latestPayment?->status?->value ?? ($subscription->status?->value ?? 'pending');
        $invoiceNumber = $latestPayment?->reference ?? ('INV/' . $subscription->created_at->format('Ymd') . '/' . str_pad($subscription->id, 5, '0', STR_PAD_LEFT));
        $plan = $subscription->plan;

        // Kop menggunakan logo profil pengguna / perusahaan jika tersedia
        $logoBase64 = image_to_base64($user->logo_perusahaan ?? null);
        $ttdBase64 = image_to_base64($user->ttd_pemimpin ?? null);
    @endphp

    {{-- KOP SURAT (Konsisten dengan Template Administrasi) --}}
    <table class="kop-table">
        <tr>
            @if ($logoBase64)
                <td width="15%" class="logo-cell">
                    <img src="{{ $logoBase64 }}" height="65">
                </td>
                <td width="70%" class="text-cell">
                    <strong style="font-size: 17px;" class="uppercase">{{ $user->name ?? config('app.name', 'WEB PEMBUKUAN') }}</strong><br>
                    <span style="font-size: 11px;">
                        {{ $user->alamat ?? 'Sistem Pembukuan & Pengelolaan Keuangan Digital' }}<br>
                        Telp: {{ $user->nomor_telepon ?? '-' }} | Email: {{ $user->email ?? '-' }}
                    </span>
                </td>
                <td width="15%"></td>
            @else
                <td width="100%" class="text-cell">
                    <strong style="font-size: 18px;" class="uppercase">{{ $user->name ?? config('app.name', 'WEB PEMBUKUAN') }}</strong><br>
                    <span style="font-size: 11px;">
                        {{ $user->alamat ?? 'Sistem Pembukuan & Pengelolaan Keuangan Digital' }}<br>
                        Telp: {{ $user->nomor_telepon ?? '-' }} | Email: {{ $user->email ?? '-' }}
                    </span>
                </td>
            @endif
        </tr>
    </table>

    <div class="line"></div>

    {{-- JUDUL DAN NOMOR BUKTI TRANSAKSI --}}
    <div class="title-doc uppercase">BUKTI PEMBAYARAN LANGGANAN</div>
    <div class="subtitle-doc">
        Nomor Invoice: <span class="fw-bold">{{ $invoiceNumber }}</span>
    </div>

    {{-- INFORMASI KEPADA & DETAIL TRANSAKSI --}}
    <table class="table-no-border" style="margin-bottom: 15px;">
        <tr>
            <td width="16%" class="fw-bold">Diterbitkan Untuk</td>
            <td width="40%">: {{ $user->name }}</td>
            <td width="18%" class="fw-bold">Tanggal Tagihan</td>
            <td width="26%">: {{ $subscription->created_at->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Email / Kontak</td>
            <td>: {{ $user->email }} @if($user->nomor_telepon) ({{ $user->nomor_telepon }}) @endif</td>
            <td class="fw-bold">Tanggal Bayar</td>
            <td>: {{ $latestPayment?->paid_at ? $latestPayment->paid_at->translatedFormat('d F Y H:i') . ' WIB' : '-' }}</td>
        </tr>
        @if ($subscription->organization)
        <tr>
            <td class="fw-bold">Organisasi</td>
            <td>: {{ $subscription->organization->name }}</td>
            <td class="fw-bold">Metode / Gateway</td>
            <td>: {{ strtoupper($latestPayment?->provider ?? 'Midtrans') }}</td>
        </tr>
        @endif
        <tr>
            <td class="fw-bold">Status Pembayaran</td>
            <td>:
                @if ($statusValue === 'paid')
                    <span class="status-badge status-paid">LUNAS</span>
                @elseif ($statusValue === 'pending')
                    <span class="status-badge status-pending">MENUNGGU PEMBAYARAN</span>
                @else
                    <span class="status-badge">{{ strtoupper($statusValue) }}</span>
                @endif
            </td>
            <td class="fw-bold">ID Transaksi Gateway</td>
            <td style="font-family: monospace; font-size: 10px;">: {{ $latestPayment?->provider_transaction_id ?? ($latestPayment?->reference ?? '-') }}</td>
        </tr>
    </table>

    {{-- TABEL RINCIAN ITEM (Border Kotak Penuh Sesuai Administrasi) --}}
    <table class="border-all">
        <thead>
            <tr class="text-center fw-bold header-bg">
                <th width="6%">NO</th>
                <th width="48%">DESKRIPSI PAKET LAYANAN</th>
                <th width="20%">MASA AKTIF / DURASI</th>
                <th width="26%" class="text-right">JUMLAH (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>
                    <strong class="uppercase">{{ $plan?->name ?? 'Paket Langganan' }}</strong>
                    <div style="font-size: 10px; color: #444; margin-top: 2px;">
                        Tipe Penagihan: {{ ucfirst($plan?->billing_type?->value ?? 'One Time') }} &bull;
                        Cakupan: {{ ucfirst($plan?->scope?->value ?? 'Personal') }}
                    </div>
                    @if ($subscription->starts_at && $subscription->ends_at)
                        <div style="font-size: 10px; color: #000; margin-top: 2px;">
                            Masa Berlaku: {{ $subscription->starts_at->translatedFormat('d F Y') }} s/d {{ $subscription->ends_at->translatedFormat('d F Y') }}
                        </div>
                    @endif
                </td>
                <td class="text-center">
                    {{ $plan?->duration_days ? $plan->duration_days . ' Hari' : '-' }}
                </td>
                <td class="text-right fw-bold">
                    {{ number_format($latestPayment?->amount ?? ($plan?->price ?? 0), 0, ',', '.') }}
                </td>
            </tr>
            <tr class="header-bg">
                <td colspan="3" class="text-right fw-bold" style="padding-right: 12px;">SUBTOTAL</td>
                <td class="text-right fw-bold">
                    Rp {{ number_format($latestPayment?->amount ?? ($plan?->price ?? 0), 0, ',', '.') }}
                </td>
            </tr>
            <tr class="header-bg">
                <td colspan="3" class="text-right fw-bold" style="padding-right: 12px;">BIAYA LAYANAN / ADM</td>
                <td class="text-right fw-bold">Rp 0</td>
            </tr>
            <tr class="header-bg">
                <td colspan="3" class="text-right fw-bold" style="padding-right: 12px; font-size: 12px;">TOTAL PEMBAYARAN</td>
                <td class="text-right fw-bold" style="font-size: 12px;">
                    Rp {{ number_format($latestPayment?->amount ?? ($plan?->price ?? 0), 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- KETERANGAN / CATATAN --}}
    <div class="notes-box">
        <strong>Keterangan:</strong><br>
        1. Dokumen ini merupakan bukti sah transaksi berlangganan paket aplikasi {{ config('app.name', 'Web Pembukuan') }}.<br>
        2. Bukti pembayaran ini dihasilkan secara otomatis oleh sistem komputer terintegrasi gateway pembayaran resmi.
    </div>

    {{-- TANDA TANGAN (Sesuai Gaya Administrasi Surat / SPP / Faktur) --}}
    <table class="footer-signature">
        <tr>
            <td width="60%"></td>
            <td width="40%" class="text-center">
                {{ now()->translatedFormat('d F Y') }}<br>
                Hormat Kami,<br>
                <strong class="uppercase">{{ $user->name ?? config('app.name', 'PENGELOLA APLIKASI') }}</strong><br>
                @if ($ttdBase64)
                    <img src="{{ $ttdBase64 }}" style="height:55px; margin: 4px 0;">
                @else
                    <div style="height:55px;"></div>
                @endif
                <br>
                <strong style="text-decoration: underline;">{{ $user->nama_pimpinan ?? ($user->name ?? 'Bagian Keuangan') }}</strong><br>
                <span>Bagian Administrasi & Keuangan</span>
            </td>
        </tr>
    </table>

</body>

</html>
