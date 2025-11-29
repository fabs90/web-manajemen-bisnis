<?php

namespace App\Models\Rapat;

use Illuminate\Database\Eloquent\Model;

class TindakLanjutRapat extends Model
{
    protected $table = 'tindak_lanjut_rapat';

    protected $fillable = [
        'agenda_rapat_id',
        'tindakan',
        'pelaksana',
        'target_selesai',
        'status',
    ];

    public function agendaRapat()
    {
        return $this->belongsTo(AgendaRapat::class);
    }
}