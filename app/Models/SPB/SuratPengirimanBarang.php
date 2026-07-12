<?php

namespace App\Models\SPB;

use App\Models\Faktur\FakturPenjualan;
use App\Models\SPP\SuratPesananPembelian;
use App\Models\SPP\SuratPesananPenjualan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SuratPengirimanBarang extends Model
{
    protected $table = 'surat_pengiriman_barang';

    protected $fillable = [
        'spp_id',
        'pesanan_penjualan_id',
        'nomor_pengiriman_barang',
        'jenis_pengiriman',
        'status_pengiriman',
        'tanggal_terima',
        'keadaan',
        'keterangan',
        'nama_penerima',
        'nama_pengirim',
        'ttd_pengirim',
        'ttd_penerima',
        'user_id',
    ];

    public function pesananPembelian(): BelongsTo
    {
        return $this->belongsTo(SuratPesananPembelian::class, 'spp_id');
    }

    public function pesananPenjualan(): BelongsTo
    {
        return $this->belongsTo(SuratPesananPenjualan::class, 'pesanan_penjualan_id');
    }

    public function suratPengirimanBarangDetail(): HasMany
    {
        return $this->hasMany(SuratPengirimanBarangDetail::class, 'spb_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fakturPenjualan(): HasOne
    {
        return $this->hasOne(FakturPenjualan::class, 'spb_id', 'id');
    }
}
