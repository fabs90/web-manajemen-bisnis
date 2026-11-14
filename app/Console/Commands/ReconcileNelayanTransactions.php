<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\KartuGudang;
use App\Models\KasirTransactionLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReconcileNelayanTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keuangan:reconcile-nelayan {--dry-run : Tampilkan laporan identifikasi tanpa mengubah database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Identifikasi dan rekonsiliasi transaksi yatim (orphan) dari Hasil Tangkapan / Kartu Gudang yang telah dihapus';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        $this->info('=== AUDIT & REKONSILIASI TRANSAKSI HASIL TANGKAPAN / KARTU GUDANG ===');
        $this->line('Mode: '.($isDryRun ? 'DRY-RUN (Simulasi)' : 'EKSEKUSI (Database Transaction)'));

        $actions = [];

        DB::beginTransaction();

        try {
            // 1. Identifikasi transaksi Kasir / Pendapatan Tunai yatim (orphan) yang sumber KartuGudang-nya sudah terhapus
            $orphanSalesEntries = JournalEntry::with(['items.account', 'kasir', 'kartuGudang'])
                ->where('transaction_type', 'pendapatan_tunai')
                ->where(function ($query) {
                    $query->whereHas('kasir')
                        ->orWhere('reference_number', 'like', 'TRX-%')
                        ->orWhere('description', 'like', 'Penjualan Tunai Kasir:%');
                })
                ->get()
                ->filter(fn (JournalEntry $entry) => $entry->kartuGudang->isEmpty());

            foreach ($orphanSalesEntries as $entry) {
                $debitTotal = (float) $entry->items->sum('debit');
                $creditTotal = (float) $entry->items->sum('credit');
                $kasirIds = $entry->kasir->pluck('id')->implode(', ');

                $actions[] = [
                    'type' => 'DELETE_ORPHAN_KASIR_JOURNAL',
                    'user_id' => $entry->user_id,
                    'journal_entry_id' => $entry->id,
                    'reference_number' => $entry->reference_number,
                    'date' => $entry->date?->format('Y-m-d'),
                    'description' => $entry->description,
                    'debit' => $debitTotal,
                    'credit' => $creditTotal,
                    'reason' => "Transaksi Kasir (Log ID: {$kasirIds}) tidak lagi memiliki relasi KartuGudang/Barang karena data hasil tangkapan sumber telah dihapus.",
                ];

                if (! $isDryRun) {
                    KasirTransactionLog::where('journal_entry_id', $entry->id)->delete();
                    $entry->delete();
                    Cache::forget("dashboard_data_{$entry->user_id}");
                }
            }

            // 2. Identifikasi jurnal penyesuaian-kartu-gudang yatim (orphan) atau belum ter-link
            $kgEntries = JournalEntry::with(['items.account', 'kartuGudang'])
                ->where('transaction_type', 'penyesuaian-kartu-gudang')
                ->get();

            foreach ($kgEntries as $entry) {
                if ($entry->kartuGudang->isNotEmpty()) {
                    continue;
                }

                if (! preg_match('/^Penyesuaian Kartu Gudang:\s*(.+?)\s*\((.*)\)$/u', (string) $entry->description, $matches)) {
                    continue;
                }

                $namaBarang = trim($matches[1]);
                $uraian = trim($matches[2]);

                $barang = Barang::where('user_id', $entry->user_id)
                    ->where('nama', $namaBarang)
                    ->first();

                if (! $barang) {
                    $actions[] = [
                        'type' => 'DELETE_ORPHAN_KG_JOURNAL',
                        'user_id' => $entry->user_id,
                        'journal_entry_id' => $entry->id,
                        'reference_number' => $entry->reference_number,
                        'date' => $entry->date?->format('Y-m-d'),
                        'description' => $entry->description,
                        'debit' => (float) $entry->items->sum('debit'),
                        'credit' => (float) $entry->items->sum('credit'),
                        'reason' => "Barang/Hasil Tangkapan '{$namaBarang}' milik User #{$entry->user_id} sudah dihapus.",
                    ];

                    if (! $isDryRun) {
                        $entry->delete();
                        Cache::forget("dashboard_data_{$entry->user_id}");
                    }

                    continue;
                }

                $matchingKartu = KartuGudang::where('user_id', $entry->user_id)
                    ->where('barang_id', $barang->id)
                    ->whereDate('tanggal', $entry->date)
                    ->where('uraian', $uraian)
                    ->first();

                if (! $matchingKartu) {
                    $actions[] = [
                        'type' => 'DELETE_ORPHAN_KG_JOURNAL',
                        'user_id' => $entry->user_id,
                        'journal_entry_id' => $entry->id,
                        'reference_number' => $entry->reference_number,
                        'date' => $entry->date?->format('Y-m-d'),
                        'description' => $entry->description,
                        'debit' => (float) $entry->items->sum('debit'),
                        'credit' => (float) $entry->items->sum('credit'),
                        'reason' => "Baris KartuGudang untuk '{$namaBarang}' ({$uraian}) sudah dihapus.",
                    ];

                    if (! $isDryRun) {
                        $entry->delete();
                        Cache::forget("dashboard_data_{$entry->user_id}");
                    }

                    continue;
                }

                // Jika KartuGudang sudah ter-link ke neraca_awal dan jurnal KG ini bernilai 0, hapus jurnal KG duplikat
                if ($matchingKartu->journal_entry_id !== null && $matchingKartu->journal_entry_id !== $entry->id) {
                    $linkedEntry = JournalEntry::find($matchingKartu->journal_entry_id);
                    if ($linkedEntry && $linkedEntry->transaction_type === 'neraca_awal' && (float) $entry->items->sum('debit') == 0.0) {
                        $actions[] = [
                            'type' => 'DELETE_REDUNDANT_ZERO_KG_JOURNAL',
                            'user_id' => $entry->user_id,
                            'journal_entry_id' => $entry->id,
                            'reference_number' => $entry->reference_number,
                            'date' => $entry->date?->format('Y-m-d'),
                            'description' => $entry->description,
                            'debit' => 0.0,
                            'credit' => 0.0,
                            'reason' => "KartuGudang #{$matchingKartu->id} sudah tercatat pada Neraca Awal (JE #{$linkedEntry->id}); jurnal penyesuaian bernilai 0 ini duplikat.",
                        ];

                        if (! $isDryRun) {
                            $entry->delete();
                        }

                        continue;
                    }
                }

                // Jika KartuGudang belum memiliki journal_entry_id, hubungkan dan koreksi nilai jika 0 pada role nelayan
                if ($matchingKartu->journal_entry_id === null) {
                    $userRole = User::where('id', $entry->user_id)->value('role') ?? 'ukm';
                    $hargaSatuan = $barang->hargaPersediaan($userRole);
                    $qty = max((float) $matchingKartu->diterima, (float) $matchingKartu->dikeluarkan);
                    $expectedAmount = $qty * $hargaSatuan;
                    $currentDebit = (float) $entry->items->sum('debit');

                    $actions[] = [
                        'type' => 'LINK_AND_SYNC_KG_JOURNAL',
                        'user_id' => $entry->user_id,
                        'journal_entry_id' => $entry->id,
                        'reference_number' => $entry->reference_number,
                        'date' => $entry->date?->format('Y-m-d'),
                        'description' => $entry->description,
                        'debit' => $currentDebit,
                        'credit' => $expectedAmount,
                        'reason' => "Menghubungkan KartuGudang #{$matchingKartu->id} ke JE #{$entry->id} dan menyinkronkan nilai persediaan Rp ".number_format($expectedAmount, 0, ',', '.').'.',
                    ];

                    if (! $isDryRun) {
                        $matchingKartu->update(['journal_entry_id' => $entry->id]);

                        if ($currentDebit == 0.0 && $expectedAmount > 0) {
                            $invAccount = Account::where('user_id', $entry->user_id)->where('code', '1105')->first();
                            $modalAccount = Account::where('user_id', $entry->user_id)->where('code', '3100')->first();

                            if ($invAccount && $modalAccount) {
                                foreach ($entry->items as $item) {
                                    if ($matchingKartu->diterima > 0) {
                                        if ($item->account_id === $invAccount->id) {
                                            $item->update(['debit' => $expectedAmount, 'credit' => 0]);
                                        } elseif ($item->account_id === $modalAccount->id) {
                                            $item->update(['debit' => 0, 'credit' => $expectedAmount]);
                                        }
                                    } elseif ($matchingKartu->dikeluarkan > 0) {
                                        if ($item->account_id === $modalAccount->id) {
                                            $item->update(['debit' => $expectedAmount, 'credit' => 0]);
                                        } elseif ($item->account_id === $invAccount->id) {
                                            $item->update(['debit' => 0, 'credit' => $expectedAmount]);
                                        }
                                    }
                                }
                            }
                        }

                        Cache::forget("dashboard_data_{$entry->user_id}");
                    }
                }
            }

            if ($isDryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Gagal melakukan rekonsiliasi: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Tipe Aksi', 'User ID', 'JE ID', 'Ref', 'Tanggal', 'Debit Awal', 'Kredit/Nilai Baru', 'Alasan'],
            array_map(fn (array $a) => [
                $a['type'],
                $a['user_id'],
                $a['journal_entry_id'],
                $a['reference_number'],
                $a['date'],
                number_format($a['debit'], 0, ',', '.'),
                number_format($a['credit'], 0, ',', '.'),
                $a['reason'],
            ], $actions)
        );

        $totalDebit = (float) JournalItem::sum('debit');
        $totalCredit = (float) JournalItem::sum('credit');
        $this->info(sprintf(
            'General Ledger Summary -> Total Debit: Rp %s | Total Credit: Rp %s | Selisih: Rp %s',
            number_format($totalDebit, 2, ',', '.'),
            number_format($totalCredit, 2, ',', '.'),
            number_format($totalDebit - $totalCredit, 2, ',', '.')
        ));

        return self::SUCCESS;
    }
}
