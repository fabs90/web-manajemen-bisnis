<x-mail::message>
# Faktur Penjualan
 
Yth. Bapak/Ibu Pelanggan,
 
Bersama email ini, kami sampaikan Faktur Penjualan dengan nomor faktur **{{ $data->kode_faktur }}** untuk pesanan Anda.
 
Rincian dokumen berupa PDF telah kami sertakan pada lampiran email ini.
 
Terima kasih atas kerja samanya.
 
Hormat kami,<br>
{{ $user->name ?? config('app.name') }}
</x-mail::message>
