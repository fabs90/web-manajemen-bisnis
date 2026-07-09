<x-mail::message>
# Surat Pengiriman Barang
 
Yth. Bapak/Ibu Pelanggan,
 
Bersama email ini, kami sampaikan Surat Pengiriman Barang (SPB) dengan nomor **{{ $data->nomor_pengiriman_barang }}** sebagai bukti bahwa barang telah dikirim/diproses.
 
Dokumen lengkap berupa PDF dapat Anda temukan pada lampiran email ini.
 
Terima kasih atas kepercayaan Anda.
 
Hormat kami,<br>
{{ $user->name ?? config('app.name') }}
</x-mail::message>
