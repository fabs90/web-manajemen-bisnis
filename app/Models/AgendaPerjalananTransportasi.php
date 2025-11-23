<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaPerjalananTransportasi extends Model
{
    public $table = "agenda_perjalanan_transportasi";

    protected $fillable = [
        "user_id",
        "agenda_perjalanan_id",
        "penerbangan_pergi",
        "penerbangan_pulang",
        "kode_booking",
        "transportasi_lokal",
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
