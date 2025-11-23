<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaPerjalananKontak extends Model
{
    public $table = "agenda_perjalanan_kontak";

    protected $fillable = [
        "user_id",
        "agenda_perjalanan_id",
        "nama",
        "telepon",
        "jenis",
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
