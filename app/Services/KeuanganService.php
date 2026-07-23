<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalItem;
use App\Models\KartuGudang;
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

        // In periodic inventory system:
        // Pembelian = total of 'membeli_barang' debit to 1105
        $pembelianKredit = $this->getPembelianTotal($userId, $dateRange);
        $pembelianTunai = 0;
        $returPembelian = $this->getReturPembelianTotal($userId, $dateRange);
        $potonganPembelian = $operating['potonganPembelian'] ?? 0;

        $pembelianBersih = $pembelianKredit + $pembelianTunai - $returPembelian - $potonganPembelian;

        // HPP = Awal + Pembelian - Akhir
        $hpp = $inventory['persediaanAwal'] + $pembelianBersih - $inventory['persediaanAkhir'];

        // If there's any manual HPP (5101) entry, we might want to add it, but standard periodic ignores it or adds to it.
        $hppManual = $this->getHppTotal($userId, $dateRange);
        $hpp += $hppManual;

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
        $potonganPenjualanId = Account::where('user_id', $userId)->where('code', '4102')->value('id');

        $items = JournalItem::with('journalEntry')
            ->where('user_id', $userId)
            ->whereIn('account_id', $revenueAccounts)
            ->whereHas('journalEntry', function ($q) use ($dateRange) {
                $q->whereBetween('date', $dateRange)
                    ->where(function ($subQ) {
                        $subQ->where('transaction_type', '!=', 'pendapatan_lain')
                            ->orWhereNull('transaction_type');
                    });
            })
            ->get();

        $returTypes = ['retur_penjualan', 'return_penjualan'];

        $returItems = $items->filter(function ($item) use ($returTypes) {
            return in_array($item->journalEntry->transaction_type ?? '', $returTypes);
        });

        $potonganItems = $items->filter(function ($item) use ($potonganPenjualanId, $returTypes) {
            return $potonganPenjualanId &&
                $item->account_id == $potonganPenjualanId &&
                ! in_array($item->journalEntry->transaction_type ?? '', $returTypes);
        });

        $otherRevenueItems = $items->filter(function ($item) use ($potonganPenjualanId, $returTypes) {
            $isRetur = in_array($item->journalEntry->transaction_type ?? '', $returTypes);
            $isPotongan = $potonganPenjualanId && $item->account_id == $potonganPenjualanId;

            return ! $isRetur && ! $isPotongan;
        });

        // Normal balance for revenue is Credit.
        $totalPenjualan = $otherRevenueItems->sum('credit');

        // returPenjualan = sum of debit on revenue accounts from retur transactions
        $returPenjualan = $returItems->sum('debit') - $returItems->sum('credit');

        // potonganPenjualan = sum of debit on 4102 (normal balance is debit)
        $potonganPenjualan = $potonganItems->sum('debit') - $potonganItems->sum('credit');

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

        $barang = Barang::where('user_id', $userId)->get();

        $persediaanAwal = 0;
        $persediaanAccount = Account::where('user_id', $userId)->where('code', '1105')->first();
        if ($persediaanAccount) {
            $items = JournalItem::where('user_id', $userId)
                ->where('account_id', $persediaanAccount->id)
                ->whereHas('journalEntry', function ($q) use ($startDate) {
                    $q->where('date', '<', $startDate)
                        ->orWhere(function ($subQ) use ($startDate) {
                            $subQ->where('date', $startDate)
                                ->where('transaction_type', '!=', 'membeli_barang')
                                ->where('transaction_type', '!=', 'retur_pembelian');
                        });
                })
                ->get();
            $persediaanAwal = $items->sum('debit') - $items->sum('credit');
        }

        $persediaanAkhir = 0;
        foreach ($barang as $b) {
            $lastKartu = KartuGudang::where('barang_id', $b->id)
                ->where('tanggal', '<=', $endDate)
                ->latest('id')
                ->first();
            if ($lastKartu) {
                $persediaanAkhir += ($lastKartu->saldo_persatuan * $b->harga_beli_per_unit);
            }
        }

        return compact('persediaanAwal', 'persediaanAkhir');
    }

    private function getPembelianTotal(int $userId, array $dateRange): float
    {
        $persediaanAccount = Account::where('user_id', $userId)->where('code', '1105')->first();
        if (! $persediaanAccount) {
            return 0;
        }

        // Pembelian = Debit ke akun persediaan dari transaksi membeli_barang
        $items = JournalItem::where('user_id', $userId)
            ->where('account_id', $persediaanAccount->id)
            ->whereHas('journalEntry', function ($q) use ($dateRange) {
                $q->whereBetween('date', $dateRange)
                    ->where('transaction_type', 'membeli_barang');
            })
            ->get();

        return (float) $items->sum('debit');
    }

    private function getReturPembelianTotal(int $userId, array $dateRange): float
    {
        $persediaanAccount = Account::where('user_id', $userId)->where('code', '1105')->first();
        if (! $persediaanAccount) {
            return 0;
        }

        $items = JournalItem::where('user_id', $userId)
            ->where('account_id', $persediaanAccount->id)
            ->whereHas('journalEntry', function ($q) use ($dateRange) {
                $q->whereBetween('date', $dateRange)
                    ->where('transaction_type', 'retur_pembelian');
            })
            ->get();

        return (float) $items->sum('credit');
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
            ->with('journalEntry')
            ->get();

        // Potongan pembelian is recorded as credit on expense accounts in membeli_barang and membayar_hutang
        $potonganPembelianItems = $items->filter(function ($item) {
            return in_array($item->journalEntry->transaction_type ?? '', ['membeli_barang', 'membayar_hutang']);
        });

        $potonganPembelian = $potonganPembelianItems->sum('credit');

        // Normal balance for expense is Debit
        // Subtract potonganPembelian from total credit so we don't double count the reduction
        $biayaOperasional = $items->sum('debit') - ($items->sum('credit') - $potonganPembelian);

        // Get pendapatan_lain from revenue accounts
        $pendapatanLainItems = JournalItem::where('user_id', $userId)
            ->whereIn('account_id', Account::where('user_id', $userId)->where('category', 'revenue')->pluck('id'))
            ->whereHas('journalEntry', function ($q) use ($dateRange) {
                $q->whereBetween('date', $dateRange)
                    ->where('transaction_type', 'pendapatan_lain');
            })
            ->get();

        $pendapatanLain = $pendapatanLainItems->sum('credit') - $pendapatanLainItems->sum('debit');

        $biayaAdministrasiBank = 0; // If specific account exists, use it.

        return compact(
            'biayaOperasional',
            'pendapatanLain',
            'biayaAdministrasiBank',
            'potonganPembelian'
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

        // Gunakan saldo persediaan dari KartuGudang agar akurat dengan fisik gudang
        $barang = Barang::where('user_id', $userId)->get();
        $nilaiPersediaan = 0;
        foreach ($barang as $b) {
            $lastKartu = KartuGudang::where('barang_id', $b->id)
                ->where('tanggal', '<=', $date)
                ->latest('id')
                ->first();
            if ($lastKartu) {
                $nilaiPersediaan += ($lastKartu->saldo_persatuan * $b->harga_beli_per_unit);
            }
        }

        $tanah = $this->getAccountBalance($userId, '1203', $date);
        $kendaraan = $this->getAccountBalance($userId, '1202', $date);
        $peralatan = $this->getAccountBalance($userId, '1201', $date);

        $totalAktiva = $totalKas + $saldoPiutang + $nilaiPersediaan + $tanah + $kendaraan + $peralatan;

        // === PASIVA ===
        $hutangUsaha = $this->getAccountBalance($userId, '2101', $date);
        $hutangBank = $this->getAccountBalance($userId, '2201', $date);
        $saldoHutang = $hutangUsaha + $hutangBank;

        // Modal
        $modalAkun = $this->getAccountBalance($userId, '3100', $date);

        // Laba all time (to ensure balance sheet balances correctly)
        // If there's no closing entry system, past year profits must be merged into Modal.
        $allTimeLabaRugi = $this->hitungLabaRugi('1970-01-01', $date);

        // Current period profit for display
        $dataLabaRugi = $this->hitungLabaRugi(date('Y-01-01', strtotime($date)), $date);

        // Display taxes accumulated over all time
        $pajak = $allTimeLabaRugi['pajak'];
        $labaBersih = $dataLabaRugi['labaSetelahPajak'];

        // Past years profit not yet closed to modal
        $labaTahunLalu = $allTimeLabaRugi['labaSetelahPajak'] - $labaBersih;

        $modal = $modalAkun + $labaTahunLalu;

        $totalPasiva = $saldoHutang + $pajak + $labaBersih + $modal;

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
