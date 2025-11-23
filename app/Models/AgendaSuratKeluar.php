<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaSuratKeluar extends Model
{
    protected $table = "agenda_surat_keluar";

    protected $fillable = [
        "user_id",
        "nomor_surat",
        "lampiran",
        "perihal",
        "tanggal_surat",

        "nama_penerima",
        "jabatan_penerima",
        "alamat_penerima",

        "paragraf_pembuka",
        "paragraf_isi",
        "paragraf_penutup",

        "nama_pengirim",
        "jabatan_pengirim",

        "tembusan",

        "ttd",
        "file_lampiran",

        "is_sent_email",
        "sent_at",
    ];

    public function emailLogs()
    {
        return $this->hasMany(SuratKeluarEmailLog::class, "surat_keluar_id");
    }
}
