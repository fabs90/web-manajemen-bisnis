<?php

namespace App\Models\MemoKredit;

use Illuminate\Database\Eloquent\Model;

class MemoKreditDetail extends Model
{
    protected $table = "memo_kredit_detail";
    protected $fillable = [
        "memo_kredit_id",
        "nama_barang",
        "kuantitas",
        "harga_satuan",
        "jumlah",
    ];

    public function memoKredit()
    {
        return $this->belongsTo(MemoKredit::class);
    }
}
