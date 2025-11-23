<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratKeluarEmailLog extends Model
{
    protected $table = "surat_keluar_email_logs";
    protected $fillable = [
        "surat_keluar_id",
        "email",
        "status",
        "message",
        "sent_at",
    ];
    public function suratKeluar()
    {
        return $this->belongsTo(AgendaSuratKeluar::class, "surat_keluar_id");
    }
}
