<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaPerjalananDetail extends Model
{
    public $table = "agenda_perjalanan_details";
    protected $fillable = [
        "user_id",
        "agenda_perjalanan_id",
        "hari",
        "tanggal",
        "waktu",
        "kegiatan",
        "lokasi",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agendaPerjalanan()
    {
        return $this->belongsTo(AgendaPerjalanan::class);
    }
}
