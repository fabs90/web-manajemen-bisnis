<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BukuBesarPiutang extends Model
{
    use HasFactory;
    protected $table = "buku_besar_piutang";
    protected $fillable = [
        "pelanggan_id",
        "kode",
        "uraian",
        "tanggal",
        "debit",
        "kredit",
        "saldo",
        "buku_besar_pendapatan_id",
        "buku_besar_pengeluaran_id",
        "neraca_awal_id",
        "neraca_akhir_id",
        "user_id",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function pendapatan()
    {
        return $this->belongsTo(
            BukuBesarPendapatan::class,
            "buku_besar_pendapatan_id",
        );
    }

    public function pengeluaran()
    {
        return $this->belongsTo(
            BukuBesarPengeluaran::class,
            "buku_besar_pengeluaran_id",
        );
    }

    public function neracaAwal()
    {
        return $this->belongsTo(NeracaAwal::class);
    }

    public function neracaAkhir()
    {
        return $this->belongsTo(NeracaAkhir::class);
    }
}
