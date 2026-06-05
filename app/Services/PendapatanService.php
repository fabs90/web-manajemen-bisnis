<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\KartuGudang;
use Illuminate\Support\Str;

class PendapatanService
{
    public function storePendapatan($request, $userId, $accounts)
    {
        $prefix = 'PEND';
        $date = \Carbon\Carbon::parse($request->tanggal)->format('Ymd');
        $random = strtoupper(Str::random(6));
        $referenceNumber = "{$prefix}-{$date}-{$random}";

        // 1. Create Journal Entry
        $entry = JournalEntry::create([
            'user_id' => $userId,
            'reference_number' => $referenceNumber,
            'date' => $request->tanggal,
            'description' => $request->uraian_pendapatan,
            'transaction_type' => 'pendapatan_tunai',
        ]);

        $totalAmount = $request->jumlah + ($request->biaya_lain ?? 0);

        // 2. Handle Jenis Pendapatan
        switch ($request->jenis_pendapatan) {
            case 'tunai':
                $this->storeTunai($request, $userId, $accounts, $entry, $totalAmount);
                break;
            case 'piutang':
                $this->storePiutang($request, $userId, $accounts, $entry);
                break;
            case 'kredit': // Pelunasan
                $this->storeKredit($request, $userId, $accounts, $entry);
                break;
        }

        // Handle Biaya Lain jika ada
        if ($request->biaya_lain > 0) {
            $this->storeBiayaLain($request, $userId, $accounts, $entry);
        }

        // 3. Handle Barang Terjual & Inventory
        if ($request->filled('barang_terjual') && is_array($request->barang_terjual)) {
            $this->handleBarangTerjual($request, $userId, $entry);
        }

        return $entry;
    }

    protected function storeTunai($request, $userId, $accounts, $entry, $totalAmount)
    {
        // Debit: Kas Utama (1101)
        $entry->items()->create([
            'user_id' => $userId,
            'journal_entry_id' => $entry->id,
            'account_id' => $accounts['1101']->id,
            'debit' => $totalAmount,
            'credit' => 0,
        ]);

        // Credit: Pendapatan Penjualan (4101)
        $entry->items()->create([
            'user_id' => $userId,
            'journal_entry_id' => $entry->id,
            'account_id' => $accounts['4101']->id,
            'debit' => 0,
            'credit' => $request->jumlah,
        ]);
    }

    protected function storePiutang($request, $userId, $accounts, $entry)
    {
        $piutangAmount = $request->jumlah + ($request->jumlah_piutang ?? 0);
        $subledgerId = $request->nama_pelanggan;

        // Debit: Piutang Usaha (1104)
        $entry->items()->create([
            'user_id' => $userId,
            'journal_entry_id' => $entry->id,
            'account_id' => $accounts['1104']->id,
            'debit' => $piutangAmount,
            'credit' => 0,
            'sub_ledger_type' => 'App\Models\Pelanggan',
            'sub_ledger_id' => $subledgerId,
        ]);

        // Credit: Pendapatan Penjualan (4101)
        $entry->items()->create([
            'user_id' => $userId,
            'journal_entry_id' => $entry->id,
            'account_id' => $accounts['4101']->id,
            'debit' => 0,
            'credit' => $request->jumlah,
        ]);
    }

    protected function storeKredit($request, $userId, $accounts, $entry)
    {
        $kreditAmount = $request->jumlah + ($request->jumlah_kredit ?? 0);
        $subledgerId = $request->nama_pelanggan;

        // Debit: Kas Utama (1101)
        $entry->items()->create([
            'user_id' => $userId,
            'journal_entry_id' => $entry->id,
            'account_id' => $accounts['1101']->id,
            'debit' => $kreditAmount,
            'credit' => 0,
        ]);

        // Credit: Piutang Usaha (1104)
        $entry->items()->create([
            'user_id' => $userId,
            'journal_entry_id' => $entry->id,
            'account_id' => $accounts['1104']->id,
            'debit' => 0,
            'credit' => $kreditAmount,
            'sub_ledger_type' => 'App\Models\Pelanggan',
            'sub_ledger_id' => $subledgerId,
        ]);
    }

    protected function storeBiayaLain($request, $userId, $accounts, $entry)
    {
        $entry->items()->create([
            'user_id' => $userId,
            'journal_entry_id' => $entry->id,
            'account_id' => $accounts['4101']->id,
            'debit' => 0,
            'credit' => $request->biaya_lain,
        ]);
    }

    protected function handleBarangTerjual($request, $userId, $entry)
    {
        foreach ($request->barang_terjual as $index => $barangId) {
            if (! $barangId) {
                continue;
            }

            $detailBarang = Barang::findOrFail($barangId);
            $jumlahDijual = $request->jumlah_barang_dijual[$index] ?? 0;

            $barangItem = KartuGudang::where('barang_id', $barangId)->where('user_id', $userId)->latest()->first();
            $saldoSatuanAwal = $barangItem ? $barangItem->saldo_persatuan : 0;
            $saldoKemasanAwal = $barangItem ? $barangItem->saldo_perkemasan : 0;
            $unitPerKemasan = $detailBarang->jumlah_unit_per_kemasan;

            if ($saldoSatuanAwal < $jumlahDijual) {
                throw new \Exception("Saldo barang '{$detailBarang->nama}' tidak mencukupi.");
            }

            $saldoPerKemasanBaru = $saldoKemasanAwal - ceil($jumlahDijual / $unitPerKemasan);
            $satuanBaru = $saldoSatuanAwal - $jumlahDijual;

            KartuGudang::create([
                'barang_id' => $barangId,
                'tanggal' => $request->tanggal,
                'diterima' => 0,
                'dikeluarkan' => $jumlahDijual,
                'uraian' => 'Penjualan: '.$request->uraian_pendapatan,
                'saldo_persatuan' => $satuanBaru,
                'saldo_perkemasan' => $saldoPerKemasanBaru,
                'journal_entry_id' => $entry->id,
                'user_id' => $userId,
            ]);
        }
    }
}
