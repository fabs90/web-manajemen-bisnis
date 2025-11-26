<?php

namespace App\Models\Rapat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AgendaRapat extends Model
{
    protected $table = 'agenda_rapat';

    protected $fillable = [
        'user_id',
        'judul_rapat',
        'tempat',
        'tanggal',
        'waktu',
        'pimpinan_rapat',
        'keputusan_rapat',
        'nama_kota',
        'notulis',
        'tanggal_rapat_berikutnya',
        'agenda_rapat_berikutnya',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pesertaRapat()
    {
        return $this->hasMany(PesertaRapat::class, 'agenda_rapat_id');
    }

    public function rapatDetails()
    {
        return $this->hasMany(RapatDetail::class, 'agenda_rapat_id');
    }
    public function keputusanRapat()
    {
        return $this->hasMany(KeputusanRapat::class, 'agenda_rapat_id');
    }

    public function tindakLanjutRapat()
    {
        return $this->hasMany(TindakLanjutRapat::class, 'agenda_rapat_id');
    }
}
