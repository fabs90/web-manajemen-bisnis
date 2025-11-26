<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaJanjiTemu extends Model
{
    public $table = "agenda_janji_temu";
    protected $fillable = [
        "user_id",
        "tgl_membuat",
        "nama_pembuat",
        "perusahaan",
        "nomor_telpon",
        "tgl_janji",
        "waktu",
        "bertemu_dengan",
        "tempat_pertemuan",
        "keperluan",
        "status",
        "dicatat_oleh",
        "dicatat_tgl",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
