<?php

namespace App\Models\Rapat;

use Illuminate\Database\Eloquent\Model;

class PesertaRapat extends Model
{
    protected $table = 'peserta_rapat';

    protected $fillable = [
        'agenda_rapat_id',
        'nama',
        'jabatan',
        'tanda_tangan',
    ];

    public function agendaRapat()
    {
        return $this->belongsTo(AgendaRapat::class);
    }
}