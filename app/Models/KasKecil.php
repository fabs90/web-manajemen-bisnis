<?php

namespace App\Models;

use App\Traits\ClearsDashboardCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasKecil extends Model
{
    use HasFactory, ClearsDashboardCache;

    protected $table = 'kas_kecil';

    protected $fillable = [
        'user_id',
        'tanggal',
        'nomor_referensi',
        'penerimaan',
        'pengeluaran',
        'saldo_akhir',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kasKecilDetail()
    {
        return $this->hasMany(KasKecilDetail::class);
    }

    public function kasKecilFormulir()
    {
        return $this->hasMany(KasKecilFormulir::class);
    }

    public function kasKecilLog()
    {
        return $this->hasMany(PengisianKasKecilLog::class);
    }

    /**
     * Recalculate running balances of Petty Cash records for a user after a specific record is deleted.
     */
    public static function recalculateBalances(int $userId, int $afterId): void
    {
        $previousRecord = self::where('user_id', $userId)
            ->where('id', '<', $afterId)
            ->orderBy('id', 'desc')
            ->first();

        $runningBalance = 0;
        if ($previousRecord !== null) {
            $runningBalance = $previousRecord->saldo_akhir;
        }

        $subsequentRecords = self::where('user_id', $userId)
            ->where('id', '>', $afterId)
            ->orderBy('id', 'asc')
            ->get();

        foreach ($subsequentRecords as $record) {
            $runningBalance = $runningBalance + $record->penerimaan - $record->pengeluaran;
            $record->update([
                'saldo_akhir' => $runningBalance,
            ]);
        }
    }
}
