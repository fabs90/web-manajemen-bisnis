<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Faktur Penjualan - {{ $faktur->kode_faktur }}</title>
    <style>
        @page { margin: 0.8cm; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        /* Border Styling */
        .border-all th, .border-all td {
            border: 1px solid #333;
            padding: 8px 6px;
            word-wrap: break-word;
            vertical-align: middle;
        }

        /* Utility Classes */
        .table-no-border td { border: none !important; padding: 3px 0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }

        /* Design Elements */
        .line {
            border-bottom: 2px solid #000;
            margin: 10px 0 20px;
        }

        .title-faktur {
            font-size: 20px;
            letter-spacing: 6px;
            margin-bottom: 5px;
            color: #000;
        }

        .header-bg {
            background-color: #f2f2f2;
        }

        .info-box {
            margin-bottom: 25px;
        }

        .footer-signature {
            margin-top: 60px;
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

    {{-- KOP SURAT --}}
    <table class="table-no-border" style="margin-bottom: 5px;">
        <tr>
            <td width="15%">
                @if (isset($profileUser->logo_perusahaan) && $profileUser->logo_perusahaan)
                    @php
                        $logoPath = public_path('storage/' . $profileUser->logo_perusahaan);
                        if(file_exists($logoPath)){
                            $logoBase64 = base64_encode(file_get_contents($logoPath));
                            $logoMime = mime_content_type($logoPath);
                        }
                    @endphp
                    @if(isset($logoBase64))
                        <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}" style="height:65px;">
                    @endif
                @endif
            </td>
            <td width="70%" class="text-center" style="vertical-align: middle;">
                {{-- Nama Perusahaan Capslock & Solid --}}
                <div style="font-size:20px; font-weight:bold; color: #000; text-transform: uppercase; letter-spacing: 1px;">
                    {{ $profileUser->name ?? 'NAMA PERUSAHAAN' }}
                </div>
                <div style="font-size:10px; color: #333;">{{ $profileUser->alamat ?? 'Alamat Lengkap Perusahaan' }}</div>
                <div style="font-size:10px; color: #333;">Telp: {{ $profileUser->nomor_telepon ?? '-' }} | Email: {{ $profileUser->email ?? '-' }}</div>
            </td>
            <td width="15%"></td>
        </tr>
    </table>

    <div class="line"></div>

    {{-- JUDUL & NOMOR --}}
    {{-- <div class="text-center" style="margin-bottom: 25px;">
        <div class="title-faktur fw-bold text-uppercase">FAKTUR</div>
        <div style="font-size: 12px; color: #000;">Nomor Dokumen: <span class="fw-bold">{{ $faktur->kode_faktur }}</span></div>
    </div> --}}
   
<div class="text-center" style="margin-bottom: 25px;">
    <div class="title-faktur fw-bold text-uppercase">FAKTUR</div>
    {{-- Format: (Nomor Urut)/F/(Nama Perusahaan)/(Bulan)/(Tahun) --}}
    <div style="font-size: 12px; color: #000;">
        Nomor: <span class="fw-bold">{{ $faktur->kode_faktur }}/F/{{ str_replace(' ', '', strtoupper($profileUser->name ?? 'DIGITRANS')) }}/{{ \Carbon\Carbon::parse($faktur->tanggal_faktur)->format('m/Y') }}</span>
    </div>
</div>

    {{-- INFORMASI KEPADA & REFERENSI --}}
    <div class="info-box">
        <table class="table-no-border">
            <tr>
                <td width="12%" class="fw-bold">Kepada</td>
                <td width="43%">: {{ $faktur->suratPengirimanBarang->pesananPembelian->pelanggan->nama }}</td>
                <td width="18%" class="fw-bold">Nomor Pesanan</td>
                <td width="27%">: {{ $faktur->suratPengirimanBarang->pesananPembelian->nomor_pesanan_pembelian ?? '-' }}</td>
            </tr>
            <tr>
                <td class="fw-bold">Alamat</td>
                <td>: {{ $faktur->suratPengirimanBarang->pesananPembelian->pelanggan->alamat }}</td>
                <td class="fw-bold">Nomor SPB</td>
                <td>: {{ $faktur->suratPengirimanBarang->nomor_pengiriman_barang }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td class="fw-bold">Tanggal</td>
                <td>: {{ \Carbon\Carbon::parse($faktur->tanggal_faktur)->translatedFormat('d F Y') }}</td>
            </tr>
        </table>
    </div>

    {{-- TABEL ITEM --}}
    <table class="border-all">
        <thead>
            <tr class="text-center fw-bold header-bg">
                <th width="5%">NO</th>
                <th width="12%">PESAN</th>
                <th width="12%">KIRIM</th>
                <th>DESKRIPSI BARANG</th>
                <th width="18%">HARGA (Rp)</th>
                <th width="18%">TOTAL (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($faktur->fakturPenjualanDetail as $index => $item)
                @php
                    $qtyOrder = $item->suratPengirimanBarangDetail->pesananPembelianDetail->kuantitas ?? 0;
                    $qtyKirim = $item->suratPengirimanBarangDetail->jumlah_dikirim ?? 0;
                    $grandTotal += $item->total;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $qtyOrder }} </td>
                    <td class="text-center">{{ $qtyKirim }} </td>
                    <td style="padding-left: 10px;">{{ $item->suratPengirimanBarangDetail->pesananPembelianDetail->nama_barang }}</td>
                    <td class="text-right">{{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="text-right fw-bold">{{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="header-bg">
                <td colspan="5" class="text-right fw-bold" style="padding-right: 15px;">TOTAL PEMBAYARAN</td>
                <td class="text-right fw-bold" style="font-size: 12px;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 15px; font-style: italic; color: #333;">
        Metode Pengiriman: <span class="fw-bold text-uppercase">{{ $faktur->suratPengirimanBarang->jenis_pengiriman ?? '-' }}</span>
    </div>

    {{-- TANDA TANGAN (Penyesuaian Posisi) --}}
    <table class="table-no-border footer-signature">
        <tr>
            <td width="60%"></td>
            <td class="text-center">
                <div style="margin-bottom: 10px;">Hormat Kami,</div>
                <div class="fw-bold text-uppercase" style="margin-bottom: 65px;">Bagian Penjualan</div>
                <div class="fw-bold text-uppercase" style="text-decoration: underline; font-size: 12px;">
                    {{ $faktur->nama_bagian_penjualan ?? '....................' }}
                </div>
                <div style="font-size: 9px; margin-top: 5px;">(Tanda Tangan & Cap Resmi)</div>
            </td>
        </tr>
    </table>

</body>
</html>