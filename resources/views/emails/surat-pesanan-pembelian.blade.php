<x-mail::message>
# Surat Pesanan Pembelian

Kepada Yth. Bapak/Ibu Pimpinan,
**{{ $data->nama_bagian_pembelian }}**

Dengan hormat,

Melalui surel ini, kami bermaksud untuk menyampaikan Surat Pesanan Pembelian (Purchase Order) dari perusahaan kami dengan nomor pesanan: **{{ $data->nomor_pesanan_pembelian }}**.

Rincian pesanan barang serta informasi pengiriman telah kami lampirkan pada dokumen PDF bersama surel ini. Kami berharap pesanan ini dapat diproses dan dikirimkan sesuai dengan waktu yang telah disepakati.

Jika terdapat pertanyaan lebih lanjut atau kendala terkait pesanan ini, mohon kesediaannya untuk menghubungi kami.

Atas perhatian dan kerja sama yang baik dari Bapak/Ibu, kami ucapkan terima kasih.

Hormat kami,

**{{ $user->name }}**
</x-mail::message>
