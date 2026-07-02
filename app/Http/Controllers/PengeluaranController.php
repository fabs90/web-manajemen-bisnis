<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengeluaranRequest;
use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Pelanggan;
use App\Services\PengeluaranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PengeluaranController extends Controller
{
    public function __construct(
        protected PengeluaranService $pengeluaranService
    ) {}

    public function list(): View
    {
        $userId = auth()->id();

        // Ambil semua JournalEntry yang merupakan pengeluaran
        $journalEntries = JournalEntry::where('user_id', $userId)
            ->whereIn('transaction_type', ['membeli_barang', 'lain_lain', 'membayar_hutang', 'kas_kecil', 'agenda_perjalanan'])
            ->with(['items.account'])
            ->latest()
            ->get();

        // Map ke format yang diharapkan view
        $allDatas = $journalEntries->map(function ($entry) {
            $hutangItem = $entry->items->firstWhere('account.code', '2101');
            $kasItem = $entry->items->firstWhere('account.code', '1101');

            // Cari item potongan (credit pada akun expense)
            $potonganItem = $entry->items->filter(fn ($item) => $item->credit > 0 && $item->account->category === 'expense')->first();
            $potonganAmount = $potonganItem->credit ?? 0;

            // Total biaya kotor (debit pada akun selain kas/bank/hutang)
            $biayaKotor = $entry->items->filter(fn ($item) => $item->debit > 0 && ! in_array($item->account->code, ['1101', '1103', '2101']))->sum('debit');

            return (object) [
                'id' => $entry->id,
                'tanggal' => $entry->date,
                'uraian' => $entry->description,
                'jumlah_hutang' => $entry->transaction_type === 'membeli_barang' ? ($hutangItem->credit ?? 0) : 0,
                'jumlah_pembelian_tunai' => $entry->transaction_type === 'membeli_barang' ? ($kasItem->credit ?? 0) : 0,
                'potongan_pembelian' => $potonganAmount,
                'lain_lain' => in_array($entry->transaction_type, ['lain_lain', 'agenda_perjalanan']) ? $biayaKotor : 0,
                'keluar_kas_kecil' => $entry->items->where('account.code', '1102')->sum('credit'),
                'jumlah_pengeluaran' => $entry->items->where('account.code', '1101')->sum('credit') + $entry->items->where('account.code', '1103')->sum('credit') + $entry->items->where('account.code', '2101')->sum('credit'),
                'transaction_type' => $entry->transaction_type,
                'hutang' => collect($entry->transaction_type === 'membayar_hutang' ? [$entry] : []), // Untuk deteksi pelunasan di view
            ];
        });

        $dataPengeluaran = $allDatas->where('transaction_type', '!=', 'kas_kecil')->values();
        $dataKasKecil = $allDatas->where('transaction_type', 'kas_kecil')->values();

        $totalPengeluaran = $dataPengeluaran->sum('jumlah_pengeluaran');
        $totalKeluarKasKecil = $dataKasKecil->sum('keluar_kas_kecil');

        // Data Hutang: Group by pelanggan dari JournalItem akun Utang Usaha (2101)
        $dataHutang = JournalItem::where('user_id', $userId)
            ->whereHas('account', fn ($q) => $q->where('code', '2101'))
            ->with(['journalEntry', 'subLedger'])
            ->get()
            ->groupBy('sub_ledger_id');

        // Transform dataHutang agar memiliki struktur yang sama dengan BukuBesarHutang lama untuk view
        $dataHutangFormatted = $dataHutang->map(function ($items) {
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
                $saldo += ($item->credit - $item->debit);

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

        return view('keuangan.pengeluaran.list', [
            'dataPengeluaran' => $dataPengeluaran,
            'dataKasKecil' => $dataKasKecil,
            'totalPengeluaran' => $totalPengeluaran,
            'totalKeluarKasKecil' => $totalKeluarKasKecil,
            'dataHutang' => $dataHutangFormatted,
        ]);
    }

    public function create(): View
    {
        $userId = auth()->id();

        $kreditur = Pelanggan::where('user_id', $userId)
            ->where('jenis', 'kreditur')
            ->get();

        $barang = Barang::where('user_id', $userId)->get();

        // List Hutang Aktif (yang saldonya > 0)
        $utangAccount = Account::where('user_id', $userId)->where('code', '2101')->first();

        $listHutang = collect();
        if ($utangAccount) {
            $items = JournalItem::where('user_id', $userId)
                ->where('account_id', $utangAccount->id)
                ->with('subLedger')
                ->get()
                ->groupBy('sub_ledger_id');

            foreach ($items as $pelangganId => $pelangganItems) {
                $saldo = $pelangganItems->sum('credit') - $pelangganItems->sum('debit');
                if ($saldo > 0) {
                    $lastItem = $pelangganItems->sortByDesc('id')->first();
                    $listHutang->push((object) [
                        'id' => $lastItem->id, // Kita gunakan ID item terakhir sebagai referensi
                        'pelanggan_id' => $pelangganId,
                        'uraian' => 'Hutang kepada '.($lastItem->subLedger->nama ?? 'Unknown'),
                        'tanggal' => $lastItem->journalEntry->date ?? now(),
                        'saldo' => $saldo,
                        'pelanggan' => $lastItem->subLedger,
                    ]);
                }
            }
        }

        return view('keuangan.pengeluaran.create', compact('kreditur', 'barang', 'listHutang'));
    }

    public function store(PengeluaranRequest $request): RedirectResponse
    {
        try {
            $this->pengeluaranService->store($request->validated(), auth()->id());

            return redirect()
                ->route('keuangan.pengeluaran.list') // Redirect ke list saja agar user bisa lihat hasilnya
                ->with('success', 'Data pengeluaran berhasil disimpan.');
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan pengeluaran', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all(),
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal menyimpan pengeluaran: '.$e->getMessage());
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->pengeluaranService->destroy($id, auth()->id());

            return redirect()
                ->route('keuangan.pengeluaran.list')
                ->with('success', 'Pengeluaran berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus pengeluaran: '.$e->getMessage());
        }
    }

    public function show(int $id): View
    {
        $userId = auth()->id();
        $entry = JournalEntry::where('id', $id)
            ->where('user_id', $userId)
            ->with(['items.account', 'items.subLedger'])
            ->firstOrFail();

        return view('keuangan.pengeluaran.show', compact('entry'));
    }

    public function destroyHutang(int $id): RedirectResponse
    {
        // Dalam sistem jurnal, menghapus satu item hutang berarti menghapus entry jurnalnya
        // atau melakukan penyesuaian. Di sini kita asumsikan hapus entry terkait item tersebut.
        try {
            $item = JournalItem::findOrFail($id);
            $this->pengeluaranService->destroy($item->journal_entry_id, auth()->id());

            return redirect()
                ->route('keuangan.pengeluaran.list')
                ->with('success', 'Data hutang berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('keuangan.pengeluaran.list')
                ->with('error', 'Gagal menghapus hutang: '.$e->getMessage());
        }
    }

    public function destroyPelunasanHutang(int $pengeluaranId): RedirectResponse
    {
        return $this->destroy($pengeluaranId);
    }
}
