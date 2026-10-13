<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturPembelianDetail extends Model
{
    protected $table = 'retur_pembelian_detail';

    protected $fillable = [
        'retur_pembelian_id',
        'nama_barang',
        'kuantitas',
        'harga_satuan',
        'jumlah',
    ];

    public function returPembelian()
    {
        return $this->belongsTo(ReturPembelian::class);
    }
}
