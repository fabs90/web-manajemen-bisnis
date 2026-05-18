<?php

namespace App\Http\Controllers;

use App\Http\Requests\NeracaAwalRequest;
use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\KartuGudang;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class NeracaAwalController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        // Tampilkan daftar Jurnal Entry yang bertipe 'neraca_awal'
        $entries = JournalEntry::where('user_id', $userId)
            ->where('transaction_type', 'neraca_awal')
            ->latest('date')
            ->get();

        return view('keuangan.neraca-awal.list', compact('entries'));
    }

    public function create()
    {
        $userId = Auth::id();
        $user = Auth::user()->name;
        $debitur = Pelanggan::where('user_id', $userId)
            ->where('jenis', 'debitur')
            ->get();
        $kreditur = Pelanggan::where('user_id', $userId)
            ->where('jenis', 'kreditur')
            ->get();
        $barang = Barang::where('user_id', $userId)->get();
        $kartuGudang = KartuGudang::where('user_id', $userId)
            ->latest()
            ->get();

        return view(
            'keuangan.neraca-awal.create',
            compact('user', 'debitur', 'barang', 'kartuGudang', 'kreditur'),
        );
    }

    public function store(NeracaAwalRequest $request)
    {
        $validated = $request->validated();
        $userId = Auth::id();
        DB::beginTransaction();

        try {
            // 1. Ambil Akun-Akun yang diperlukan
            $accounts = Account::where('user_id', $userId)->get()->keyBy('code');

            // 2. Hitung total hutang & piutang
            $totalHutang = collect($validated['hutang'] ?? [])->sum('jumlah');
            $totalPiutang = collect($validated['piutang'] ?? [])->sum('jumlah');

            $totalDebit = collect([
                $validated['kas'],
                $totalPiutang,
                $validated['total_persediaan'] ?? 0,
                $validated['tanah_bangunan'] ?? 0,
                $validated['kendaraan'] ?? 0,
                $validated['meubel_peralatan'] ?? 0,
            ])->sum();

            $modal = $totalDebit - $totalHutang;

            // 3. Simpan Header Jurnal
            $entry = JournalEntry::create([
                'user_id' => $userId,
                'reference_number' => 'NA-'.now()->format('YmdHis'),
                'date' => now(),
                'description' => 'Saldo Awal - Neraca Awal',
                'transaction_type' => 'neraca_awal',
            ]);

            // 4. Simpan Detail Jurnal (Journal Items)

            // A. Kas Utama (1101)
            if ($validated['kas'] > 0) {
                JournalItem::create([
                    'user_id' => $userId,
                    'journal_entry_id' => $entry->id,
                    'account_id' => $accounts['1101']->id,
                    'debit' => $validated['kas'],
                    'credit' => 0,
                ]);
            }

            // B. Piutang Usaha (1104) - Per Pelanggan
            if (! empty($validated['piutang']) && ! $request->has('tidak_ada_piutang')) {
                foreach ($validated['piutang'] as $p) {
                    if ($p['jumlah'] > 0) {
                        JournalItem::create([
                            'user_id' => $userId,
                            'journal_entry_id' => $entry->id,
                            'account_id' => $accounts['1104']->id,
                            'sub_ledger_type' => Pelanggan::class,
                            'sub_ledger_id' => $p['nama'], // 'nama' di form adalah ID pelanggan
                            'debit' => $p['jumlah'],
                            'credit' => 0,
                        ]);
                    }
                }
            }

            // C. Persediaan Barang Dagang (1105)
            if (($validated['total_persediaan'] ?? 0) > 0) {
                JournalItem::create([
                    'user_id' => $userId,
                    'journal_entry_id' => $entry->id,
                    'account_id' => $accounts['1105']->id,
                    'debit' => $validated['total_persediaan'],
                    'credit' => 0,
                ]);

                // Link Kartu Gudang yang dipilih ke Journal Entry ini
                if (! empty($validated['barang_ids'])) {
                    KartuGudang::whereIn('barang_id', $validated['barang_ids'])
                        ->where('user_id', $userId)
                        ->update(['journal_entry_id' => $entry->id]);
                }
            }

            // D. Aset Tetap
            $fixedAssets = [
                '1203' => 'tanah_bangunan',
                '1202' => 'kendaraan',
                '1201' => 'meubel_peralatan',
            ];

            foreach ($fixedAssets as $code => $field) {
                if (($validated[$field] ?? 0) > 0) {
                    JournalItem::create([
                        'user_id' => $userId,
                        'journal_entry_id' => $entry->id,
                        'account_id' => $accounts[$code]->id,
                        'debit' => $validated[$field],
                        'credit' => 0,
                    ]);
                }
            }

            // E. Utang Usaha (2101) - Per Pelanggan/Supplier
            if (! empty($validated['hutang']) && ! $request->has('tidak_ada_hutang')) {
                foreach ($validated['hutang'] as $h) {
                    if ($h['jumlah'] > 0) {
                        JournalItem::create([
                            'user_id' => $userId,
                            'journal_entry_id' => $entry->id,
                            'account_id' => $accounts['2101']->id,
                            'sub_ledger_type' => Pelanggan::class,
                            'sub_ledger_id' => $h['nama'],
                            'debit' => 0,
                            'credit' => $h['jumlah'],
                        ]);
                    }
                }
            }

            // F. Modal Pemilik (3100)
            if ($modal > 0) {
                JournalItem::create([
                    'user_id' => $userId,
                    'journal_entry_id' => $entry->id,
                    'account_id' => $accounts['3100']->id,
                    'debit' => 0,
                    'credit' => $modal,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('laporan-keuangan.neraca-awal.index')
                ->with('success', 'Neraca Awal berhasil disimpan ke jurnal.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error saving Neraca Awal: '.$e->getMessage());

            return redirect()
                ->route('laporan-keuangan.neraca-awal.create')
                ->withInput()
                ->withErrors('Gagal menyimpan: '.$e->getMessage());
        }
    }

    public function show($id)
    {
        $userId = Auth::id();
        $entry = JournalEntry::with(['items.account', 'items.subLedger'])
            ->where('user_id', $userId)
            ->findOrFail($id);

        return view('keuangan.neraca-awal.show', compact('entry'));
    }

    public function destroy($id)
    {
        $userId = Auth::id();
        $entry = JournalEntry::where('user_id', $userId)
            ->where('transaction_type', 'neraca_awal')
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            // Unlink Kartu Gudang
            KartuGudang::where('journal_entry_id', $entry->id)
                ->where('user_id', $userId)
                ->update(['journal_entry_id' => null]);

            // Hapus Jurnal Header (Cascade will delete items)
            $entry->delete();

            DB::commit();

            return redirect()
                ->route('laporan-keuangan.neraca-awal.index')
                ->with('success', 'Neraca Awal berhasil dihapus.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error deleting Neraca Awal: '.$e->getMessage());

            return redirect()
                ->route('laporan-keuangan.neraca-awal.index')
                ->withErrors('Gagal menghapus: '.$e->getMessage());
        }
    }
}
