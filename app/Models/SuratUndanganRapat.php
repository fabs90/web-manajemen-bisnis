<?php

namespace App\Models;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;

class SuratUndanganRapat extends Model
{
    protected $table = 'surat_undangan_rapat';

    protected $fillable = [
        'user_id',
        'nomor_surat',
        'lampiran',
        'file_lampiran',
        'perihal',
        'nama_penerima',
        'email_penerima',
        'jabatan_penerima',
        'kota_penerima',
        'judul_rapat',
        'tanggal_rapat',
        'hari',
        'waktu_mulai',
        'waktu_selesai',
        'tempat',
        'nama_penandatangan',
        'jabatan_penandatangan',
        'tembusan',
        'ttd',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(SuratUndanganRapatDetail::class);
    }

    public function generatePdf()
    {
        $this->loadMissing('details');

        return Pdf::loadView('administrasi.surat.surat-undangan-rapat.template-pdf', [
            'agendaJanjiTemu' => $this,
            'profileUser' => $this->user,
        ])->setPaper('a4', 'portrait');
    }
}
