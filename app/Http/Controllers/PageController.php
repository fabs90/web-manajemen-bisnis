<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Pelanggan;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function dashboard()
    {
        $userId = Auth::id();

        // Data barang dengan kartu gudang terbaru
        $barangDenganKartuTerbaru = Barang::with([
            'kartuGudang' => function ($query) {
                $query->latest()->take(1);
            },
        ])
            ->where('user_id', $userId)
            ->get();

        // --- Summary Analytics menggunakan Skema Accounting Baru ---

        // Total Kas (Account codes: 1101, 1102, 1103)
        $kasAccounts = Account::where('user_id', $userId)
            ->whereIn('code', ['1101', '1102', '1103'])
            ->get();

        $totalKas = $kasAccounts->sum(function ($account) {
            $sumDebit = JournalItem::where('account_id', $account->id)->sum('debit');
            $sumCredit = JournalItem::where('account_id', $account->id)->sum('credit');

            return $account->opening_balance + $sumDebit - $sumCredit;
        });

        // Detail Kas untuk tabel dashboard
        $detailKas = JournalItem::with(['journalEntry', 'account'])
            ->where('user_id', $userId)
            ->whereIn('account_id', $kasAccounts->pluck('id'))
            ->get()
            ->map(function ($item) {
                return (object) [
                    'created_at' => $item->journalEntry->date,
                    'uraian' => $item->journalEntry->description,
                    'debit' => $item->debit,
                    'kredit' => $item->credit,
                    'saldo' => 0, // Akan dihitung running balance di view atau di sini
                ];
            });

        // Running balance untuk detailKas
        $runningBalance = 0;
        // Kita perlu urutkan berdasarkan tanggal jurnal dan ID jurnal untuk running balance yang konsisten
        $detailKasItems = JournalItem::with(['journalEntry', 'account'])
            ->where('journal_items.user_id', $userId)
            ->whereIn('account_id', $kasAccounts->pluck('id'))
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->orderBy('journal_entries.date')
            ->orderBy('journal_entries.id')
            ->select('journal_items.*')
            ->get();

        $detailKas = $detailKasItems->map(function ($item) use (&$runningBalance) {
            $runningBalance += ($item->debit - $item->credit);

            return (object) [
                'created_at' => $item->journalEntry->date,
                'uraian' => $item->journalEntry->description,
                'debit' => $item->debit,
                'kredit' => $item->credit,
                'saldo' => $runningBalance,
            ];
        });

        // Total Piutang (Account code: 1104)
        $piutangAccount = Account::where('user_id', $userId)->where('code', '1104')->first();
        $totalPiutang = 0;
        if ($piutangAccount) {
            $sumDebit = JournalItem::where('account_id', $piutangAccount->id)->sum('debit');
            $sumCredit = JournalItem::where('account_id', $piutangAccount->id)->sum('credit');
            $totalPiutang = $piutangAccount->opening_balance + $sumDebit - $sumCredit;
        }

        // Total Hutang (Account code: 2101)
        $hutangAccount = Account::where('user_id', $userId)->where('code', '2101')->first();
        $totalHutang = 0;
        if ($hutangAccount) {
            $sumDebit = JournalItem::where('account_id', $hutangAccount->id)->sum('debit');
            $sumCredit = JournalItem::where('account_id', $hutangAccount->id)->sum('credit');
            $totalHutang = $hutangAccount->opening_balance + $sumCredit - $sumDebit; // Kredit normal
        }

        // Total Persediaan (Account code: 1105)
        $persediaanAccount = Account::where('user_id', $userId)->where('code', '1105')->first();
        $totalPersediaan = 0;
        if ($persediaanAccount) {
            $sumDebit = JournalItem::where('account_id', $persediaanAccount->id)->sum('debit');
            $sumCredit = JournalItem::where('account_id', $persediaanAccount->id)->sum('credit');
            $totalPersediaan = $persediaanAccount->opening_balance + $sumDebit - $sumCredit;
        }

        // Laba Bersih (Revenue - Expense)
        $revenueTotal = JournalItem::where('user_id', $userId)
            ->whereHas('account', function ($q) {
                $q->where('category', 'revenue');
            })
            ->selectRaw('SUM(credit) - SUM(debit) as total')
            ->value('total') ?? 0;

        $expenseTotal = JournalItem::where('user_id', $userId)
            ->whereHas('account', function ($q) {
                $q->where('category', 'expense');
            })
            ->selectRaw('SUM(debit) - SUM(credit) as total')
            ->value('total') ?? 0;

        $labaBersih = $revenueTotal - $expenseTotal;

        // Data untuk Chart: Pendapatan vs Pengeluaran per bulan
        $months = [];
        $pendapatanPerBulan = [];
        $pengeluaranPerBulan = [];
        $endDate = now();
        $startDate = now()->subMonths(5);

        $period = CarbonPeriod::create(
            $startDate->startOfMonth(),
            '1 month',
            $endDate->endOfMonth()
        );

        foreach ($period as $date) {
            $monthLabel = $date->format('M Y');
            $months[] = $monthLabel;

            $pendapatan = JournalItem::where('user_id', $userId)
                ->whereHas('account', function ($q) {
                    $q->where('category', 'revenue');
                })
                ->whereHas('journalEntry', function ($q) use ($date) {
                    $q->whereMonth('date', $date->month)->whereYear('date', $date->year);
                })
                ->selectRaw('SUM(credit) - SUM(debit) as total')
                ->value('total') ?? 0;

            $pengeluaran = JournalItem::where('user_id', $userId)
                ->whereHas('account', function ($q) {
                    $q->where('category', 'expense');
                })
                ->whereHas('journalEntry', function ($q) use ($date) {
                    $q->whereMonth('date', $date->month)->whereYear('date', $date->year);
                })
                ->selectRaw('SUM(debit) - SUM(credit) as total')
                ->value('total') ?? 0;

            $pendapatanPerBulan[] = (float) $pendapatan;
            $pengeluaranPerBulan[] = (float) $pengeluaran;
        }

        // Transaksi Terbaru (Journal Entries)
        $transaksiTerbaru = JournalEntry::where('user_id', $userId)
            ->with(['items.account'])
            ->latest('date')
            ->limit(5)
            ->get()
            ->map(function ($entry) {
                // Untuk summary di tabel, kita ambil total debit sebagai "jumlah"
                return (object) [
                    'tanggal' => $entry->date->format('Y-m-d'),
                    'uraian' => $entry->description,
                    'jumlah' => $entry->items->sum('debit'),
                    'tipe' => $entry->transaction_type ?? 'Jurnal Umum',
                ];
            });

        // List Piutang per Pelanggan (Sub-Ledger)
        $listPiutang = [];
        if ($piutangAccount) {
            $listPiutang = JournalItem::with(['subLedger', 'journalEntry'])
                ->where('user_id', $userId)
                ->where('account_id', $piutangAccount->id)
                ->get()
                ->map(function ($item) {
                    return (object) [
                        'tanggal' => $item->journalEntry->date->format('Y-m-d'),
                        'uraian' => $item->journalEntry->description,
                        'debit' => $item->debit,
                        'kredit' => $item->credit,
                        'saldo' => 0, // Running balance per group akan dihitung di view atau transform
                        'sub_ledger' => $item->subLedger,
                        'sub_ledger_id' => $item->sub_ledger_id,
                    ];
                })
                ->groupBy('sub_ledger_id');

            // Hitung running balance per pelanggan
            $listPiutang = $listPiutang->map(function ($items) {
                $rb = 0;

                return $items->sortBy('tanggal')->map(function ($item) use (&$rb) {
                    $rb += ($item->debit - $item->kredit);
                    $item->saldo = $rb;

                    return $item;
                });
            });
        }

        // List Hutang per Pelanggan/Supplier (Sub-Ledger)
        $listHutang = [];
        if ($hutangAccount) {
            $listHutang = JournalItem::with(['subLedger', 'journalEntry'])
                ->where('user_id', $userId)
                ->where('account_id', $hutangAccount->id)
                ->get()
                ->map(function ($item) {
                    return (object) [
                        'tanggal' => $item->journalEntry->date->format('Y-m-d'),
                        'uraian' => $item->journalEntry->description,
                        'debit' => $item->debit,
                        'kredit' => $item->credit,
                        'saldo' => 0,
                        'sub_ledger' => $item->subLedger,
                        'sub_ledger_id' => $item->sub_ledger_id,
                    ];
                })
                ->groupBy('sub_ledger_id');

            // Hitung running balance per sub-ledger (kredit normal)
            $listHutang = $listHutang->map(function ($items) {
                $rb = 0;

                return $items->sortBy('tanggal')->map(function ($item) use (&$rb) {
                    $rb += ($item->kredit - $item->debit);
                    $item->saldo = $rb;

                    return $item;
                });
            });
        }

        return view(
            'layouts.main',
            compact(
                'barangDenganKartuTerbaru',
                'totalKas',
                'totalPiutang',
                'totalHutang',
                'totalPersediaan',
                'labaBersih',
                'months',
                'pendapatanPerBulan',
                'pengeluaranPerBulan',
                'transaksiTerbaru',
                'detailKas',
                'listPiutang',
                'listHutang'
            )
        );
    }

    public function chartData(Request $request)
    {
        $userId = Auth::id();
        $periode = (int) $request->get('periode', 6);
        $endDate = now();
        $startDate = $periode === 1 ? now()->startOfMonth() : now()->subMonths($periode - 1);

        $labels = [];
        $dataPendapatan = [];
        $dataPengeluaran = [];

        if ($periode === 1) {
            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $labels[] = $date->format('d M');

                $pendapatan = JournalItem::where('user_id', $userId)
                    ->whereHas('account', function ($q) {
                        $q->where('category', 'revenue');
                    })
                    ->whereHas('journalEntry', function ($q) use ($date) {
                        $q->whereDate('date', $date);
                    })
                    ->selectRaw('SUM(credit) - SUM(debit) as total')->value('total') ?? 0;

                $pengeluaran = JournalItem::where('user_id', $userId)
                    ->whereHas('account', function ($q) {
                        $q->where('category', 'expense');
                    })
                    ->whereHas('journalEntry', function ($q) use ($date) {
                        $q->whereDate('date', $date);
                    })
                    ->selectRaw('SUM(debit) - SUM(credit) as total')->value('total') ?? 0;

                $dataPendapatan[] = (float) $pendapatan;
                $dataPengeluaran[] = (float) $pengeluaran;
            }
        } else {
            $period = CarbonPeriod::create($startDate->startOfMonth(), '1 month', $endDate->endOfMonth());
            foreach ($period as $date) {
                $labels[] = $date->format('M Y');

                $pendapatan = JournalItem::where('user_id', $userId)
                    ->whereHas('account', function ($q) {
                        $q->where('category', 'revenue');
                    })
                    ->whereHas('journalEntry', function ($q) use ($date) {
                        $q->whereMonth('date', $date->month)->whereYear('date', $date->year);
                    })
                    ->selectRaw('SUM(credit) - SUM(debit) as total')->value('total') ?? 0;

                $pengeluaran = JournalItem::where('user_id', $userId)
                    ->whereHas('account', function ($q) {
                        $q->where('category', 'expense');
                    })
                    ->whereHas('journalEntry', function ($q) use ($date) {
                        $q->whereMonth('date', $date->month)->whereYear('date', $date->year);
                    })
                    ->selectRaw('SUM(debit) - SUM(credit) as total')->value('total') ?? 0;

                $dataPendapatan[] = (float) $pendapatan;
                $dataPengeluaran[] = (float) $pengeluaran;
            }
        }

        return response()->json([
            'labels' => $labels,
            'pendapatan' => $dataPendapatan,
            'pengeluaran' => $dataPengeluaran,
        ]);
    }

    public function getStarted()
    {
        return view('get-started');
    }

    public function deleteAllData() {}

    public function dashboard_superadmin()
    {
        return view('superadmin.dashboard.superadmin');
    }
}
