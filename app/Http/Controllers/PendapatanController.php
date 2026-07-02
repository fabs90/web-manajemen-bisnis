<?php

namespace App\Http\Controllers;

use App\Http\Requests\PendapatanRequest;
use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\KartuGudang;
use App\Models\Pelanggan;
use App\Services\PendapatanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PendapatanController extends Controller
{
    public function __construct(
        protected PendapatanService $pendapatanService
    ) {}

    public function index()
    {
        $userId = auth()->id();

        // Ambil semua akun untuk mapping kode -> id
        $accounts = Account::where('user_id', $userId)->get()->keyBy('code');

        if (! $accounts->has('1101') || ! $accounts->has('4101')) {
            return redirect()->route('dashboard')->with('error', 'Akun Kas Utama (1101) atau Pendapatan Penjualan (4101) belum diatur.');
        }

        $kasId = $accounts['1101']->id;
        $pendapatanId = $accounts['4101']->id;
        $piutangId = $accounts->has('1104') ? $accounts['1104']->id : null;

        // 1. Data Utama (Penerimaan/Pendapatan)
        // Ambil JournalEntry yang menyentuh akun Kas atau Pendapatan
        $journalEntries = JournalEntry::where('user_id', $userId)
            ->whereHas('items', function ($q) use ($kasId, $pendapatanId) {
                $q->whereIn('account_id', [$kasId, $pendapatanId]);
            })
            ->whereNotIn('transaction_type', [
                'neraca_awal', 
                'membeli_barang', 
                'lain_lain', 
                'membayar_hutang', 
                'kas_kecil', 
                'agenda_perjalanan',
                'pemesanan-barang'
            ])
            ->with(['items.account'])
            ->latest()
            ->get();

        $allDatas = $journalEntries->map(function ($entry) use ($kasId, $pendapatanId, $piutangId) {
            $uangDiterima = $entry->items->where('account_id', $kasId)->sum('debit');
            $totalPenjualan = $entry->items->where('account_id', $pendapatanId)->sum('credit');

            $penjualanTunai = $uangDiterima > 0 ? $totalPenjualan : 0;
            $penjualanKredit = $uangDiterima == 0 ? $totalPenjualan : 0;

            $piutangDagang = $piutangId ? ($entry->items->where('account_id', $piutangId)->sum('debit') + $entry->items->where('account_id', $piutangId)->sum('credit')) : 0;

            return (object) [
                'id' => $entry->id,
                'tanggal' => $entry->created_at,
                'uraian' => $entry->description,
                'piutang_dagang' => $piutangDagang,
                'penjualan_tunai' => $penjualanTunai,
                'penjualan_kredit' => $penjualanKredit,
                'potongan_pembelian' => 0, // Placeholder
                'lain_lain' => 0,
                'uang_diterima' => $uangDiterima,
            ];
        });

        // 2. Data Piutang (Sub-ledger Pelanggan)
        $dataPiutang = collect();
        if ($piutangId) {
            $piutangItems = \App\Models\JournalItem::where('user_id', $userId)
                ->where('account_id', $piutangId)
                ->with(['journalEntry', 'subLedger'])
                ->get();

            $dataPiutang = $piutangItems->groupBy('sub_ledger_id')->map(function ($items) {
                $saldo = 0;

                return $items->sort(function ($a, $b) {
                    $dateA = $a->journalEntry->date;
                    $dateB = $b->journalEntry->date;
                    if ($dateA !== $dateB) {
                        return $dateA <=> $dateB;
                    }

                    $isAwalA = $a->journalEntry->transaction_type === 'neraca_awal' ? 0 : 1;
                    $isAwalB = $b->journalEntry->transaction_type === 'neraca_awal' ? 0 : 1;
                    if ($isAwalA !== $isAwalB) {
                        return $isAwalA <=> $isAwalB;
                    }

                    $createdA = $a->journalEntry->created_at;
                    $createdB = $b->journalEntry->created_at;
                    if ($createdA !== $createdB) {
                        return $createdA <=> $createdB;
                    }

                    return $a->id <=> $b->id;
                })->values()->map(function ($item) use (&$saldo) {
                    $saldo += ($item->debit - $item->credit);

                    return (object) [
                        'id' => $item->id,
                        'tanggal' => $item->journalEntry->date,
                        'uraian' => $item->journalEntry->description,
                        'debit' => $item->debit,
                        'kredit' => $item->credit,
                        'saldo' => $saldo,
                        'pelanggan' => $item->subLedger,
                    ];
                });
            });
        }

        $totalPendapatan = $allDatas->sum('uang_diterima');
        $bunga_bank = (object) ['bunga_bank' => 0];

        return view('keuangan.pendapatan.list', compact('allDatas', 'dataPiutang', 'totalPendapatan', 'bunga_bank'));
    }

    public function create()
    {
        $userId = auth()->id();

        // Ambil semua debitur
        $debitur = Pelanggan::where('user_id', $userId)
            ->where('jenis', 'debitur')
            ->orderBy('nama')
            ->get();

        // Ambil semua barang
        $barang = Barang::where('user_id', $userId)
            ->orderBy('nama')
            ->get();

        // Ambil piutang aktif dari JournalItem account 1104
        $accounts = Account::where('user_id', $userId)->get()->keyBy('code');
        $piutangId = $accounts->has('1104') ? $accounts['1104']->id : null;

        $listPiutang = collect();
        if ($piutangId) {
            // Kita ambil reference_number yang masih memiliki saldo piutang > 0
            $activePiutang = \App\Models\JournalItem::where('user_id', $userId)
                ->where('account_id', $piutangId)
                ->with(['journalEntry', 'subLedger'])
                ->get()
                ->groupBy('sub_ledger_id');

            foreach ($activePiutang as $pelangganId => $items) {
                $saldo = $items->sum('debit') - $items->sum('credit');
                if ($saldo > 0) {
                    $firstItem = $items->first();
                    $listPiutang->push((object) [
                        'kode' => $firstItem->id,
                        'pelanggan_id' => $pelangganId,
                        'pelanggan' => $firstItem->subLedger,
                        'saldo' => $saldo,
                    ]);
                }
            }
        }

        return view(
            'keuangan.pendapatan.create',
            compact('debitur', 'barang', 'listPiutang'),
        );
    }

    public function store(PendapatanRequest $request)
    {
        $userId = auth()->id();
        $accounts = Account::where('user_id', $userId)->get()->keyBy('code');

        DB::beginTransaction();
        try {
            $this->pendapatanService->storePendapatan($request, $userId, $accounts);

            DB::commit();

            return redirect()->route('keuangan.pendapatan.list')->with('success', 'Penerimaan berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan pendapatan: '.$e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $userId = auth()->id();
            $entry = JournalEntry::where('id', $id)->where('user_id', $userId)->firstOrFail();

            // Hapus KartuGudang terkait
            KartuGudang::where('journal_entry_id', $entry->id)->delete();

            // Hapus JournalEntry (akan cascade ke JournalItems)
            $entry->delete();

            DB::commit();

            return redirect()->route('keuangan.pendapatan.list')->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus pendapatan: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal menghapus: '.$e->getMessage());
        }
    }

    public function show(int $id)
    {
        $userId = auth()->id();
        $entry = JournalEntry::where('id', $id)
            ->where('user_id', $userId)
            ->with(['items.account', 'items.subLedger'])
            ->firstOrFail();

        return view('keuangan.pendapatan.show', compact('entry'));
    }

    public function createLain()
    {
        return view('keuangan.pendapatan.create_lain');
    }

    public function storeLain(Request $request)
    {
        $validatedData = $request->validate([
            'uraian_pendapatan' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric',
        ]);

        $userId = auth()->id();
        $accounts = Account::where('user_id', $userId)->get()->keyBy('code');

        DB::beginTransaction();
        try {
            $prefix = 'PEND-LAIN';
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

            // Debit: Kas Utama (1101)
            $entry->items()->create([
                'user_id' => $userId,
                'journal_entry_id' => $entry->id,
                'account_id' => $accounts['1101']->id,
                'debit' => $request->jumlah,
                'credit' => 0,
            ]);

            // Credit: Pendapatan Penjualan (4101) - Or a specific "Other Income" account if available
            $entry->items()->create([
                'user_id' => $userId,
                'journal_entry_id' => $entry->id,
                'account_id' => $accounts['4101']->id,
                'debit' => 0,
                'credit' => $request->jumlah,
            ]);

            DB::commit();

            return redirect()
                ->route('keuangan.pendapatan.create_lain')
                ->with('success', 'Data pendapatan lain berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan pendapatan lain: '.$e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan: '.$e->getMessage());
        }
    }

    public function destroyPiutang($id)
    {
        // Dalam sistem baru, piutang hanyalah JournalItem.
        // Untuk menghapus "transaksi piutang", kita hapus JournalEntry-nya.
        // Tapi di view list.blade.php, action mengarah ke destroyPiutang dengan $item->id (JournalItem ID)
        // Jadi kita cari JournalEntry dari JournalItem tersebut.

        DB::beginTransaction();
        try {
            $userId = auth()->id();
            $item = \App\Models\JournalItem::where('id', $id)->where('user_id', $userId)->firstOrFail();
            $entry = $item->journalEntry;

            if ($entry) {
                // Hapus KartuGudang terkait jika ada
                KartuGudang::where('journal_entry_id', $entry->id)->delete();
                $entry->delete();
            } else {
                $item->delete();
            }

            DB::commit();

            return redirect()
                ->route('keuangan.pendapatan.list')
                ->with(
                    'success',
                    'Data piutang & penyesuaian kas berhasil dihapus.',
                );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus piutang: '.$e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus: '.$e->getMessage());
        }
    }
}
