<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\KartuGudang;
use App\Models\KasirTransactionLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class KeuanganService
{
    private const TAX_RATE = 0.15;

    private const UMKM_NELAYAN_TAX_THRESHOLD = 500000000;

    private const UMKM_NELAYAN_TAX_RATE = 0.005;

    /**
     * Hitung tarif pajak yang berlaku berdasarkan role dan omzet bruto periode.
     */
    public function getPajakRate(float $omzet, float $labaSebelumPajak = 0, string $userRole = 'ukm'): float
    {
        if (in_array($userRole, ['ukm', 'nelayan'], true)) {
            return $omzet > self::UMKM_NELAYAN_TAX_THRESHOLD ? self::UMKM_NELAYAN_TAX_RATE : 0.0;
        }

        return $labaSebelumPajak > 0 ? self::TAX_RATE : 0.0;
    }

    /**
     * Hitung nominal pajak terpusat berdasarkan role, omzet bruto, dan laba sebelum pajak.
     */
    public function hitungPajak(float $omzet, float $labaSebelumPajak = 0, string $userRole = 'ukm'): float
    {
        if (in_array($userRole, ['ukm', 'nelayan'], true)) {
            if ($omzet > self::UMKM_NELAYAN_TAX_THRESHOLD) {
                return $omzet * self::UMKM_NELAYAN_TAX_RATE;
            }

            return 0.0;
        }

        return $labaSebelumPajak > 0 ? $labaSebelumPajak * self::TAX_RATE : 0.0;
    }

    public function hitungLabaRugi($startDate = null, $endDate = null, ?int $userId = null)
    {
        $userId = $userId ?? Auth::id();
        $startDate = $startDate ?? now()->startOfYear()->format('Y-m-d');
        $endDate = $endDate ?? now()->endOfYear()->format('Y-m-d');
        $dateRange = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];

        // === GATHER METRICS ===
        $sales = $this->getSalesMetrics($userId, $dateRange);
        $inventory = $this->getInventoryMetrics($userId, $dateRange);
        $operating = $this->getOperatingMetrics($userId, $dateRange);

        // === CALCULATE SUMMARY ===
        $penjualanBersih =
            $sales['totalPenjualan'] -
            $sales['returPenjualan'] -
            $sales['potonganPenjualan'];

        $userRole = User::where('id', $userId)->value('role') ?? 'ukm';
        $isNelayan = $userRole === 'nelayan';

        // For fisherman (nelayan), fish catches have no buy price (only sell price).
        // Catch results are direct products rather than purchased merchandise.
        // Therefore, purchasing merchandise and merchandise HPP do not apply (HPP = 0).
        if ($isNelayan) {
            $pembelianKredit = 0.0;
            $pembelianTunai = 0.0;
            $returPembelian = 0.0;
            $potonganPembelian = 0.0;
            $pembelianBersih = 0.0;
            $hpp = 0.0;
            $labaKotor = $penjualanBersih;
        } else {
            // In periodic inventory system:
            // Pembelian = total of 'membeli_barang' debit to 1105
            $pembelianKredit = $this->getPembelianKreditTotal($userId, $dateRange);
            $pembelianTunai = $this->getPembelianTunaiTotal($userId, $dateRange);
            $returPembelian = $this->getReturPembelianTotal($userId, $dateRange);
            $potonganPembelian = $operating['potonganPembelian'] ?? 0;

            $pembelianBersih = $pembelianKredit + $pembelianTunai - $returPembelian - $potonganPembelian;

            // HPP: Gunakan total HPP dari jurnal (perpetual) jika ada, atau gunakan rumus periodik (Awal + Pembelian - Akhir)
            $hppPerpetual = $this->getHppTotal($userId, $dateRange);
            if ($hppPerpetual > 0) {
                $hpp = $hppPerpetual;
            } else {
                $hpp = max(0, $inventory['persediaanAwal'] + $pembelianBersih - $inventory['persediaanAkhir']);
            }

            $labaKotor = $penjualanBersih - $hpp;
        }

        // $labaOperasional = $labaKotor - $operating['biayaOperasional'];

        $labaSebelumPajak = $labaKotor + $operating['totalPendapatanBiayaLain'];

        $omzet = (float) ($sales['totalPenjualan'] + max(0, $operating['pendapatanLain']));
        $pajakRate = $this->getPajakRate($omzet, (float) $labaSebelumPajak, $userRole);
        $pajak = $this->hitungPajak($omzet, (float) $labaSebelumPajak, $userRole);
        $labaSetelahPajak = $labaSebelumPajak - $pajak;

        return array_merge($sales, $inventory, $operating, [
            'penjualanBersih' => $penjualanBersih,
            'omzet' => $omzet,
            'pembelianKredit' => $pembelianKredit,
            'pembelianTunai' => $pembelianTunai,
            'returPembelian' => $returPembelian,
            'potonganPembelian' => $potonganPembelian,
            'pembelianBersih' => $pembelianBersih,
            'hpp' => $hpp,
            'labaKotor' => $labaKotor,
            // 'labaOperasional' => $labaOperasional,
            'labaSebelumPajak' => $labaSebelumPajak,
            'pajakRate' => $pajakRate,
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
                !in_array($item->journalEntry->transaction_type ?? '', $returTypes);
        });

        $otherRevenueItems = $items->filter(function ($item) use ($potonganPenjualanId, $returTypes) {
            $isRetur = in_array($item->journalEntry->transaction_type ?? '', $returTypes);
            $isPotongan = $potonganPenjualanId && $item->account_id == $potonganPenjualanId;

            return !$isRetur && !$isPotongan;
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
        $userRole = User::where('id', $userId)->value('role') ?? 'ukm';

        $barang = Barang::where('user_id', $userId)->get();

        $persediaanAwal = 0;
        foreach ($barang as $b) {
            // Mengambil baris pertama di Kartu Gudang sebagai Saldo Awal
            $firstKartu = KartuGudang::where('barang_id', $b->id)
                ->oldest('id')
                ->first();
            if ($firstKartu) {
                $persediaanAwal += ($firstKartu->saldo_persatuan * $b->hargaPersediaan($userRole));
            }
        }

        $persediaanAkhir = 0;
        foreach ($barang as $b) {
            $lastKartu = KartuGudang::where('barang_id', $b->id)
                ->where('tanggal', '<=', $endDate)
                ->latest('id')
                ->first();
            if ($lastKartu) {
                $persediaanAkhir += ($lastKartu->saldo_persatuan * $b->hargaPersediaan($userRole));
            }
        }

        return compact('persediaanAwal', 'persediaanAkhir');
    }

    private function getPembelianTunaiTotal(int $userId, array $dateRange): float
    {
        $entries = JournalEntry::where('user_id', $userId)
            ->where('transaction_type', 'membeli_barang')
            ->whereBetween('date', $dateRange)
            ->with(['items.account'])
            ->get();

        $totalTunai = 0;
        foreach ($entries as $entry) {
            $pengeluaranKas = $entry->items->where('account.code', '1101')->sum('credit') + $entry->items->where('account.code', '1103')->sum('credit');
            $keluarKasKecil = $entry->items->where('account.code', '1102')->sum('credit');

            if ($pengeluaranKas > 0 || $keluarKasKecil > 0) {
                $biayaKotor = $entry->items->filter(function ($item) {
                    return $item->debit > 0 && !in_array($item->account->code, ['1101', '1103', '2101']);
                })->sum('debit');
                $totalTunai += $biayaKotor;
            }
        }

        return (float) $totalTunai;
    }

    private function getPembelianKreditTotal(int $userId, array $dateRange): float
    {
        $hutangAccount = Account::where('user_id', $userId)->where('code', '2101')->first();
        if (!$hutangAccount) {
            return 0;
        }

        $kredit = JournalItem::where('user_id', $userId)
            ->where('account_id', $hutangAccount->id)
            ->whereHas('journalEntry', function ($q) use ($dateRange) {
                $q->whereBetween('date', $dateRange)
                    ->whereIn('transaction_type', ['membeli_barang', 'pemesanan-barang']);
            })
            ->sum('credit');

        return (float) $kredit;
    }

    private function getReturPembelianTotal(int $userId, array $dateRange): float
    {
        $persediaanAccount = Account::where('user_id', $userId)->where('code', '1105')->first();
        if (!$persediaanAccount) {
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
        if (!$hppAccount) {
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

        // BIAYA OPERASIONAL: Seluruh beban non-pembelian barang
        $biayaOperasionalItems = $items->filter(function ($item) {
            return !in_array($item->journalEntry->transaction_type ?? '', ['membeli_barang', 'membayar_hutang']);
        });

        $biayaOperasional = $biayaOperasionalItems->sum('debit') - $biayaOperasionalItems->sum('credit');

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
        $totalPendapatanBiayaLain = $pendapatanLain - $biayaOperasional - $biayaAdministrasiBank;

        return compact(
            'biayaOperasional',
            'pendapatanLain',
            'biayaAdministrasiBank',
            'totalPendapatanBiayaLain',
            'potonganPembelian'
        );
    }

    public function hitungNeraca($date = null, ?int $userId = null)
    {
        $userId = $userId ?? Auth::id();
        $date = $date ?? now()->format('Y-m-d');

        // === AKTIVA ===
        $kas = $this->getAccountBalance($userId, '1101', $date);
        $kasKecil = $this->getAccountBalance($userId, '1102', $date);
        $bank = $this->getAccountBalance($userId, '1103', $date);
        $totalKas = $kas + $kasKecil + $bank;

        $saldoPiutang = $this->getAccountBalance($userId, '1104', $date);

        // Gunakan saldo persediaan dari KartuGudang agar akurat dengan fisik gudang
        $userRole = User::where('id', $userId)->value('role') ?? 'ukm';
        $barang = Barang::where('user_id', $userId)->get();
        $nilaiPersediaan = 0;
        foreach ($barang as $b) {
            $lastKartu = KartuGudang::where('barang_id', $b->id)
                ->whereDate('tanggal', '<=', $date)
                ->latest('id')
                ->first();
            if ($lastKartu) {
                $nilaiPersediaan += ($lastKartu->saldo_persatuan * $b->hargaPersediaan($userRole));
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

        // Pajak & laba periode tahun berjalan
        $dataLabaRugi = $this->hitungLabaRugi(date('Y-01-01', strtotime($date)), $date, $userId);

        $pajak = $dataLabaRugi['pajak'];
        $labaBersih = $dataLabaRugi['labaSetelahPajak'];

        // Modal dihitung sesuai ketentuan: Total Aktiva - Hutang - Pajak - Laba
        $modal = $totalAktiva - $saldoHutang - $pajak - $labaBersih;

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
        if (!$account) {
            return 0;
        }

        $items = JournalItem::where('user_id', $userId)
            ->where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($date) {
                $q->whereDate('date', '<=', $date);
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

    /**
     * Synchronously delete a Barang (Hasil Tangkapan / Master Barang) along with its
     * KartuGudang records and clean up or adjust all related financial journals,
     * KasirTransactionLogs, and General Ledger entries.
     */
    public function syncDeleteBarang(Barang $barang): void
    {
        $userId = (int) $barang->user_id;
        $namaBarang = $barang->nama;

        $kartuGudangList = KartuGudang::where('barang_id', $barang->id)
            ->with('barang')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($kartuGudangList as $kartu) {
            $this->syncDeleteKartuGudang($kartu, false);
        }

        $hasOtherSameName = Barang::where('user_id', $userId)
            ->where('id', '!=', $barang->id)
            ->where('nama', $namaBarang)
            ->exists();

        if (!$hasOtherSameName) {
            JournalEntry::where('user_id', $userId)
                ->where('transaction_type', 'penyesuaian-kartu-gudang')
                ->where('description', 'like', 'Penyesuaian Kartu Gudang: ' . $namaBarang . ' (%')
                ->get()
                ->each(fn(JournalEntry $entry) => $entry->delete());
        }

        $barang->delete();

        Cache::forget("dashboard_data_{$userId}");
    }

    /**
     * Synchronously delete a single KartuGudang record and clean up or adjust its
     * related JournalEntry, JournalItems, and KasirTransactionLog.
     */
    public function syncDeleteKartuGudang(KartuGudang $kartuGudang, bool $recalculateBalances = true): void
    {
        $barang = $kartuGudang->relationLoaded('barang')
            ? $kartuGudang->barang
            : Barang::find($kartuGudang->barang_id);

        $barangId = (int) $kartuGudang->barang_id;
        $userId = (int) $kartuGudang->user_id;

        $entry = $kartuGudang->journal_entry_id
            ? JournalEntry::with(['items.account', 'kasir'])->find($kartuGudang->journal_entry_id)
            : null;

        if ($barang) {
            $legacyQuery = JournalEntry::where('user_id', $userId)
                ->where('transaction_type', 'penyesuaian-kartu-gudang')
                ->whereDate('date', $kartuGudang->tanggal)
                ->where('description', 'Penyesuaian Kartu Gudang: ' . $barang->nama . ' (' . $kartuGudang->uraian . ')');

            if ($entry && $entry->transaction_type === 'penyesuaian-kartu-gudang') {
                $legacyQuery->where('id', '!=', $entry->id);
            }

            if (!$entry || $entry->transaction_type !== 'penyesuaian-kartu-gudang') {
                $legacyEntry = $legacyQuery->first();
                $legacyEntry?->delete();
            }
        }

        if ($entry) {
            $this->adjustOrDeleteLinkedJournalEntry($entry, $kartuGudang, $barang);
        }

        $kartuGudang->delete();

        if ($recalculateBalances) {
            KartuGudang::recalculateBalances($barangId);
        }

        Cache::forget("dashboard_data_{$userId}");
    }

    /**
     * Adjust or delete a JournalEntry when one of its linked KartuGudang records is removed.
     */
    private function adjustOrDeleteLinkedJournalEntry(
        JournalEntry $entry,
        KartuGudang $kartuGudang,
        ?Barang $barang
    ): void {
        $type = $entry->transaction_type;

        if ($type === 'penyesuaian-kartu-gudang') {
            $entry->delete();

            return;
        }

        if ($type === 'neraca_awal') {
            $userRole = User::where('id', $entry->user_id)->value('role') ?? 'ukm';
            $netQty = (float) $kartuGudang->diterima - (float) $kartuGudang->dikeluarkan;
            $itemValue = max(0.0, $netQty * ($barang ? $barang->hargaPersediaan($userRole) : 0.0));

            if ($itemValue > 0) {
                $persediaanItem = $entry->items->first(fn(JournalItem $i) => $i->account?->code === '1105' && $i->debit > 0);
                $modalItem = $entry->items->first(fn(JournalItem $i) => $i->account?->code === '3100' && $i->credit > 0);

                if ($persediaanItem) {
                    $newPersediaanDebit = max(0.0, (float) $persediaanItem->debit - $itemValue);
                    $persediaanItem->account?->update(['opening_balance' => $newPersediaanDebit]);

                    if ($newPersediaanDebit <= 0) {
                        $persediaanItem->delete();
                    } else {
                        $persediaanItem->update(['debit' => $newPersediaanDebit]);
                    }
                }

                if ($modalItem) {
                    $newModalCredit = max(0.0, (float) $modalItem->credit - $itemValue);
                    $modalItem->account?->update(['opening_balance' => $newModalCredit]);

                    if ($newModalCredit <= 0) {
                        $modalItem->delete();
                    } else {
                        $modalItem->update(['credit' => $newModalCredit]);
                    }
                }
            }

            $entry->load('items');
            if ($entry->items->isEmpty() || (float) $entry->items->sum('debit') <= 0) {
                $entry->delete();
            }

            return;
        }

        $remainingKartu = KartuGudang::where('journal_entry_id', $entry->id)
            ->where('id', '!=', $kartuGudang->id)
            ->with('barang')
            ->get();

        if ($remainingKartu->isEmpty()) {
            KasirTransactionLog::where('journal_entry_id', $entry->id)->delete();
            $entry->delete();

            return;
        }

        if (in_array($type, ['pendapatan_tunai', 'penjualan'], true)) {
            $deletedGross = (float) $kartuGudang->dikeluarkan * (float) ($barang?->harga_jual_per_unit ?? 0.0);
            $remainingGross = (float) $remainingKartu->sum(function (KartuGudang $k): float {
                return (float) $k->dikeluarkan * (float) ($k->barang?->harga_jual_per_unit ?? 0.0);
            });

            $revenueItem = $entry->items->first(fn(JournalItem $i) => $i->account?->code === '4101' && $i->credit > 0);
            $discountItem = $entry->items->first(fn(JournalItem $i) => $i->account?->code === '4102' && $i->debit > 0);
            $cashOrPiutangItem = $entry->items->first(fn(JournalItem $i) => in_array($i->account?->code, ['1101', '1103', '1104'], true) && $i->debit > 0);

            $currentCredit = (float) ($revenueItem?->credit ?? 0.0);
            $newCredit = $remainingGross > 0 ? $remainingGross : max(0.0, $currentCredit - $deletedGross);
            $currentDiscount = $discountItem ? (float) $discountItem->debit : 0.0;
            $newDiscount = min($currentDiscount, $newCredit);
            $newNetDebit = max(0.0, $newCredit - $newDiscount);

            if ($newCredit <= 0) {
                KasirTransactionLog::where('journal_entry_id', $entry->id)->delete();
                $entry->delete();

                return;
            }

            if ($revenueItem) {
                $revenueItem->update(['credit' => $newCredit]);
            }

            if ($cashOrPiutangItem) {
                $cashOrPiutangItem->update(['debit' => $newNetDebit]);
            }

            if ($discountItem) {
                if ($newDiscount <= 0) {
                    $discountItem->delete();
                } else {
                    $discountItem->update(['debit' => $newDiscount]);
                }
            }

            $deletedCost = (float) $kartuGudang->dikeluarkan * (float) ($barang?->harga_beli_per_unit ?? 0.0);
            $hppItem = $entry->items->first(fn(JournalItem $i) => $i->account?->code === '5101' && $i->debit > 0);
            $invItem = $entry->items->first(fn(JournalItem $i) => $i->account?->code === '1105' && $i->credit > 0);

            if ($hppItem && $invItem && $deletedCost > 0) {
                $newCost = max(0.0, (float) $hppItem->debit - $deletedCost);
                if ($newCost <= 0) {
                    $hppItem->delete();
                    $invItem->delete();
                } else {
                    $hppItem->update(['debit' => $newCost]);
                    $invItem->update(['credit' => $newCost]);
                }
            }

            foreach ($entry->kasir as $log) {
                $newBayar = (float) $log->bayar;
                $newKembalian = max(0.0, $newBayar - $newNetDebit);
                $log->update([
                    'jumlah' => $newNetDebit,
                    'diskon' => $newDiscount,
                    'kembalian' => $newKembalian,
                ]);
            }

            return;
        }

        $unitPrice = in_array($type, ['retur_penjualan', 'return_penjualan'], true)
            ? (float) ($barang?->harga_jual_per_unit ?? 0.0)
            : (float) ($barang?->harga_beli_per_unit ?? 0.0);
        $qty = max((float) $kartuGudang->diterima, (float) $kartuGudang->dikeluarkan);
        $deletedAmount = $qty * $unitPrice;

        if ($deletedAmount > 0) {
            $primaryDebit = $entry->items->first(fn(JournalItem $i) => $i->debit > 0);
            $primaryCredit = $entry->items->first(fn(JournalItem $i) => $i->credit > 0);

            if ($primaryDebit && $primaryCredit) {
                $newDebit = max(0.0, (float) $primaryDebit->debit - $deletedAmount);
                $newCredit = max(0.0, (float) $primaryCredit->credit - $deletedAmount);

                if ($newDebit <= 0 || $newCredit <= 0) {
                    $entry->delete();
                } else {
                    $primaryDebit->update(['debit' => $newDebit]);
                    $primaryCredit->update(['credit' => $newCredit]);
                }
            }
        }
    }
}
