<?php

namespace App\Models\SPP;

use App\Models\Pelanggan;
use App\Models\SPB\SuratPengirimanBarang;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PesananPembelian extends Model
{
    protected $table = "pesanan_pembelian";
    protected $fillable = [
        "pelanggan_id",
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
