<?php

namespace App\Models\SPB;

use App\Models\SPP\SuratPesananPembelianDetail;
use App\Models\SPP\SuratPesananPenjualanDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratPengirimanBarangDetail extends Model
{
    protected $table = 'surat_pengiriman_barang_detail';

    protected $fillable = ['spb_id', 'spp_detail_id', 'pesanan_penjualan_detail_id', 'jumlah_dikirim', 'keterangan'];

    public function suratPengirimanBarang(): BelongsTo
    {
        return $this->belongsTo(SuratPengirimanBarang::class, 'spb_id');
    }

    public function pesananPembelianDetail(): BelongsTo
    {
        return $this->belongsTo(SuratPesananPembelianDetail::class, 'spp_detail_id');
    }

    public function pesananPenjualanDetail(): BelongsTo
    {
        return $this->belongsTo(SuratPesananPenjualanDetail::class, 'pesanan_penjualan_detail_id');
    }
}
