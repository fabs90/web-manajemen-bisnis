<x-mail::message>
# Update Undangan Rapat

Yth. **{{ $suratUndanganRapat->nama_penerima }}**,
{{ $suratUndanganRapat->jabatan_penerima ? $suratUndanganRapat->jabatan_penerima : '' }}
{{ $suratUndanganRapat->kota_penerima ? $suratUndanganRapat->kota_penerima : '' }}

Dengan hormat,

Pemberitahuan ini dikirimkan untuk menginformasikan bahwa terdapat **perubahan/update** pada agenda rapat yang telah dikirimkan sebelumnya. Berikut adalah rincian terbaru:

**Judul Rapat:** {{ $suratUndanganRapat->judul_rapat }}
**Hari/Tanggal:** {{ $suratUndanganRapat->hari }}, {{ \Carbon\Carbon::parse($suratUndanganRapat->tanggal_rapat)->translatedFormat('d F Y') }}
**Waktu:** {{ \Carbon\Carbon::parse($suratUndanganRapat->waktu_mulai)->format('H:i') }} - {{ $suratUndanganRapat->waktu_selesai ? \Carbon\Carbon::parse($suratUndanganRapat->waktu_selesai)->format('H:i') : 'Selesai' }}
**Tempat:** {{ $suratUndanganRapat->tempat }}

**Agenda Rapat Terbaru:**
@foreach($suratUndanganRapat->details as $index => $detail)
{{ $index + 1 }}. {{ $detail->agenda }}
@endforeach

Mohon untuk menyesuaikan jadwal Bapak/Ibu dengan rincian terbaru di atas. Terlampir juga dokumen PDF revisi untuk referensi Bapak/Ibu.

Atas perhatian dan kerjasamanya diucapkan terima kasih.

Hormat kami,

**{{ $suratUndanganRapat->nama_penandatangan }}**
{{ $suratUndanganRapat->jabatan_penandatangan }}

Terima Kasih,<br>
{{ config('app.name') }}
</x-mail::message>
