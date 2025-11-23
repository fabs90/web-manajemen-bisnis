<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaTelpon extends Model
{
    public $table = "agenda_telpon";

    protected $fillable = [
        "user_id",
        "tgl_panggilan",
        "waktu_panggilan",
        "nama_penelpon",
        "perusahaan",
        "nomor_telpon",
        "jadwal_tanggal",
        "jadwal_waktu",
        "jadwal_dengan",
        "keperluan",
        "tingkat_status",
        "catatan_khusus",
        "status",
        "dicatat_oleh",
        "dicatat_tgl",
        "is_done",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
