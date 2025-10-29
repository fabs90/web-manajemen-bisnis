<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BukuBesarKas extends Model
{
    use HasFactory;
    protected $table = "buku_besar_kas";

    protected $fillable = [
        "kode",
        "uraian",
        "tanggal",
        "debit",
        "kredit",
        "saldo",
        "neraca_awal_id",
        "neraca_akhir_id",
        "user_id",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function neracaAwal()
    {
        return $this->belongsTo(NeracaAwal::class);
    }

    public function neracaAkhir()
    {
        return $this->belongsTo(NeracaAkhir::class);
    }
}
