<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaSuratMasuk extends Model
{
    protected $table = "agenda_surat_masuk";

    protected $fillable = [
        "user_id",
        "nomor_agenda",
        "tanggal_terima",
        "nomor_surat",
        "tanggal_surat",
        "pengirim",
        "perihal",
        "file_surat",
        "status_disposisi",

        // Disposisi
        "disp_segera",
        "disp_teliti",
        "disp_edarkan",
        "disp_diketahui",
        "disp_koordinasikan",
        "disp_proses_lanjut",
        "disp_arsipkan",
        "disp_mohon_dijawab",

        // Catatan
        "catatan",

        // Tujuan
        "tujuan_keuangan",
        "tujuan_gudang",
        "tujuan_karyawan",
        "tujuan_lainnya",

        // Others
        "tanggal_disposisi",
        "ttd_pimpinan",
    ];

    protected $casts = [
        // Disposisi
        "disp_segera" => "boolean",
        "disp_teliti" => "boolean",
        "disp_edarkan" => "boolean",
        "disp_diketahui" => "boolean",
        "disp_koordinasikan" => "boolean",
        "disp_proses_lanjut" => "boolean",
        "disp_arsipkan" => "boolean",
        "disp_mohon_dijawab" => "boolean",

        // Tujuan
        "tujuan_keuangan" => "boolean",
        "tujuan_gudang" => "boolean",
        "tujuan_karyawan" => "boolean",
        "tujuan_lainnya" => "boolean",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
