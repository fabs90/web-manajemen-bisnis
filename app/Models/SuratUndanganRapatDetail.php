<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratUndanganRapatDetail extends Model
{
    protected $table = "surat_undangan_rapat_detail";

    protected $fillable = [
        "user_id",
        "surat_undangan_rapat_id",
        "agenda",
    ];

    public function suratUndanganRapat()
    {
        return $this->belongsTo(SuratUndanganRapat::class);
    }
}