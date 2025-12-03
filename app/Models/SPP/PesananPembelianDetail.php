<?php

namespace App\Models\SPP;

use Illuminate\Database\Eloquent\Model;

class PesananPembelianDetail extends Model
{
    protected $table = "pesanan_pembelian_detail";
    protected $fillable = [
        "spp_id",
        "nama_barang",
        "kuantitas",
        "harga",
        "diskon",
        "total",
    ];

    public function pesananPembelian()
    {
        return $this->belongsTo(PesananPembelian::class, "spp_id");
    }
}
