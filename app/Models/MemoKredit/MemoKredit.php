<?php

namespace App\Models\MemoKredit;

use App\Models\Faktur\FakturPenjualan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MemoKredit extends Model
{
    protected $table = "memo_kredit";
    protected $fillable = [
        "nomor_memo",
        "tanggal",
        "faktur_penjualan_id",
        "alasan_pengembalian",
        "total",
        "user_id",
    ];

    public function fakturPenjualan()
    {
        return $this->belongsTo(FakturPenjualan::class, "faktur_penjualan_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function memoKreditDetail()
    {
        return $this->hasMany(MemoKreditDetail::class, "memo_kredit_id");
    }
}
