<?php

namespace App\Models\Faktur;

use App\Models\MemoKredit\MemoKredit;
use App\Models\Pelanggan;
use App\Models\SPB\SuratPengirimanBarang;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class FakturPenjualan extends Model
{
    protected $table = "faktur_penjualan";
    protected $fillable = [
        "kode_faktur",
        "pelanggan_id",
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

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, "pelanggan_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function suratPengirimanBarang()
    {
        return $this->hasOne(SuratPengirimanBarang::class);
    }

    public function memoKredit()
    {
        return $this->hasOne(MemoKredit::class);
    }
}
