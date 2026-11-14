<?php

namespace App\Models;

use App\Traits\ClearsDashboardCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KartuGudang extends Model
{
    use ClearsDashboardCache, HasFactory;

    protected $table = 'kartu_gudang';

    protected $fillable = [
        'barang_id',
        'tanggal',
        'diterima',
        'dikeluarkan',
        'user_id',
        'uraian',
        'saldo_persatuan',
        'saldo_perkemasan',
        'journal_entry_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function formatSaldoPerkemasan(?int $unitPerKemasan = null): string
    {
        $unitPerKemasan = $unitPerKemasan ?? $this->barang?->jumlah_unit_per_kemasan ?? 1;

        if ($unitPerKemasan <= 1) {
            return $this->saldo_persatuan.' unit';
        }

        $saldo = (int) $this->saldo_persatuan;
        if ($saldo === 0) {
            return '0 kemas';
        }

        $isNegative = $saldo < 0;
        $absSaldo = abs($saldo);

        $kemas = intdiv($absSaldo, $unitPerKemasan);
        $sisa = $absSaldo % $unitPerKemasan;

        $prefix = $isNegative ? '-' : '';

        if ($sisa === 0) {
            return "{$prefix}{$kemas} kemas";
        }

        return "{$prefix}{$kemas} kemas + {$sisa} unit";
    }

    /**
     * Recalculate running balances (saldo_persatuan and saldo_perkemasan) for a Barang.
     */
    public static function recalculateBalances(int $barangId): void
    {
        $barang = Barang::find($barangId);
        $unitPerKemasan = (int) ($barang?->jumlah_unit_per_kemasan ?? 1);

        $records = self::where('barang_id', $barangId)
            ->orderBy('id', 'asc')
            ->get();

        $runningBalance = 0;

        foreach ($records as $record) {
            $runningBalance += ((int) $record->diterima - (int) $record->dikeluarkan);
            $saldoKemasan = $unitPerKemasan > 0
                ? (int) ceil($runningBalance / $unitPerKemasan)
                : 0;

            if ((int) $record->saldo_persatuan !== $runningBalance || (int) $record->saldo_perkemasan !== $saldoKemasan) {
                $record->update([
                    'saldo_persatuan' => $runningBalance,
                    'saldo_perkemasan' => $saldoKemasan,
                ]);
            }
        }
    }
}
