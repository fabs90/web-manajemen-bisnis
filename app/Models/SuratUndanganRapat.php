<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratUndanganRapat extends Model
{
    protected $table = "surat_undangan_rapat";

    protected $fillable = [
        "user_id",
        "nomor_surat",
        "lampiran",
        "perihal",
        "nama_penerima",
        "jabatan_penerima",
        "kota_penerima",
        "judul_rapat",
        "tanggal_rapat",
        "hari",
        "waktu_mulai",
        "waktu_selesai",
        "tempat",
        "nama_penandatangan",
        "jabatan_penandatangan",
        "tembusan",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(SuratUndanganRapatDetail::class);
    }
}