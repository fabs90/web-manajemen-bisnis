<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\KasKecil;
use App\Models\PengisianKasKecilLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ManajemenKasKecilController extends Controller
{
    public function index()
    {
        $kasKecilLogs = PengisianKasKecilLog::where(
            'user_id',
            auth()->id(),
        )->get();

        $saldoAkhir = KasKecil::where('user_id', auth()->id())->latest()->value('saldo_akhir');

        return view('keuangan.kas-kecil.index', compact('kasKecilLogs', 'saldoAkhir'));
    }

    public function create()
    {
        return view('keuangan.kas-kecil.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $accounts = Account::where('user_id', auth()->id())->get()->keyBy('code');

            if ($accounts->isEmpty()) {
                throw new \Exception('Akun belum diatur. Silakan atur akun di Neraca Awal.');
            }

            $kasUtama = $accounts['1101'] ?? $accounts->firstWhere('category', 'asset');
            $kasKecilAccount = $accounts['1102'] ?? $accounts->firstWhere('category', 'asset');

            if (! $kasUtama || ! $kasKecilAccount) {
                throw new \Exception('Akun Kas Utama atau Kas Kecil tidak ditemukan.');
            }

            $formattedDate = now()->format('Ymd');
            $random = strtoupper(Str::random(6));
            $referenceNumber = "PKK-{$formattedDate}-{$random}";

            $entry = JournalEntry::create([
                'user_id' => auth()->id(),
                'reference_number' => $referenceNumber,
                'date' => now(),
                'description' => 'Pengisian Kas Kecil: '.$request->uraian,
                'transaction_type' => 'kas_kecil',
            ]);

            // Debit: Kas Kecil
            $entry->items()->create([
                'user_id' => auth()->id(),
                'account_id' => $kasKecilAccount->id,
                'debit' => $request->jumlah,
                'credit' => 0,
            ]);

            // Credit: Kas Utama
            $entry->items()->create([
                'user_id' => auth()->id(),
                'account_id' => $kasUtama->id,
                'debit' => 0,
                'credit' => $request->jumlah,
            ]);

            $latestSaldoKasKecil = KasKecil::where('user_id', auth()->id())
                ->latest()
                ->first();
            $saldoAkhir =
                ($latestSaldoKasKecil->saldo_akhir ?? 0) + $request->jumlah;

            // Tambah ke kas kecil
            $kasKecil = KasKecil::create([
                'user_id' => auth()->id(),
                'tanggal' => now(),
                'nomor_referensi' => $referenceNumber,
                'penerimaan' => $request->jumlah,
                'pengeluaran' => 0,
                'saldo_akhir' => $saldoAkhir,
            ]);

            // Tambah ke logs
            PengisianKasKecilLog::create([
                'journal_entry_id' => $entry->id,
                'kas_kecil_id' => $kasKecil->id,
                'uraian' => 'Pengisian Kas Kecil - '.now()->format('d/m/Y'),
                'jumlah' => $request->jumlah,
                'tanggal_transaksi' => now(),
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('keuangan.pengeluaran-kas-kecil.index')
                ->with('success', 'Kas kecil berhasil ditambahkan');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error creating kas kecil: '.$e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Failed to create kas kecil');
        }
    }
}
