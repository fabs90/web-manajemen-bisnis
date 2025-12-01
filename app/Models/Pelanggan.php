<?php

namespace App\Models;

use App\Models\Faktur\FakturPenjualan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;
    protected $table = "pelanggan";
    protected $fillable = [
        "nama",
        "kontak",
        "alamat",
        "email",
        "jenis",
        "user_id",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bukuBesarPiutang()
    {
        return $this->hasMany(BukuBesarPiutang::class);
    }

    public function bukuBesarHutang()
    {
        return $this->hasMany(BukuBesarHutang::class);
    }

    public function fakturPenjualan()
    {
        return $this->hasMany(FakturPenjualan::class);
    }
}
