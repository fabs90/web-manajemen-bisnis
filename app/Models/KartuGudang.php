<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KartuGudang extends Model
{
    use HasFactory;
    protected $table = "kartu_gudang";
    protected $fillable = [
        "barang_id",
        "tanggal",
        "diterima",
        "dikeluarkan",
        "user_id",
        "uraian",
        "saldo_persatuan",
        "saldo_perkemasan",
        "user_id",
    ];
    protected $casts = [
        "tanggal" => "date",
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }

    public function bukuBesarPendapatan()
    {
        return $this->belongsTo(BukuBesarPendapatan::class);
    }
    public function bukuBesarPengeluaran()
    {
        return $this->belongsTo(BukuBesarPengeluaran::class);
    }
}