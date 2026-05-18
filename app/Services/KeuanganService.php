<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalItem;
use Illuminate\Support\Facades\Auth;

class KeuanganService
{
    private const TAX_RATE = 0.15;

    public function hitungLabaRugi($startDate = null, $endDate = null)
    {
        $userId = Auth::id();
        $startDate = $startDate ?? now()->startOfYear()->format('Y-m-d');
        $endDate = $endDate ?? now()->endOfYear()->format('Y-m-d');
        $dateRange = [$startDate, $endDate];

        // === GATHER METRICS ===
        $sales = $this->getSalesMetrics($userId, $dateRange);
        $inventory = $this->getInventoryMetrics($userId, $dateRange);
        $operating = $this->getOperatingMetrics($userId, $dateRange);

        // === CALCULATE SUMMARY ===
        $penjualanBersih =
            $sales['totalPenjualan'] -
            $sales['returPenjualan'] -
            $sales['potonganPenjualan'];

        // In perpetual inventory system:
        // HPP is recorded directly.
        $hpp = $this->getHppTotal($userId, $dateRange);

        // Purchases (for report detail)
        // Purchases = Ending Inventory - Beginning Inventory + HPP
        $pembelianBersih = $inventory['persediaanAkhir'] - $inventory['persediaanAwal'] + $hpp;

        // Split into components for report consistency if possible
        $pembelianKredit = $pembelianBersih;
        $pembelianTunai = 0;
        $returPembelian = 0;
        $potonganPembelian = 0;

        $labaKotor = $penjualanBersih - $hpp;

        $labaOperasional = $labaKotor - $operating['biayaOperasional'];

        $labaSebelumPajak =
            $labaOperasional +
            $operating['pendapatanLain'] -
            $operating['biayaAdministrasiBank'];

        $pajak = $labaSebelumPajak > 0 ? $labaSebelumPajak * self::TAX_RATE : 0;
        $labaSetelahPajak = $labaSebelumPajak - $pajak;

        return array_merge($sales, $inventory, $operating, [
            'penjualanBersih' => $penjualanBersih,
            'pembelianKredit' => $pembelianKredit,
            'pembelianTunai' => $pembelianTunai,
            'returPembelian' => $returPembelian,
            'potonganPembelian' => $potonganPembelian,
            'pembelianBersih' => $pembelianBersih,
            'hpp' => $hpp,
            'labaKotor' => $labaKotor,
            'labaOperasional' => $labaOperasional,
            'labaSebelumPajak' => $labaSebelumPajak,
            'pajak' => $pajak,
            'labaSetelahPajak' => $labaSetelahPajak,
        ]);
    }

    private function getSalesMetrics(int $userId, array $dateRange): array
    {
        // Revenue accounts are category 'revenue'
        $revenueAccounts = Account::where('user_id', $userId)->where('category', 'revenue')->pluck('id');

        $items = JournalItem::where('user_id', $userId)
            ->whereIn('account_id', $revenueAccounts)
            ->whereHas('journalEntry', function ($q) use ($dateRange) {
                $q->whereBetween('date', $dateRange);
            })
            ->get();

        // Normal balance for revenue is Credit.
        // totalPenjualan = sum of credit
        $totalPenjualan = $items->sum('credit');

        // returPenjualan = sum of debit
        $returPenjualan = $items->sum('debit');

        $potonganPenjualan = 0;
        $penjualanKredit = $totalPenjualan; // For display
        $penjualanTunai = 0;
        $bungaPenjualan = 0;

        return compact(
            'penjualanKredit',
            'penjualanTunai',
            'bungaPenjualan',
            'totalPenjualan',
            'returPenjualan',
            'potonganPenjualan',
        );
    }

    private function getInventoryMetrics(int $userId, array $dateRange): array
    {
        $startDate = $dateRange[0];
        $endDate = $dateRange[1];

        $persediaanAwal = $this->getAccountBalance($userId, '1105', date('Y-m-d', strtotime($startDate.' -1 day')));
        $persediaanAkhir = $this->getAccountBalance($userId, '1105', $endDate);

        return compact('persediaanAwal', 'persediaanAkhir');
    }

    private function getHppTotal(int $userId, array $dateRange): float
    {
        $hppAccount = Account::where('user_id', $userId)->where('code', '5101')->first();
        if (! $hppAccount) {
            return 0;
        }

        $items = JournalItem::where('user_id', $userId)
            ->where('account_id', $hppAccount->id)
            ->whereHas('journalEntry', function ($q) use ($dateRange) {
                $q->whereBetween('date', $dateRange);
            })
            ->get();

        // Normal balance is Debit
        return $items->sum('debit') - $items->sum('credit');
    }

    private function getOperatingMetrics(int $userId, array $dateRange): array
    {
        // Expense accounts are category 'expense', but excluding HPP (5101)
        $expenseAccounts = Account::where('user_id', $userId)
            ->where('category', 'expense')
            ->where('code', '!=', '5101')
            ->pluck('id');

        $items = JournalItem::where('user_id', $userId)
            ->whereIn('account_id', $expenseAccounts)
            ->whereHas('journalEntry', function ($q) use ($dateRange) {
                $q->whereBetween('date', $dateRange);
            })
            ->get();

        // Normal balance for expense is Debit
        $biayaOperasional = $items->sum('debit') - $items->sum('credit');

        $pendapatanLain = 0;
        $biayaAdministrasiBank = 0; // If specific account exists, use it.

        return compact(
            'biayaOperasional',
            'pendapatanLain',
            'biayaAdministrasiBank',
        );
    }

    public function hitungNeraca($date = null)
    {
        $userId = Auth::id();
        $date = $date ?? now()->format('Y-m-d');

        // === AKTIVA ===
        $kas = $this->getAccountBalance($userId, '1101', $date);
        $kasKecil = $this->getAccountBalance($userId, '1102', $date);
        $bank = $this->getAccountBalance($userId, '1103', $date);
        $totalKas = $kas + $kasKecil + $bank;

        $saldoPiutang = $this->getAccountBalance($userId, '1104', $date);
        $nilaiPersediaan = $this->getAccountBalance($userId, '1105', $date);

        $tanah = $this->getAccountBalance($userId, '1203', $date);
        $kendaraan = $this->getAccountBalance($userId, '1202', $date);
        $peralatan = $this->getAccountBalance($userId, '1201', $date);

        $totalAktiva = $totalKas + $saldoPiutang + $nilaiPersediaan + $tanah + $kendaraan + $peralatan;

        // === PASIVA ===
        $saldoHutang = $this->getAccountBalance($userId, '2101', $date);

        // Modal & Retained Earnings
        $modal = $this->getAccountBalance($userId, '3100', $date);
        $labaDitahan = $this->getAccountBalance($userId, '3200', $date);

        // Current period profit
        $dataLabaRugi = $this->hitungLabaRugi(date('Y-01-01', strtotime($date)), $date);
        $pajak = $dataLabaRugi['pajak'];
        $labaBersih = $dataLabaRugi['labaSetelahPajak'];

        $totalPasiva = $saldoHutang + $pajak + $labaBersih + $modal + $labaDitahan;

        return compact(
            'totalKas',
            'saldoPiutang',
            'nilaiPersediaan',
            'tanah',
            'kendaraan',
            'peralatan',
            'totalAktiva',
            'saldoHutang',
            'pajak',
            'labaBersih',
            'modal',
            'labaDitahan',
            'totalPasiva'
        );
    }

    private function getAccountBalance(int $userId, string $accountCode, string $date): float
    {
        $account = Account::where('user_id', $userId)->where('code', $accountCode)->first();
        if (! $account) {
            return 0;
        }

        $items = JournalItem::where('user_id', $userId)
            ->where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($date) {
                $q->where('date', '<=', $date);
            })
            ->get();

        $debit = $items->sum('debit');
        $credit = $items->sum('credit');

        if ($account->normal_balance === 'debit') {
            return (float) ($debit - $credit);
        } else {
            return (float) ($credit - $debit);
        }
    }
}
