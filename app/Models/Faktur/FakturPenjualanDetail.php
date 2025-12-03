<?php

namespace App\Models\Faktur;

use App\Models\SPB\SuratPengirimanBarangDetail;
use Illuminate\Database\Eloquent\Model;

class FakturPenjualanDetail extends Model
{
    protected $table = "faktur_penjualan_detail";
    protected $fillable = [
        "faktur_penjualan_id",
        "spb_detail_id",
        "harga",
        "total",
    ];

    public function fakturPenjualan()
    {
        return $this->belongsTo(FakturPenjualan::class, "faktur_penjualan_id");
    }

    public function suratPengirimanBarangDetail()
    {
        return $this->belongsTo(
            SuratPengirimanBarangDetail::class,
            "spb_detail_id",
        );
    }
}
