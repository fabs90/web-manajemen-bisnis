<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasKecil extends Model
{
    protected $table = "kas_kecil";

    protected $fillable = [
        "user_id",
        "tanggal",
        "nomor_referensi",
        "penerimaan",
        "pengeluaran",
        "saldo_akhir",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kasKecilDetail()
    {
        return $this->hasMany(KasKecilDetail::class);
    }

    public function kasKecilFormulir()
    {
        return $this->hasMany(KasKecilFormulir::class);
    }

    public function kasKecilLog()
    {
        return $this->hasMany(PengisianKasKecilLog::class);
    }
}
