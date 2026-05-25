<?php

namespace App\Models\SPP;

use App\Models\Pelanggan;
use App\Models\SPB\SuratPengirimanBarang;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PesananPembelian extends Model
{
    protected $table = 'surat_pesanan_pembelian';

    protected $fillable = [
        'jenis',
        'pelanggan_id',
        'supplier_id',
        'nomor_pesanan_pembelian',
        'tanggal_pesanan_pembelian',
        'tanggal_terima',
        'tanggal_kirim_pesanan_pembelian',
        'nama_bagian_pembelian',
        'ttd_pengirim',
        'user_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Pelanggan::class, 'supplier_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pesananPembelianDetail()
    {
        return $this->hasMany(PesananPembelianDetail::class, 'spp_id');
    }

    public function suratPengirimanBarang()
    {
        return $this->hasMany(SuratPengirimanBarang::class, 'spp_id');
    }

    public function generatePdf()
    {
        $this->loadMissing(['supplier', 'pesananPembelianDetail', 'user']);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'administrasi.surat.surat-pesanan-pembelian.template-pdf',
            [
                'data' => $this,
                'profileUser' => $this->user,
            ]
        )->setPaper('A4', 'portrait');
    }
}
