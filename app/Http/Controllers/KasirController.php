<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Barang;
use App\Models\JenisPembayaran;
use App\Models\JournalEntry;
use App\Models\KartuGudang;
use App\Models\KasirTransactionLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KasirController extends Controller
{
    public function index()
    {
        $kasirTransactions = KasirTransactionLog::where('user_id', auth()->id())
            ->orderBy('tanggal_transaksi', 'desc')
            ->get();

        return view('keuangan.kasir.index', compact('kasirTransactions'));
    }

    public function create()
    {
        $jenisPembayaran = JenisPembayaran::select('id', 'nama')->get();
        // Ambil semua barang
        $barang = Barang::where('user_id', auth()->id())
            ->orderBy('nama')
            ->get();

        return view(
            'keuangan.kasir.create',
            compact('barang', 'jenisPembayaran'),
        );
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $userId = auth()->id();
            $prefix = 'TRX';
            $date = Carbon::now()->format('Ymd');
            $random = strtoupper(Str::random(6));
            $kodeTransaksi = "{$prefix}-{$date}-{$random}";
            $jenisPembayaran = JenisPembayaran::findOrFail($request->jenis_pembayaran_id);
            $namaPembayaran = $jenisPembayaran ? $jenisPembayaran->nama : 'tunai';

            // Ambil akun-akun
            $accounts = Account::where('user_id', $userId)->get()->keyBy('code');

            if (! isset($accounts['1101']) || ! isset($accounts['4101'])) {
                throw new \Exception('Akun Kas Utama (1101) atau Pendapatan Penjualan (4101) tidak ditemukan.');
            }

            // Jurnal Entri
            $entry = JournalEntry::create([
                'user_id' => $userId,
                'reference_number' => $kodeTransaksi,
                'date' => now(),
                'description' => "Penjualan Tunai Kasir: {$kodeTransaksi}",
                'transaction_type' => 'pendapatan_tunai',
            ]);

            // Jurnal Item Debit Kas Utama
            $entry->items()->create([
                'user_id' => $userId,
                'journal_entry_id' => $entry->id,
                'account_id' => $accounts['1101']->id,
                'debit' => $request->grand_total,
                'credit' => 0,
            ]);

            // Jurnal Item Credit Pendapatan Penjualan
            $entry->items()->create([
                'user_id' => $userId,
                'journal_entry_id' => $entry->id,
                'account_id' => $accounts['4101']->id,
                'debit' => 0,
                'credit' => $request->grand_total,
            ]);

            // Insert barang
            if ($request->filled('id_barang_terjual')) {
                foreach ($request->id_barang_terjual as $index => $barangId) {
                    if (! $barangId) {
                        continue;
                    }

                    $detailBarang = Barang::where('id', $barangId)->first();
                    if (! $detailBarang) {
                        throw new \Exception(
                            "Barang dengan ID {$barangId} tidak ditemukan.",
                        );
                    }

                    $barangItem = KartuGudang::where('barang_id', $barangId)
                        ->latest()
                        ->first();

                    if (! $barangItem) {
                        throw new \Exception(
                            "Kartu gudang untuk barang ID {$barangId} tidak ditemukan.",
                        );
                    }

                    $saldoSatuanAwal = $barangItem->saldo_persatuan;
                    $saldoKemasanAwal = $barangItem->saldo_perkemasan;
                    $unitPerKemasan = $detailBarang->jumlah_unit_per_kemasan;
                    $jumlahDijual = $request->jumlah_barang_dijual[$index] ?? 0;
                    if ($saldoSatuanAwal < $jumlahDijual) {
                        throw new \Exception(
                            "Saldo barang '{$detailBarang->nama}' tidak mencukupi. Tersedia: {$saldoSatuanAwal}, Dibutuhkan: {$jumlahDijual}",
                        );
                    }

                    $saldoPerKemasanBaru =
                        $saldoKemasanAwal -
                        ceil($jumlahDijual / $unitPerKemasan);

                    $saldoSatuanBaru = $saldoSatuanAwal - $jumlahDijual;

                    KartuGudang::create([
                        'barang_id' => $barangId,
                        'tanggal' => now(),
                        'diterima' => 0,
                        'dikeluarkan' => $jumlahDijual,
                        'uraian' => 'Pendapatan Kasir Tunai: '.
                            $detailBarang->nama.
                            ' - '.
                            Carbon::now('Asia/Jakarta')->format('d/m/Y H:i'),
                        'saldo_persatuan' => $saldoSatuanBaru,
                        'saldo_perkemasan' => $saldoPerKemasanBaru,
                        'journal_entry_id' => $entry->id,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            // Simpan Log Transaksi Kasir
            KasirTransactionLog::create([
                'user_id' => $userId,
                'journal_entry_id' => $entry->id,
                'uraian' => "Penjualan Kasir - Pembayaran: {$namaPembayaran}",
                'tanggal_transaksi' => now(),
                'jumlah' => $request->grand_total,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error occurred while processing request', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi Error: '.$e->getMessage());
        }

        return redirect()->back()->with('success', 'Transaksi berhasil');
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $log = KasirTransactionLog::findOrFail($id);
            $journalEntryId = $log->journal_entry_id;

            if ($journalEntryId) {
                // Hapus kartu gudang terkait
                KartuGudang::where('journal_entry_id', $journalEntryId)->delete();
                // Hapus jurnal entry (ini akan men-cascade hapus ke journal_items dan kasir_transaction_logs)
                $journalEntry = JournalEntry::find($journalEntryId);
                if ($journalEntry) {
                    $journalEntry->delete();
                } else {
                    $log->delete();
                }
            } else {
                $log->delete();
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Hapus transaksi berhasil');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error occurred while deleting transaction', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi Error: '.$e->getMessage());
        }
    }
}
