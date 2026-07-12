<?php

namespace App\Models\SPP;

use App\Models\Barang;
use Illuminate\Database\Eloquent\Model;

class SuratPesananPembelianDetail extends Model
{
    protected $table = 'surat_pesanan_pembelian_detail';

    protected $fillable = [
        'spp_id',
        'barang_id',
        'nama_barang',
        'kuantitas',
        'harga',
        'diskon',
        'total',
    ];

    public function pesananPembelian()
    {
        return $this->belongsTo(SuratPesananPembelian::class, 'spp_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
