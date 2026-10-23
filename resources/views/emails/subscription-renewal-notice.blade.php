<x-mail::message>
# Pemberitahuan Perpanjangan Langganan

Yth. **{{ $user?->name ?? $owner?->name ?? 'Pelanggan' }}**,

Kami ingin menginformasikan bahwa masa aktif paket langganan **{{ $plan?->name ?? 'Paket Layanan' }}** Anda akan segera berakhir.

Berikut adalah rincian langganan Anda saat ini:

<x-mail::panel>
**Paket Layanan:** {{ $plan?->name ?? '-' }} {{ $plan?->code ? '(' . $plan->code . ')' : '' }}  
@if($subscription->organization)
**Organisasi:** {{ $subscription->organization->name }}  
@endif
**Biaya Perpanjangan:** Rp {{ number_format($plan?->price ?? 0, 0, ',', '.') }}  
**Durasi Paket:** {{ $plan?->duration_days ? $plan->duration_days . ' Hari' : 'Permanen' }}  
**Tanggal Berakhir:** {{ $subscription->ends_at ? $subscription->ends_at->translatedFormat('d F Y') : '-' }}  
@if($daysRemaining !== null)
**Sisa Masa Aktif:** {{ $daysRemaining === 0 ? 'Berakhir Hari Ini' : $daysRemaining . ' Hari Lagi' }}
@endif
</x-mail::panel>

Untuk memastikan akses Anda ke seluruh fitur pembukuan, laporan keuangan, dan administrasi tetap berjalan lancar tanpa gangguan, silakan lakukan perpanjangan paket sebelum masa aktif berakhir melalui tombol di bawah ini:

<x-mail::button :url="$renewUrl">
Perpanjang Langganan Sekarang
</x-mail::button>

Apabila Anda telah melakukan pembayaran atau perpanjangan paket baru-baru ini, silakan abaikan email pemberitahuan ini.

Terima kasih atas kepercayaan Anda menggunakan layanan kami.

Hormat kami,<br>
**{{ config('app.name') }}**
</x-mail::message>
