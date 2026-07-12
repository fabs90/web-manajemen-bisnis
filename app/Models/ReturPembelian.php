<?php

namespace App\Models;

use App\Models\SPP\SuratPesananPembelian;
use Illuminate\Database\Eloquent\Model;

class ReturPembelian extends Model
{
    protected $table = 'retur_pembelian';

    protected $guarded = ['id'];

    public function detail()
    {
        return $this->hasMany(ReturPembelianDetail::class);
    }

    public function pesananPembelian()
    {
        return $this->belongsTo(SuratPesananPembelian::class, 'pesanan_pembelian_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Pelanggan::class, 'supplier_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}