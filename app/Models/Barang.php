<?php

namespace App\Models;

use App\Traits\ClearsDashboardCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use ClearsDashboardCache, HasFactory;

    protected $table = 'barang';

    protected $fillable = [
        'kode_barang',
        'nama',
        'user_id',
        'jumlah_max',
        'jumlah_min',
        'jumlah_unit_per_kemasan',
        'harga_beli_per_unit',
        'harga_beli_per_kemas',
        'harga_jual_per_unit',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kartuGudang()
    {
        return $this->hasMany(KartuGudang::class);
    }

    public function latestKartuGudang()
    {
        return $this->hasOne(KartuGudang::class)->latestOfMany();
    }

    public function getSaldoAkhir()
    {
        $latestKartu = $this->kartuGudang()
            ->orderBy('created_at', 'desc')
            ->first();

        return $latestKartu ? $latestKartu->saldo_persatuan : 0;
    }

    public function formatSaldoPerkemasan(?int $saldoUnit = 0): string
    {
        $unitPerKemasan = $this->jumlah_unit_per_kemasan ?: 1;

        if ($unitPerKemasan <= 1) {
            return $saldoUnit.' unit';
        }

        if ($saldoUnit === 0) {
            return '0 kemas';
        }

        $isNegative = $saldoUnit < 0;
        $absSaldo = abs($saldoUnit);

        $kemas = intdiv($absSaldo, $unitPerKemasan);
        $sisa = $absSaldo % $unitPerKemasan;

        $prefix = $isNegative ? '-' : '';

        if ($sisa === 0) {
            return "{$prefix}{$kemas} kemas";
        }

        return "{$prefix}{$kemas} kemas + {$sisa} unit";
    }
}
