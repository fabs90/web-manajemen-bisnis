<?php

namespace App\Models\Rapat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AgendaRapat extends Model
{
    protected $table = "agenda_rapat";

    protected $fillable = [
        "user_id",
        "nomor_surat",
        "judul_rapat",
        "tempat",
        "tanggal",
        "waktu",
        "pemimpin_rapat",
        "keputusan_rapat",
        "nama_kota",
        "nama_notulis",
        "agenda_rapat",
        "tanggal_rapat_berikutnya",
        "agenda_rapat_berikutnya",
        "waktu_rapat_berikutnya",
        "ttd_pemimpin",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pesertaRapat()
    {
        return $this->hasMany(PesertaRapat::class, "agenda_rapat_id");
    }

    public function rapatDetails()
    {
        return $this->hasMany(RapatDetail::class, "agenda_rapat_id");
    }

    public function tindakLanjutRapat()
    {
        return $this->hasMany(TindakLanjutRapat::class, "agenda_rapat_id");
    }

    public function hasilKeputusanRapat()
    {
        return $this->hasOne(HasilKeputusanRapat::class, "agenda_rapat_id");
    }
}
