<?php

namespace App\Models\Rapat;

use Illuminate\Database\Eloquent\Model;

class RapatDetail extends Model
{
    protected $table = 'rapat_detail';

    protected $fillable = [
        'agenda_rapat_id',
        'judul_agenda',
        'pembicara',
        'pembahasan',
    ];

    public function agendaRapat()
    {
        return $this->belongsTo(AgendaRapat::class);
    }

    public function keputusanRapat()
    {
        return $this->hasMany(KeputusanRapat::class, 'rapat_detail_id');
    }
}
