<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BukuBesarPengeluaran extends Model
{
    protected $table = "buku_besar_pengeluaran_tunai";
    protected $fillable = [
        "tanggal",
        "uraian",
        "potongan_pembelian",
        "jumlah_hutang",
        "jumlah_pembelian_tunai",
        "lain_lain",
        "admin_bank",
        "jumlah_retur_pembelian",
        "jumlah_pengeluaran",
        "user_id",
        "buku_besar_kas_id",
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
            "buku_besar_pengeluaran_id",
        );
    }

    public function kas()
    {
        return $this->belongsTo(BukuBesarKas::class, "buku_besar_kas_id");
    }
}
