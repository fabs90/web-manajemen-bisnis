<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengisianKasKecilLog extends Model
{
    protected $table = "pengisian_kas_kecil_logs";

    protected $fillable = [
        "buku_besar_kas_id",
        "kas_kecil_id",
        "uraian",
        "jumlah",
        "tanggal_transaksi",
        "user_id",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bukuBesarKas()
    {
        return $this->belongsTo(BukuBesarKas::class);
    }

    public function kasKecil()
    {
        return $this->belongsTo(KasKecil::class);
    }
}
