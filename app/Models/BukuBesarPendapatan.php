<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BukuBesarPendapatan extends Model
{
    protected $table = "buku_besar_pendapatan_tunai";
    protected $fillable = [
        "tanggal",
        "uraian",
        "potongan_pembelian",
        "piutang_dagang",
        "penjualan_tunai",
        "lain_lain",
        "uang_diterima",
        "bunga_bank",
        "retur_penjualan",
        "user_id",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function piutang()
    {
        return $this->hasMany(
            BukuBesarPiutang::class,
            "buku_besar_pendapatan_id",
        );
    }

    public function hutang()
    {
        return $this->hasMany(
            BukuBesarHutang::class,
            "buku_besar_pendapatan_id",
        );
    }
}
