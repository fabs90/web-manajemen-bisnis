<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasKecilDetail extends Model
{
    protected $table = "kas_kecil_detail";
    protected $fillable = [
        "user_id",
        "kas_kecil_id",
        "keterangan",
        "kategori",
        "jumlah",
    ];

    public $timestamps = false;

    public function kasKecil()
    {
        return $this->belongsTo(KasKecil::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
