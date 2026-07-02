<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Permintaan Kas Kecil</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .table th, .table td {
            border: 1px solid #000; padding: 6px;
        }
        .text-center { text-align: center; }
        .no-border td, .no-border th { border: none; }
        .signature-box { width: 33%; text-align: center; font-size: 12px; }
        .signature-box img { width: 80px; margin-top: 6px; }
        .header-title { text-align: center; font-size: 16px; font-weight: bold; }
        hr { border: 1px solid #000; margin: 10px 0; }
    </style>
</head>
<body>

    @php
        $formulir = $data->kasKecilFormulir->last();

        $getImgBase64 = function($path) {
            if (!$path) return null;
            $fullPath = storage_path('app/public/' . $path);
            if (file_exists($fullPath)) {
                $mime = mime_content_type($fullPath);
                $base64 = base64_encode(file_get_contents($fullPath));
                return 'data:' . $mime . ';base64,' . $base64;
            }
            return null;
        };

        $logoSrc = $getImgBase64($userProfile->logo_perusahaan);
        $ttdPemohonSrc = $getImgBase64($formulir->ttd_nama_pemohon ?? '');
        $ttdAtasanSrc = $getImgBase64($formulir->ttd_atasan_langsung ?? '');
        $ttdKeuanganSrc = $getImgBase64($formulir->ttd_bagian_keuangan ?? '');
    @endphp

    {{-- HEADER --}}
    <div style="text-align:center;">
        @if($logoSrc)
            <img src="{{ $logoSrc }}" style="height:70px; margin-bottom:8px;">
        @endif
        <div class="header-title">FORM PERMINTAAN KAS KECIL</div>
    </div>
    <hr>



    {{-- DATA PEMOHON --}}
    <table class="no-border">
        <tr>
            <td width="30%"><strong>Nama Pemohon</strong></td>
            <td>: {{ $formulir->nama_pemohon ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Departemen</strong></td>
            <td>: {{ $formulir->departemen ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Tanggal</strong></td>
            <td>: {{ \Carbon\Carbon::parse($data->tanggal)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td><strong>Nomor Referensi</strong></td>
            <td>: {{ $data->nomor_referensi }}</td>
        </tr>
        <tr>
            <td><strong>Jenis</strong></td>
            <td>: {{ $data->pengeluaran > 0 ? 'Pengeluaran' : 'Penerimaan' }}</td>
        </tr>
    </table>

    {{-- RINCIAN --}}
    <h4 style="margin-top:20px;">Rincian Permintaan Dana:</h4>
    <table class="table">
        <thead>
            <tr class="text-center">
                <th width="5%">No</th>
                <th width="50%">Keterangan</th>
                <th width="20%">Kategori</th>
                <th width="25%">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data->kasKecilDetail as $i => $detail)
            <tr>
                <td class="text-center">{{ $i+1 }}</td>
                <td>{{ $detail->keterangan }}</td>
                <td>{{ $detail->kategori ?? '-' }}</td>
                <td>Rp {{ number_format($detail->jumlah, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-center">Total</th>
                <th>Rp {{ number_format(($data->penerimaan + $data->pengeluaran), 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="no-border" style="margin-top:40px; width:100%;">
        <tr>
            <td class="signature-box">
                Pemohon,<br>
                @if($ttdPemohonSrc)
                    <img src="{{ $ttdPemohonSrc }}" style="height:60px;">
                @else <br><br><br> @endif
                <strong>{{ $formulir->nama_pemohon }}</strong>
            </td>

            <td class="signature-box">
                Mengetahui,<br>
                @if($ttdAtasanSrc)
                    <img src="{{ $ttdAtasanSrc }}" style="height:60px;">
                @else <br><br><br> @endif
                <strong>{{ $formulir->nama_atasan_langsung }}</strong>
            </td>

            <td class="signature-box">
                Bagian Keuangan,<br>
                @if($ttdKeuanganSrc)
                    <img src="{{ $ttdKeuanganSrc }}" style="height:60px;">
                @else <br><br><br> @endif
                <strong>{{ $formulir->nama_bagian_keuangan }}</strong>
            </td>
        </tr>
    </table>

</body>
</html>
