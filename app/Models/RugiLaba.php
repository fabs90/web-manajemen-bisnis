<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RugiLaba extends Model
{
    use HasFactory;
    protected $table = "rugi_laba";
    protected $fillable = [
        "kode",
        "total_penjualan",
        "uraian",
        "hpp",
        "biaya_operasional",
        "laba_bersih",
        "user_id",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
