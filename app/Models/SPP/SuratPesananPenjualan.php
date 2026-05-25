<?php

namespace App\Models\SPP;

use App\Models\Pelanggan;
use App\Models\SPB\SuratPengirimanBarang;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuratPesananPenjualan extends Model
{
    protected $table = 'surat_pesanan_penjualan';

    protected $fillable = [
        'pelanggan_id',
        'jenis',
        'nomor_pesanan_penjualan',
        'tanggal_pesanan_penjualan',
        'tanggal_kirim_pesanan_penjualan',
        'nama_bagian_pembelian',
        'ttd_bagian_pembelian',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pesanan_penjualan' => 'date',
            'tanggal_kirim_pesanan_penjualan' => 'date',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SuratPesananPenjualanDetail::class, 'pesanan_penjualan_id');
    }

    public function suratPengirimanBarang(): HasMany
    {
        return $this->hasMany(SuratPengirimanBarang::class, 'pesanan_penjualan_id');
    }
}
