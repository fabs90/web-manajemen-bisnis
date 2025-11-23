<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasKecilFormulir extends Model
{
    protected $table = "kas_kecil_formulir";
    protected $fillable = [
        "user_id",
        "kas_kecil_id",
        "nama_pemohon",
        "departemen",
        "ttd_nama_pemohon",
        "nama_atasan_langsung",
        "ttd_atasan_langsung",
        "nama_bagian_keuangan",
        "ttd_bagian_keuangan",
    ];

    public function kas_kecil()
    {
        return $this->belongsTo(KasKecil::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
