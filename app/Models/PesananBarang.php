<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananBarang extends Model
{
    protected $table = "pesanan_barang";
    protected $fillable = [
        "barang_id",
        "user_id",
        "stok_sekarang",
        "jumlah_pesanan",
        "status",
        "tanggal_pesan",
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
