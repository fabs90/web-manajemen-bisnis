<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalItem;
use App\Models\Pelanggan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PernyataanPiutangService
{
    /**
     * Mendapatkan daftar pelanggan yang memiliki saldo piutang (berdasarkan JournalItem).
     *
     * @return Collection
     */
    public function getDaftarPiutang()
    {
        $userId = auth()->id();

        // Cari akun Piutang Usaha (1104)
        $account = Account::where('user_id', $userId)
            ->where('code', '1104')
            ->first();

        if (! $account) {
            return collect();
        }

        // Hitung saldo piutang per pelanggan (sub_ledger)
        // Saldo = sum(debit) - sum(credit)
        return JournalItem::where('user_id', $userId)
            ->where('account_id', $account->id)
            ->where('sub_ledger_type', Pelanggan::class)
            ->select('sub_ledger_id', 'sub_ledger_type', DB::raw('SUM(debit) - SUM(credit) as total_piutang'))
            ->groupBy('sub_ledger_id', 'sub_ledger_type')
            ->having('total_piutang', '>', 0)
            ->with('subLedger')
            ->get()
            ->filter(function ($item) {
                return $item->subLedger !== null;
            })
            ->map(function ($item) {
                return (object) [
                    'pelanggan' => $item->subLedger,
                    'total_piutang' => $item->total_piutang,
                ];
            });
    }

    /**
     * Mendapatkan total piutang untuk pelanggan tertentu.
     *
     * @return float
     */
    public function getTotalPiutangPelanggan(int $pelangganId)
    {
        $userId = auth()->id();

        $account = Account::where('user_id', $userId)
            ->where('code', '1104')
            ->first();

        if (! $account) {
            return 0;
        }

        return (float) JournalItem::where('user_id', $userId)
            ->where('account_id', $account->id)
            ->where('sub_ledger_type', Pelanggan::class)
            ->where('sub_ledger_id', $pelangganId)
            ->sum(DB::raw('debit - credit'));
    }
}
