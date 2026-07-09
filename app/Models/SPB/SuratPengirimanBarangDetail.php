<?php

namespace App\Models\SPB;

use App\Models\SPP\PesananPembelianDetail;
use Illuminate\Database\Eloquent\Model;

class SuratPengirimanBarangDetail extends Model
{
    protected $table = 'surat_pengiriman_barang_detail';

    protected $fillable = ['spb_id', 'spp_detail_id', 'pesanan_penjualan_detail_id', 'jumlah_dikirim', 'keterangan'];

    public function suratPengirimanBarang(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SuratPengirimanBarang::class, 'spb_id');
    }

    public function pesananPembelianDetail(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PesananPembelianDetail::class, 'spp_detail_id');
    }

    public function pesananPenjualanDetail(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\SPP\SuratPesananPenjualanDetail::class, 'pesanan_penjualan_detail_id');
    }
}
