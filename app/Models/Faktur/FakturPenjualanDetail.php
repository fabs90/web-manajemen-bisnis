<?php

namespace App\Models\Faktur;

use Illuminate\Database\Eloquent\Model;

class FakturPenjualanDetail extends Model
{
    protected $table = "faktur_penjualan_detail";
    protected $fillable = [
        "faktur_penjualan_id",
        "jumlah_dipesan",
        "jumlah_dikirim",
        "nama_barang",
        "harga",
        "diskon",
        "total",
    ];

    public function fakturPenjualan()
    {
        return $this->belongsTo(FakturPenjualan::class, "faktur_penjualan_id");
    }
}
