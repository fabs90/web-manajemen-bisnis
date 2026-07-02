<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\KartuGudang;
use App\Models\KasKecil;
use App\Models\Pelanggan;
use App\Models\PengisianKasKecilLog;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PengeluaranService
{
    public function store(array $validated, int $userId): void
    {
        DB::transaction(function () use ($validated, $userId) {
            $accounts = Account::where('user_id', $userId)->get()->keyBy('code');

            if ($accounts->isEmpty()) {
                throw new Exception('Akun belum diatur. Silakan atur akun di Neraca Awal.');
            }

            switch ($validated['jenis_keperluan']) {
                case 'lain_lain':
                    $this->processLainLain($validated, $userId, $accounts);
                    break;

                case 'membayar_hutang':
                    $this->processMembayarHutang($validated, $userId, $accounts);
                    break;

                case 'membeli_barang':
                    $this->processMembeliBarang($validated, $userId, $accounts);
                    break;

                case 'kas_kecil':
                    $this->processKasKecil($validated, $userId, $accounts);
                    break;
            }
        });
    }

    private function generateReference(string $prefix, string $date): string
    {
        $formattedDate = date('Ymd', strtotime($date));
        $random = strtoupper(Str::random(6));

        return "{$prefix}-{$formattedDate}-{$random}";
    }

    private function processLainLain(array $data, int $userId, $accounts): void
    {
        $baseAmount = $data['jumlah_manual'] ?? 0;
        $potongan = $data['potongan_pembelian'] ?? 0;
        $biayaLain = $data['biaya_lain'] ?? 0;
        $adminBank = $data['admin_bank'] ?? 0;
        $totalKeluar = $data['jumlah'] ?? ($baseAmount - $potongan + $biayaLain + $adminBank);

        $entry = JournalEntry::create([
            'user_id' => $userId,
            'reference_number' => $this->generateReference('EXP', $data['tanggal']),
            'date' => $data['tanggal'],
            'description' => 'Pengeluaran lain-lain: ' . $data['uraian_pengeluaran'],
            'transaction_type' => 'lain_lain',
        ]);

        // 1. Debit: Beban Utama
        $entry->items()->create([
            'user_id' => $userId,
            'account_id' => $accounts['5202']->id ?? $accounts->firstWhere('category', 'expense')->id,
            'debit' => $baseAmount,
            'credit' => 0,
        ]);

        // 2. Debit: Biaya Lain (jika ada)
        if ($biayaLain > 0) {
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['5202']->id ?? $accounts->firstWhere('category', 'expense')->id,
                'debit' => $biayaLain,
                'credit' => 0,
            ]);
        }

        // 3. Credit: Potongan Pembelian (sebagai pengurang beban atau akun pendapatan lain)
        if ($potongan > 0) {
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['5202']->id ?? $accounts->firstWhere('category', 'expense')->id,
                'debit' => 0,
                'credit' => $potongan,
            ]);
        }

        // 4. Debit: Admin Bank
        if ($adminBank > 0) {
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['5202']->id ?? $accounts->firstWhere('category', 'expense')->id,
                'debit' => $adminBank,
                'credit' => 0,
            ]);
        }

        // 5. Credit: Kas atau Utang
        if ($data['jenis_pengeluaran'] == 'tunai') {
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['1101']->id,
                'debit' => 0,
                'credit' => $totalKeluar,
            ]);
        } else {
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['2101']->id,
                'sub_ledger_type' => Pelanggan::class,
                'sub_ledger_id' => $data['nama_kreditur'],
                'debit' => 0,
                'credit' => $totalKeluar,
            ]);
        }
    }

    private function processMembayarHutang(array $data, int $userId, $accounts): void
    {
        $jumlahBayar = $data['jumlah_manual'] ?? 0;
        $adminBank = $data['admin_bank'] ?? 0;
        $potongan = $data['potongan_pembelian'] ?? 0;
        $biayaLain = $data['biaya_lain'] ?? 0;
        $totalKeluar = $data['jumlah'] ?? ($jumlahBayar + $adminBank - $potongan + $biayaLain);

        $hutangItem = JournalItem::findOrFail($data['hutang_id']);

        $entry = JournalEntry::create([
            'user_id' => $userId,
            'reference_number' => $this->generateReference('PAY', $data['tanggal']),
            'date' => $data['tanggal'],
            'description' => 'Pelunasan hutang - ' . ($hutangItem->subLedger->nama ?? 'Unknown') . ': ' . $data['uraian_pengeluaran'],
            'transaction_type' => 'membayar_hutang',
        ]);

        // 1. Debit: Utang Usaha
        $entry->items()->create([
            'user_id' => $userId,
            'account_id' => $accounts['2101']->id,
            'sub_ledger_type' => Pelanggan::class,
            'sub_ledger_id' => $hutangItem->sub_ledger_id,
            'debit' => $jumlahBayar,
            'credit' => 0,
        ]);

        // 2. Debit: Admin Bank / Biaya Lain
        if ($adminBank + $biayaLain > 0) {
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['5202']->id ?? $accounts->firstWhere('category', 'expense')->id,
                'debit' => $adminBank + $biayaLain,
                'credit' => 0,
            ]);
        }

        // 3. Credit: Potongan
        if ($potongan > 0) {
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['5202']->id ?? $accounts->firstWhere('category', 'expense')->id,
                'debit' => 0,
                'credit' => $potongan,
            ]);
        }

        // 4. Credit: Kas
        $entry->items()->create([
            'user_id' => $userId,
            'account_id' => $accounts['1101']->id,
            'debit' => 0,
            'credit' => $totalKeluar,
        ]);
    }

    private function processMembeliBarang(array $data, int $userId, $accounts): void
    {
        $potongan = $data['potongan_pembelian'] ?? 0;
        $biayaLain = $data['biaya_lain'] ?? 0;
        $adminBank = $data['admin_bank'] ?? 0;

        // Hitung total harga barang dari database untuk akurasi
        $baseAmount = 0;
        if (!empty($data['barang_dibeli'])) {
            foreach ($data['barang_dibeli'] as $index => $barangId) {
                $qty = $data['jumlah_barang_dibeli'][$index] ?? 0;
                $barang = Barang::find($barangId);
                $baseAmount += ($barang->harga_beli_per_unit ?? 0) * $qty;
            }
        }

        $totalKeluar = $data['jumlah'] ?? ($baseAmount - $potongan + $biayaLain + $adminBank);

        $entry = JournalEntry::create([
            'user_id' => $userId,
            'reference_number' => $this->generateReference('PUR', $data['tanggal']),
            'date' => $data['tanggal'],
            'description' => 'Pembelian barang: ' . $data['uraian_pengeluaran'],
            'transaction_type' => 'membeli_barang',
        ]);

        // 1. Debit: Persediaan (1105)
        $entry->items()->create([
            'user_id' => $userId,
            'account_id' => $accounts['1105']->id,
            'debit' => $baseAmount,
            'credit' => 0,
        ]);

        // 2. Debit: Biaya Lain / Admin Bank
        if ($biayaLain + $adminBank > 0) {
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['5202']->id ?? $accounts->firstWhere('category', 'expense')->id,
                'debit' => $biayaLain + $adminBank,
                'credit' => 0,
            ]);
        }

        // 3. Credit: Potongan
        if ($potongan > 0) {
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['5202']->id ?? $accounts->firstWhere('category', 'expense')->id,
                'debit' => 0,
                'credit' => $potongan,
            ]);
        }

        // 4. Credit: Kas / Utang
        if ($data['jenis_pengeluaran'] == 'tunai') {
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['1101']->id,
                'debit' => 0,
                'credit' => $totalKeluar,
            ]);
        } else {
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['2101']->id,
                'sub_ledger_type' => Pelanggan::class,
                'sub_ledger_id' => $data['nama_kreditur'],
                'debit' => 0,
                'credit' => $totalKeluar,
            ]);
        }

        $this->tambahBarangKeGudang($data, $userId);
    }

    private function processKasKecil(array $data, int $userId, $accounts): void
    {
        $baseAmount = $data['jumlah_manual'] ?? 0;
        $potongan = $data['potongan_pembelian'] ?? 0;
        $biayaLain = $data['biaya_lain'] ?? 0;
        $adminBank = $data['admin_bank'] ?? 0;
        $totalKeluar = $data['jumlah'] ?? ($baseAmount - $potongan + $biayaLain + $adminBank);

        $entry = JournalEntry::create([
            'user_id' => $userId,
            'reference_number' => $this->generateReference('PET', $data['tanggal']),
            'date' => $data['tanggal'],
            'description' => 'Pengisian Kas Kecil: ' . $data['uraian_pengeluaran'],
            'transaction_type' => 'kas_kecil',
        ]);

        // 1. Debit: Kas Kecil (1102)
        $entry->items()->create([
            'user_id' => $userId,
            'account_id' => $accounts['1102']->id,
            'debit' => $baseAmount,
            'credit' => 0,
        ]);

        // 2. Debit: Biaya Lain / Admin Bank
        if ($biayaLain + $adminBank > 0) {
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['5202']->id ?? $accounts->firstWhere('category', 'expense')->id,
                'debit' => $biayaLain + $adminBank,
                'credit' => 0,
            ]);
        }

        // 3. Credit: Potongan
        if ($potongan > 0) {
            $entry->items()->create([
                'user_id' => $userId,
                'account_id' => $accounts['5202']->id ?? $accounts->firstWhere('category', 'expense')->id,
                'debit' => 0,
                'credit' => $potongan,
            ]);
        }

        // 4. Credit: Kas Utama (1101)
        $entry->items()->create([
            'user_id' => $userId,
            'account_id' => $accounts['1101']->id,
            'debit' => 0,
            'credit' => $totalKeluar,
        ]);

        // Catat di tabel kas_kecil untuk historis
        $latestKasKecil = KasKecil::where('user_id', $userId)->latest('id')->first();
        $saldoAkhir = ($latestKasKecil->saldo_akhir ?? 0) + $baseAmount;

        $kasKecil = KasKecil::create([
            'user_id' => $userId,
            'tanggal' => $data['tanggal'],
            'nomor_referensi' => $entry->reference_number,
            'penerimaan' => $baseAmount,
            'pengeluaran' => 0,
            'saldo_akhir' => $saldoAkhir,
        ]);

        PengisianKasKecilLog::create([
            'journal_entry_id' => $entry->id,
            'kas_kecil_id' => $kasKecil->id,
            'uraian' => 'Pengisian kas kecil: ' . $data['uraian_pengeluaran'],
            'jumlah' => $baseAmount,
            'tanggal_transaksi' => $data['tanggal'],
            'user_id' => $userId,
        ]);
    }

    private function tambahBarangKeGudang(array $data, int $userId): void
    {
        if (empty($data['barang_dibeli']) || empty($data['jumlah_barang_dibeli'])) {
            return;
        }

        foreach ($data['barang_dibeli'] as $index => $barangId) {
            $jumlahDibeli = (int) $data['jumlah_barang_dibeli'][$index];

            $detailBarang = Barang::where('id', $barangId)->where('user_id', $userId)->firstOrFail();
            $barangItem = KartuGudang::where('barang_id', $barangId)->where('user_id', $userId)->latest()->first();

            $pcsPerKemasan = $detailBarang->jumlah_unit_per_kemasan;
            $saldoSatuanBaru = ($barangItem->saldo_persatuan ?? 0) + $jumlahDibeli;
            $saldoPerKemasanBaru = ($barangItem->saldo_perkemasan ?? 0) + round($jumlahDibeli / $pcsPerKemasan, 0);

            KartuGudang::create([
                'barang_id' => $barangId,
                'tanggal' => $data['tanggal'],
                'diterima' => $jumlahDibeli,
                'dikeluarkan' => 0,
                'uraian' => $data['uraian_pengeluaran'],
                'saldo_persatuan' => $saldoSatuanBaru,
                'saldo_perkemasan' => $saldoPerKemasanBaru,
                'user_id' => $userId,
            ]);
        }
    }

    public function destroy(int $id, int $userId): void
    {
        DB::transaction(function () use ($id, $userId) {
            $entry = JournalEntry::where('id', $id)->where('user_id', $userId)->firstOrFail();

            // Jika ini pengisian kas kecil, hapus log terkait
            if ($entry->transaction_type === 'kas_kecil') {
                PengisianKasKecilLog::where('journal_entry_id', $entry->id)->delete();
                $kasKecil = KasKecil::where('nomor_referensi', $entry->reference_number)->first();
                if ($kasKecil !== null) {
                    $deletedId = $kasKecil->id;
                    $kasKecil->delete();
                    KasKecil::recalculateBalances($userId, $deletedId);
                }
            }

            // Hapus items (sudah cascade di DB biasanya, tapi jika tidak:)
            $entry->items()->delete();
            $entry->delete();
        });
    }
}
