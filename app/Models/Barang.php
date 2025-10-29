<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;
    protected $table = "barang";

    protected $fillable = [
        "kode_barang",
        "nama",
        "user_id",
        "jumlah_max",
        "jumlah_min",
        "jumlah_unit_per_kemasan",
        "harga_beli_per_unit",
        "harga_beli_per_kemas",
        "harga_jual_per_unit",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kartuGudang()
    {
        return $this->hasMany(KartuGudang::class);
    }
}
