<x-mail::message>
# Surat Pesanan Pembelian

Halo {{ $data->pelanggan->nama }},

Terlampir adalah salinan PDF Surat Pesanan Pembelian kepada {{ $user->nama }} dengan nomor pesanan: **{{ $data->nomor_pesanan_pembelian }}**.

Terima kasih,<br>
**{{ $user->nama }}**
</x-mail::message>
