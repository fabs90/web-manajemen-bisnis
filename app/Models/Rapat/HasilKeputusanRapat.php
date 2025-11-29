<?php

namespace App\Models\Rapat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class HasilKeputusanRapat extends Model
{
    protected $table = "hasil_keputusan_rapat";
    protected $fillable = [
        "agenda_rapat_id",
        "nomor_surat",
        "keputusan_rapat",
        "kota_tujuan",
        "tanggal_tujuan",
        "jabatan_penanggung_jawab",
        "nama_penanggung_jawab",
        "user_id",
    ];

    public function agendaRapat()
    {
        return $this->belongsTo(AgendaRapat::class, "agenda_rapat_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
