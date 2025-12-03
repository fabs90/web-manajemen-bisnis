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
        "spb_id",
        "kode_faktur",
        "tanggal_faktur",
        "nama_bagian_penjualan",
        "user_id",
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function suratPengirimanBarang()
    {
        return $this->belongsTo(SuratPengirimanBarang::class, "spb_id", "id");
    }

    public function fakturPenjualanDetail()
    {
        return $this->hasMany(FakturPenjualanDetail::class);
    }

    public function memoKredit()
    {
        return $this->hasOne(MemoKredit::class);
    }
}
