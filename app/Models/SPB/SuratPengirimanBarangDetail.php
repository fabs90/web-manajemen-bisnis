<?php

namespace App\Models\SPB;

use App\Models\SPP\PesananPembelianDetail;
use Illuminate\Database\Eloquent\Model;

class SuratPengirimanBarangDetail extends Model
{
    protected $table = "surat_pengiriman_barang_detail";
    protected $fillable = ["spb_id", "spp_detail_id", "jumlah_dikirim"];

    public function suratPengirimanBarang()
    {
        return $this->belongsTo(SuratPengirimanBarang::class, "spb_id");
    }

    public function pesananPembelianDetail()
    {
        return $this->belongsTo(PesananPembelianDetail::class, "spp_detail_id");
    }
}
