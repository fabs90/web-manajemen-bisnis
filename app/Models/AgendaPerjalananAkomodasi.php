<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaPerjalananAkomodasi extends Model
{
    public $table = "agenda_perjalanan_akomodasi";

    protected $fillable = [
        "user_id",
        "agenda_perjalanan_id",
        "hotel",
        "alamat",
        "telepon",
        "check_in",
        "check_out",
        "booking_number",
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
