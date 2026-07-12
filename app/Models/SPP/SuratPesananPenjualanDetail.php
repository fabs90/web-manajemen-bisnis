<?php

namespace App\Models\SPP;

use App\Models\Barang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratPesananPenjualanDetail extends Model
{
    protected $table = 'surat_pesanan_penjualan_detail';

    protected $fillable = [
        'pesanan_penjualan_id',
        'barang_id',
        'nama_barang',
        'kuantitas',
        'harga',
        'diskon',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'kuantitas' => 'integer',
            'harga' => 'decimal:2',
            'diskon' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function pesananPenjualan(): BelongsTo
    {
        return $this->belongsTo(SuratPesananPenjualan::class, 'pesanan_penjualan_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
