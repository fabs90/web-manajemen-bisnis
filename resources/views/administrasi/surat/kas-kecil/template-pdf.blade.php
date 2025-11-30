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

    {{-- HEADER --}}
    <div style="text-align:center;">
        @if($userProfile->logo_perusahaan)
            <img src="{{ public_path('storage/'.$userProfile->logo_perusahaan) }}" width="90" style="margin-bottom:8px;">
        @endif
        <div class="header-title">FORM PERMINTAAN KAS KECIL</div>
    </div>
    <hr>

    @php
        $formulir = $data->kasKecilFormulir->last();
    @endphp

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
                @if($formulir->ttd_nama_pemohon)
                    <img src="{{ public_path('storage/'.$formulir->ttd_nama_pemohon) }}">
                @else <br><br><br> @endif
                <strong>{{ $formulir->nama_pemohon }}</strong>
            </td>

            <td class="signature-box">
                Mengetahui,<br>
                @if($formulir->ttd_atasan_langsung)
                    <img src="{{ public_path('storage/'.$formulir->ttd_atasan_langsung) }}">
                @else <br><br><br> @endif
                <strong>{{ $formulir->nama_atasan_langsung }}</strong>
            </td>

            <td class="signature-box">
                Bagian Keuangan,<br>
                @if($formulir->ttd_bagian_keuangan)
                    <img src="{{ public_path('storage/'.$formulir->ttd_bagian_keuangan) }}">
                @else <br><br><br> @endif
                <strong>{{ $formulir->nama_bagian_keuangan }}</strong>
            </td>
        </tr>
    </table>

</body>
</html>
