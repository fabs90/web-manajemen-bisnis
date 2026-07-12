<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturPembelianDetail extends Model
{
    protected $table = 'retur_pembelian_detail';

    protected $guarded = ['id'];

    public function returPembelian()
    {
        return $this->belongsTo(ReturPembelian::class);
    }
}
