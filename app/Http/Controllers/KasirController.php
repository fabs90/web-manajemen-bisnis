<?php

namespace App\Http\Controllers;

use App\Http\Requests\KasirRequest;
use App\Models\Account;
use App\Models\Barang;
use App\Models\JenisPembayaran;
use App\Models\JournalEntry;
use App\Models\KartuGudang;
use App\Models\KasirTransactionLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KasirController extends Controller
{
    public function index()
    {
        $kasirTransactions = KasirTransactionLog::with('journalEntry.kartuGudang.barang')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('keuangan.kasir.index', compact('kasirTransactions'));
    }

    public function create()
    {
        $jenisPembayaran = JenisPembayaran::select('id', 'nama')->get();
        // Ambil semua barang
        $barang = Barang::with('latestKartuGudang')
            ->where('user_id', auth()->id())
            ->orderBy('nama')
            ->get();

        $paketDiskons = \App\Models\PaketDiskon::where('user_id', auth()->id())
            ->where('is_active', true)
            ->get();

        return view(
            'keuangan.kasir.create',
            compact('barang', 'jenisPembayaran', 'paketDiskons'),
        );
    }

    public function store(KasirRequest $request)
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

            if (!isset($accounts['1101']) || !isset($accounts['4101'])) {
                throw new \Exception('Akun Kas Utama (1101) atau Pendapatan Penjualan (4101) tidak ditemukan.');
            }

            if ($request->diskon_total > 0 && !isset($accounts['4102'])) {
                throw new \Exception('Akun Potongan Penjualan (4102) tidak ditemukan.');
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

            // Potongan Penjualan (Debit) jika ada
            if ($request->diskon_total > 0) {
                $entry->items()->create([
                    'user_id' => $userId,
                    'journal_entry_id' => $entry->id,
                    'account_id' => $accounts['4102']->id,
                    'debit' => $request->diskon_total,
                    'credit' => 0,
                ]);
            }

            // Jurnal Item Credit Pendapatan Penjualan
            $pendapatanTotal = $request->grand_total + ($request->diskon_total ?? 0);
            $entry->items()->create([
                'user_id' => $userId,
                'journal_entry_id' => $entry->id,
                'account_id' => $accounts['4101']->id,
                'debit' => 0,
                'credit' => $pendapatanTotal,
            ]);

            $receiptItems = [];

            // Insert barang
            if ($request->filled('id_barang_terjual')) {
                foreach ($request->id_barang_terjual as $index => $barangId) {
                    if (!$barangId) {
                        continue;
                    }

                    $detailBarang = Barang::where('id', $barangId)
                        ->where('user_id', auth()->id())
                        ->first();
                    if (!$detailBarang) {
                        throw new \Exception(
                            "Barang dengan ID {$barangId} tidak valid atau bukan milik Anda.",
                        );
                    }

                    $barangItem = KartuGudang::where('barang_id', $barangId)
                        ->where('user_id', auth()->id())
                        ->latest('id')
                        ->first();

                    if (!$barangItem) {
                        throw new \Exception(
                            "Kartu gudang untuk barang ID {$barangId} tidak ditemukan.",
                        );
                    }

                    $saldoSatuanAwal = $barangItem->saldo_persatuan; // 240
                    $saldoKemasanAwal = $barangItem->saldo_perkemasan; // 10
                    $unitPerKemasan = $detailBarang->jumlah_unit_per_kemasan; // 24
                    $jumlahDijual = $request->jumlah_barang_dijual[$index] ?? 0;
                    if ($saldoSatuanAwal < $jumlahDijual) {
                        throw new \Exception(
                            "Saldo barang {$detailBarang->nama} tidak mencukupi. Tersedia: {$saldoSatuanAwal}, Dibutuhkan: {$jumlahDijual}",
                        );
                    }

                    $saldoSatuanBaru = $saldoSatuanAwal - $jumlahDijual;

                    $saldoPerKemasanBaru = $unitPerKemasan > 0
                        ? ceil($saldoSatuanBaru / $unitPerKemasan)
                        : 0;

                    KartuGudang::create([
                        'barang_id' => $barangId,
                        'tanggal' => now(),
                        'diterima' => 0,
                        'dikeluarkan' => $jumlahDijual,
                        'uraian' => 'Pendapatan Kasir Tunai: ' .
                            $detailBarang->nama .
                            ' - ' .
                            Carbon::now('Asia/Jakarta')->format('d/m/Y H:i'),
                        'saldo_persatuan' => $saldoSatuanBaru,
                        'saldo_perkemasan' => $saldoPerKemasanBaru,
                        'journal_entry_id' => $entry->id,
                        'user_id' => auth()->id(),
                    ]);

                    $receiptItems[] = [
                        'nama' => $detailBarang->nama,
                        'qty' => $jumlahDijual,
                        'harga' => $detailBarang->harga_jual_per_unit,
                        'subtotal' => $jumlahDijual * $detailBarang->harga_jual_per_unit,
                    ];
                }
            }

            // Simpan Log Transaksi Kasir
            KasirTransactionLog::create([
                'user_id' => $userId,
                'journal_entry_id' => $entry->id,
                'uraian' => "Penjualan Kasir - Pembayaran: {$namaPembayaran}",
                'tanggal_transaksi' => now(),
                'bayar' => $request->uang_bayar,
                'kembalian' => $request->uang_kembalian,
                'jumlah' => $request->grand_total,
                'diskon' => $request->diskon_total ?? 0,
                'paket_diskon_id' => $request->paket_diskon_id,
            ]);

            DB::commit();

            $receiptData = [
                'toko' => auth()->user()->name ?: config('app.name', 'Kasir Store'),
                'alamat' => auth()->user()->alamat ?: 'Alamat Toko',
                'no_telp' => auth()->user()->nomor_telepon ?: 'Nomor Telepon',
                'tanggal' => Carbon::now('Asia/Jakarta')->format('d/m/Y H:i'),
                'kode_transaksi' => $kodeTransaksi,
                'jenis_pembayaran' => $namaPembayaran,
                'items' => $receiptItems,
                'subtotal_keseluruhan' => $pendapatanTotal,
                'diskon' => $request->diskon_total ?? 0,
                'total' => $request->grand_total,
                'bayar' => $request->uang_bayar,
                'kembali' => $request->uang_kembalian,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error occurred while processing request', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi Error: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Transaksi berhasil')->with('receipt', $receiptData);
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
                ->with('error', 'Terjadi Error: ' . $e->getMessage());
        }
    }
}
