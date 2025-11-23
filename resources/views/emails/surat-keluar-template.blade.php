<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; font-size: 14px; }
        .header-table td { vertical-align: top; }
        .info { margin-bottom: 20px; }
        .section-title { font-weight: bold; margin-top: 20px; text-decoration: underline; }
        .ttd-area { margin-top: 40px; }
        .tembusan { margin-top: 20px; }
    </style>
</head>
<body>

    {{-- HEADER SURAT --}}
    <table width="100%" class="header-table">
        <tr>
            <td width="80">
                @if($user->logo_perusahaan)
                    <img src="{{ asset('storage/'.$user->logo_perusahaan) }}" width="80">
                @endif
            </td>
            <td>
                <strong style="font-size: 16px;">{{ $user->name }}</strong><br>
                {{ $user->alamat }}<br>
                    Telp: {{ $user->nomor_telepon }}<br>
                        Email: {{ $user->email }}
            </td>
        </tr>
    </table>

    <hr>

    {{-- Nomor Surat --}}
    <div class="info">
        <p><strong>Nomor Surat:</strong> {{ $surat->nomor_surat }}</p>
        <p><strong>Lampiran:</strong> {{ $surat->lampiran ?? '-' }}</p>
        <p><strong>Perihal:</strong> {{ $surat->perihal }}</p>
        <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($surat->tanggal_surat)->format('d F Y') }}</p>
    </div>

    {{-- TUJUAN --}}
    <div class="info">
        <p><strong>Kepada Yth:</strong><br>
            {{ $surat->nama_penerima }}<br>
            {{ $surat->jabatan_penerima }}<br>
            {{ $surat->alamat_penerima }}
        </p>
    </div>

    <p>Dengan hormat,</p>

    {{-- PEMBUKA --}}
    <p>{!! nl2br(e($surat->paragraf_pembuka)) !!}</p>

    {{-- ISI --}}
    <p>{!! nl2br(e($surat->paragraf_isi)) !!}</p>

    {{-- PENUTUP --}}
    <p>{!! nl2br(e($surat->paragraf_penutup)) !!}</p>

    <p>
        Demikian surat ini kami sampaikan. Atas kerja sama yang baik disampaikan terima kasih.
    </p>

    {{-- TTD --}}
    <div class="ttd-area">
        <p>Hormat kami,</p>

        @if($surat->ttd)
            <img src="{{ asset('storage/'.$surat->ttd) }}" width="120"><br>
        @else
            <br><br><br>
        @endif

        <strong>{{ $surat->nama_pengirim }}</strong><br>
        {{ $surat->jabatan_pengirim }}
    </div>

    {{-- TEMBUSAN --}}
    @if($surat->tembusan)
        <div class="tembusan">
            <strong>Tembusan:</strong>
            <p>{!! nl2br(e($surat->tembusan)) !!}</p>
        </div>
    @endif

    {{-- LAMPIRAN FILE --}}
    @if($surat->file_lampiran)
        <p>
            <strong>Lampiran File:</strong><br>
            <a href="{{ asset('storage/'.$surat->file_lampiran) }}">Klik untuk membuka lampiran</a>
        </p>
    @endif

</body>
</html>
