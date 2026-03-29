<?php

namespace App\Models\SPP;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Pelanggan, User};
use App\Models\SPB\SuratPengirimanBarang;

class PesananPembelian extends Model
{
    protected $table = "pesanan_pembelian";
    protected $fillable = [
        "jenis",
        "pelanggan_id",
        "supplier_id",
        "nomor_pesanan_pembelian",
        "tanggal_pesanan_pembelian",
        "tanggal_terima",
        "tanggal_kirim_pesanan_pembelian",
        "nama_bagian_pembelian",
        "user_id",
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pesananPembelianDetail()
    {
        return $this->hasMany(PesananPembelianDetail::class, "spp_id");
    }

    public function suratPengirimanBarang()
    {
        return $this->hasMany(SuratPengirimanBarang::class, "spp_id");
    }
}