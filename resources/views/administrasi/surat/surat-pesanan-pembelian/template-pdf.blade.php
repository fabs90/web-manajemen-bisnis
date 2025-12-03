<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pesanan Pembelian</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; }
        .fw-bold { font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #000; padding: 8px; }
        .no-border td, .no-border th { border: none !important; }
        .mb-3 { margin-bottom: 15px; }
        .mb-4 { margin-bottom: 25px; }
        .uppercase { text-transform: uppercase; }
    </style>
</head>
<body>

    {{-- Kop Surat Perusahaan --}}
    <table width="100%" class="no-border mb-4">
        <tr>
            <td class="fw-bold" style="font-size: 18px;">
                {{ $profileUser->name ?? 'NAMA PERUSAHAAN' }}
            </td>
        </tr>
        <tr>
            <td>{{ $profileUser->alamat ?? 'Alamat Perusahaan' }}</td>
        </tr>
        <tr>
            <td>{{ $profileUser->email ?? 'Email Perusahaan' }} | {{ $profileUser->telepon ?? 'Telepon' }}</td>
        </tr>
    </table>

    <h3 class="text-center fw-bold mb-3 uppercase">SURAT PESANAN PEMBELIAN</h3>

    {{-- Nomor dan Tanggal Surat --}}
    <table width="100%" class="no-border mb-4">
        <tr>
            <td width="60%"></td>
            <td width="40%">
                <strong>Nomor:</strong> ({{ $data->nomor_pesanan_pembelian ?? '___' }})/PP/{{ $profileUser->name ?? 'PERUSAHAAN' }}/{{ date('m/Y') }} <br>
                <strong>Tanggal:</strong> {{ $data->tanggal_pesanan_pembelian ?? date('d/m/Y') }}
            </td>
        </tr>
    </table>

    {{-- Kepada & Rencana Pengiriman --}}
    <p class="mb-3">
        Kepada Yth.<br>
        <strong>{{ $data->pelanggan->nama ?? '-' }}</strong><br>
        {{ $data->pelanggan->alamat ?? '-' }}<br>
        {{ $data->pelanggan->kontak ?? '' }}
    </p>

    <p class="mb-4">
        Dengan hormat,<br>
        Mohon kiranya Saudara dapat mengirimkan barang-barang berikut ini paling lambat tanggal
        <strong>{{ $data->tanggal_kirim_pesanan_pembelian ?? '(tanggal/bulan/tahun)' }}</strong>:
    </p>

    {{-- Tabel Detail Barang --}}
    <table class="table">
        <thead style="background-color: #f0f0f0;">
            <tr class="text-center">
                <th width="5%">No</th>
                <th width="15%">Kuantitas</th>
                <th width="45%">Nama Barang</th>
                <th width="15%">Harga Satuan</th>
                <th width="20%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data->pesananPembelianDetail as $detail)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $detail->kuantitas }} {{ $detail->satuan ?? '' }}</td>
                    <td>{{ $detail->nama_barang }}</td>
                    <td class="text-right">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada item</td>
                </tr>
            @endforelse

            {{-- Baris kosong agar tetap 8 baris (opsional, sesuai contoh klasik) --}}
            @for($i = count($data->pesananPembelianDetail) + 1; $i <= 8; $i++)
                <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
            @endfor

            {{-- Total --}}
            <tr>
                <td colspan="4" class="text-right fw-bold" style="font-size: 13px;">TOTAL</td>
                <td class="text-right fw-bold" style="font-size: 13px;">
                    Rp {{ number_format($data->pesananPembelianDetail->sum('total'), 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Catatan (opsional) --}}
    @if($data->catatan)
        <p><strong>Catatan:</strong><br>{{ nl2br(e($data->catatan)) }}</p>
    @endif

    {{-- Tanda Tangan --}}
    <table width="100%" class="no-border" style="margin-top: 50px;">
        <tr>
            <td width="50%"></td>
            <td width="50%" class="text-center">
                Hormat kami,<br><br><br><br><br>
                <strong><u>({{ $profileUser->name ?? 'Nama Pengguna' }})</u></strong><br>
                {{ $profileUser->jabatan ?? 'Bagian Pembelian' }}
            </td>
        </tr>
    </table>

</body>
</html>
