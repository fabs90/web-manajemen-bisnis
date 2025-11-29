<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasirTransactionLog extends Model
{
    protected $table = "kasir_transaction_logs";
    protected $fillable = [
        "pendapatan_id",
        "buku_besar_kas_id",
        "uraian",
        "tanggal_transaksi",
        "jumlah",
        "user_id",
    ];

    public function bukuBesarPendapatan()
    {
        return $this->belongsTo(BukuBesarPendapatan::class, "pendapatan_id");
    }

    public function bukuBesarKas()
    {
        return $this->belongsTo(BukuBesarKas::class, "buku_besar_kas_id");
    }

    public function user()
    {
        return $this->belongsto(User::class);
    }
}
