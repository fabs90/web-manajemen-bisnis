<x-mail::message>
# Undangan Rapat

Yth. **{{ $surat->nama_penerima }}**,
{{ $surat->jabatan_penerima ? $surat->jabatan_penerima : '' }}
{{ $surat->kota_penerima ? $surat->kota_penerima : '' }}

Dengan hormat,

Bersama ini kami mengundang Bapak/Ibu untuk menghadiri rapat dengan rincian sebagai berikut:

**Judul Rapat:** {{ $surat->judul_rapat }}
**Hari/Tanggal:** {{ $surat->hari }}, {{ \Carbon\Carbon::parse($surat->tanggal_rapat)->translatedFormat('d F Y') }}
**Waktu:** {{ \Carbon\Carbon::parse($surat->waktu_mulai)->format('H:i') }} - {{ $surat->waktu_selesai ? \Carbon\Carbon::parse($surat->waktu_selesai)->format('H:i') : 'Selesai' }}
**Tempat:** {{ $surat->tempat }}

**Agenda Rapat:**
@foreach($surat->details as $index => $detail)
{{ $index + 1 }}. {{ $detail->agenda }}
@endforeach

Demikian undangan ini kami sampaikan. Atas perhatian dan kehadirannya diucapkan terima kasih.

Hormat kami,

**{{ $surat->nama_penandatangan }}**
{{ $surat->jabatan_penandatangan }}


Terima Kasih,<br>
{{ config('app.name') }}
</x-mail::message>
