<?php

namespace App\Models\Rapat;

use Illuminate\Database\Eloquent\Model;

class KeputusanRapat extends Model
{
    protected $table = 'keputusan_rapat';

    protected $fillable = [
        'agenda_rapat_id',
        // 'rapat_detail_id',
        'keputusan',
    ];

    public function agendaRapat()
    {
        return $this->belongsTo(AgendaRapat::class);
    }

    // public function rapatDetail()
    // {
    //     return $this->belongsTo(RapatDetail::class);
    // }
}