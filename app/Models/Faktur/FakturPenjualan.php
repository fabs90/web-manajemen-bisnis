<?php

namespace App\Models\Faktur;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class FakturPenjualan extends Model
{
    protected $table = "faktur_penjualan";
    protected $fillable = [
        "kode_faktur",
        "nama_pembeli",
        "alamat_pembeli",
        "nomor_pesanan",
        "nomor_spb",
        "tanggal",
        "jenis_pengiriman",
        "nama_bagian_penjualan",
        "user_id",
    ];

    public function fakturPenjualanDetail()
    {
        return $this->hasMany(FakturPenjualanDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
