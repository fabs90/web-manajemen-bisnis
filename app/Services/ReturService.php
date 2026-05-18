<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\KartuGudang;
use App\Models\Pelanggan;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturService
{
    public function getReturPenjualanList()
    {
        $userId = Auth::id();

        return JournalEntry::where('user_id', $userId)
            ->where('transaction_type', 'retur_penjualan')
            ->with(['items.account', 'items.subLedger'])
            ->latest('date')
            ->get();
    }

    public function getReturPembelianList()
    {
        $userId = Auth::id();

        return JournalEntry::where('user_id', $userId)
            ->where('transaction_type', 'retur_pembelian')
            ->with(['items.account', 'items.subLedger'])
            ->latest('date')
            ->get();
    }

    public function getActivePiutang(int $userId)
    {
        $account = Account::where('user_id', $userId)->where('code', '1104')->first();
        if (! $account) {
            return collect();
        }

        return JournalItem::where('user_id', $userId)
            ->where('account_id', $account->id)
            ->where('sub_ledger_type', Pelanggan::class)
            ->select('sub_ledger_id', DB::raw('SUM(debit) - SUM(credit) as saldo'))
            ->groupBy('sub_ledger_id')
            ->having('saldo', '>', 0)
            ->with('subLedger')
            ->get();
    }

    public function getActiveHutang(int $userId)
    {
        $account = Account::where('user_id', $userId)->where('code', '2101')->first();
        if (! $account) {
            return collect();
        }

        return JournalItem::where('user_id', $userId)
            ->where('account_id', $account->id)
            ->where('sub_ledger_type', Pelanggan::class)
            ->select('sub_ledger_id', DB::raw('SUM(credit) - SUM(debit) as saldo'))
            ->groupBy('sub_ledger_id')
            ->having('saldo', '>', 0)
            ->with('subLedger')
            ->get();
    }

    public function storeReturPenjualan(array $data)
    {
        $userId = Auth::id();
        $accounts = Account::where('user_id', $userId)->get()->keyBy('code');

        if ($accounts->isEmpty()) {
            throw new Exception('Akun belum diatur. Silakan atur akun di Neraca Awal.');
        }

        return DB::transaction(function () use ($userId, $data, $accounts) {
            $totalSalesAmount = 0;
            $totalCogsAmount = 0;
            $keterangan = $data['retur_keterangan'] ?: 'Retur penjualan';

            $entry = JournalEntry::create([
                'user_id' => $userId,
                'reference_number' => 'RPJ-'.now()->format('YmdHis'),
                'date' => $data['tanggal'],
                'description' => 'Retur Penjualan: '.$keterangan,
                'transaction_type' => 'retur_penjualan',
            ]);

            foreach ($data['items'] as $item) {
                $barang = Barang::where('id', $item['barang_id'])->where('user_id', $userId)->firstOrFail();
                $qty = (int) $item['qty'];
                $hargaJual = (float) $item['harga'];
                $lineTotal = $qty * $hargaJual;

                $totalSalesAmount += $lineTotal;
                $totalCogsAmount += $qty * ($barang->harga_beli_per_unit ?? 0);

                // Update Kartu Gudang (Barang Masuk)
                $lastKartu = KartuGudang::where('barang_id', $barang->id)
                    ->where('user_id', $userId)
                    ->latest()
                    ->first();

                $saldoPersatuanBaru = ($lastKartu->saldo_persatuan ?? 0) + $qty;
                $pcsPerKemasan = $barang->jumlah_unit_per_kemasan ?: 1;
                $saldoPerKemasanBaru = ($lastKartu->saldo_perkemasan ?? 0) + round($qty / $pcsPerKemasan, 0);

                KartuGudang::create([
                    'barang_id' => $barang->id,
                    'tanggal' => $data['tanggal'],
                    'diterima' => $qty,
                    'dikeluarkan' => 0,
                    'uraian' => 'Retur Penjualan: '.$keterangan,
                    'saldo_persatuan' => $saldoPersatuanBaru,
                    'saldo_perkemasan' => $saldoPerKemasanBaru,
                    'user_id' => $userId,
                    'journal_entry_id' => $entry->id,
                ]);
            }

            // 1. Debit: Pendapatan Penjualan (4101) - Mengurangi Pendapatan
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['4101']->id,
                'debit' => $totalSalesAmount,
                'credit' => 0,
            ]);

            // 2. Credit: Piutang (1104) atau Kas (1101)
            if ($data['retur_penanganan'] === 'kurangi_piutang') {
                $entry->items()->create([
                    'user_id' => $userId,
                    'account_id' => $accounts['1104']->id,
                    'sub_ledger_type' => Pelanggan::class,
                    'sub_ledger_id' => $data['nama_pelanggan'],
                    'debit' => 0,
                    'credit' => $totalSalesAmount,
                ]);
            } else {
                $entry->items()->create([
                    'user_id' => $userId,
                    'account_id' => $accounts['1101']->id,
                    'debit' => 0,
                    'credit' => $totalSalesAmount,
                ]);
            }

            // 3. Debit: Persediaan (1105) - Menambah Persediaan
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['1105']->id,
                'debit' => $totalCogsAmount,
                'credit' => 0,
            ]);

            // 4. Credit: HPP (5101) - Mengurangi HPP
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['5101']->id,
                'debit' => 0,
                'credit' => $totalCogsAmount,
            ]);

            return $entry;
        });
    }

    public function storeReturPembelian(array $data)
    {
        $userId = Auth::id();
        $accounts = Account::where('user_id', $userId)->get()->keyBy('code');

        if ($accounts->isEmpty()) {
            throw new Exception('Akun belum diatur. Silakan atur akun di Neraca Awal.');
        }

        return DB::transaction(function () use ($userId, $data, $accounts) {
            $totalPurchaseAmount = 0;
            $keterangan = $data['retur_keterangan'] ?: 'Retur pembelian';

            $entry = JournalEntry::create([
                'user_id' => $userId,
                'reference_number' => 'RPB-'.now()->format('YmdHis'),
                'date' => $data['tanggal'],
                'description' => 'Retur Pembelian: '.$keterangan,
                'transaction_type' => 'retur_pembelian',
            ]);

            foreach ($data['items'] as $item) {
                $barang = Barang::where('id', $item['barang_id'])->where('user_id', $userId)->firstOrFail();
                $qty = (int) $item['qty'];
                $hargaBeli = (float) $item['harga'];
                $lineTotal = $qty * $hargaBeli;

                $totalPurchaseAmount += $lineTotal;

                // Update Kartu Gudang (Barang Keluar)
                $lastKartu = KartuGudang::where('barang_id', $barang->id)
                    ->where('user_id', $userId)
                    ->latest()
                    ->first();

                $saldoPersatuanBaru = ($lastKartu->saldo_persatuan ?? 0) - $qty;
                $pcsPerKemasan = $barang->jumlah_unit_per_kemasan ?: 1;
                $saldoPerKemasanBaru = ($lastKartu->saldo_perkemasan ?? 0) - round($qty / $pcsPerKemasan, 0);

                KartuGudang::create([
                    'barang_id' => $barang->id,
                    'tanggal' => $data['tanggal'],
                    'diterima' => 0,
                    'dikeluarkan' => $qty,
                    'uraian' => 'Retur Pembelian: '.$keterangan,
                    'saldo_persatuan' => $saldoPersatuanBaru,
                    'saldo_perkemasan' => $saldoPerKemasanBaru,
                    'user_id' => $userId,
                    'journal_entry_id' => $entry->id,
                ]);
            }

            // 1. Credit: Persediaan (1105) - Mengurangi Persediaan
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['1105']->id,
                'debit' => 0,
                'credit' => $totalPurchaseAmount,
            ]);

            // 2. Debit: Utang (2101) atau Kas (1101)
            if ($data['retur_penanganan'] === 'kurangi_hutang') {
                $entry->items()->create([
                    'user_id' => $userId,
                    'account_id' => $accounts['2101']->id,
                    'sub_ledger_type' => Pelanggan::class,
                    'sub_ledger_id' => $data['nama_pelanggan'],
                    'debit' => $totalPurchaseAmount,
                    'credit' => 0,
                ]);
            } else {
                $entry->items()->create([
                    'user_id' => $userId,
                    'account_id' => $accounts['1101']->id,
                    'debit' => $totalPurchaseAmount,
                    'credit' => 0,
                ]);
            }

            return $entry;
        });
    }
}
