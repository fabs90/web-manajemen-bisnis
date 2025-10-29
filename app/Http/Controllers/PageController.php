<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BiayaOperasional;
use App\Models\BukuBesarHutang;
use App\Models\BukuBesarKas;
use App\Models\BukuBesarPendapatan;
use App\Models\BukuBesarPengeluaran;
use App\Models\BukuBesarPiutang;
use App\Models\KartuGudang;
use App\Models\RugiLaba;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function index()
    {
        return view("welcome");
    }

    public function dashboard()
    {
        $barang = Barang::all();
        $kartuBarang = KartuGudang::all();
        $barangDenganKartuTerbaru = Barang::with([
            "kartuGudang" => function ($query) {
                $query->latest("id")->limit(1);
            },
        ])->get();

        $userId = Auth::id();

        // Data barang dengan kartu gudang terbaru (seperti yang sudah ada)
        $barangDenganKartuTerbaru = Barang::with([
            "kartuGudang" => function ($query) {
                $query->latest()->take(1);
            },
        ])
            ->where("user_id", $userId)
            ->get();

        // Summary Analytics
        // Total Kas (dari buku_besar_kas, ambil saldo terakhir)
        $totalKas =
            BukuBesarKas::where("user_id", $userId)
                ->latest("tanggal")
                ->value("saldo") ?? 0;

        // Total Piutang (sum saldo dari buku_besar_piutang per pelanggan, ambil terbaru per pelanggan)
        $totalPiutang = BukuBesarPiutang::select(
            "pelanggan_id",
            DB::raw("MAX(id) as max_id"),
        )
            ->where("user_id", $userId)
            ->groupBy("pelanggan_id")
            ->get()
            ->map(function ($item) {
                return BukuBesarPiutang::find($item->max_id)->saldo;
            })
            ->sum();

        // Total Hutang (sum saldo dari buku_besar_hutang per pelanggan, ambil terbaru per pelanggan)
        $totalHutang = BukuBesarHutang::select(
            "pelanggan_id",
            DB::raw("MAX(id) as max_id"),
        )
            ->where("user_id", $userId)
            ->groupBy("pelanggan_id")
            ->get()
            ->map(function ($item) {
                return BukuBesarHutang::find($item->max_id)->saldo;
            })
            ->sum();

        // Total Persediaan (sum stok terbaru dari kartu_gudang * harga_beli_per_unit)
        $totalPersediaan = Barang::where("user_id", $userId)
            ->with([
                "kartuGudang" => function ($query) {
                    $query->latest()->take(1);
                },
            ])
            ->get()
            ->map(function ($barang) {
                $kartu = $barang->kartuGudang->first();
                $stokUnit = $kartu ? $kartu->saldo_persatuan : 0;
                return $stokUnit * $barang->harga_beli_per_unit;
            })
            ->sum();

        // Laba Bersih (dari rugi_laba terbaru, atau hitung sederhana: total pendapatan - total pengeluaran - biaya operasional)
        $labaBersih =
            RugiLaba::where("user_id", $userId)
                ->latest()
                ->value("laba_bersih") ?? 0;

        if ($labaBersih == 0) {
            // Hitung alternatif jika belum ada di rugi_laba
            $totalPendapatan = BukuBesarPendapatan::where(
                "user_id",
                $userId,
            )->sum("uang_diterima");
            $totalPengeluaran = BukuBesarPengeluaran::where(
                "user_id",
                $userId,
            )->sum("jumlah_pengeluaran");
            $totalBiayaOperasional = BiayaOperasional::where(
                "user_id",
                $userId,
            )->sum("jumlah");
            $labaBersih =
                $totalPendapatan - $totalPengeluaran - $totalBiayaOperasional;
        }

        // Data untuk Chart: Pendapatan vs Pengeluaran per bulan
        $months = [];
        $pendapatanPerBulan = [];
        $pengeluaranPerBulan = [];
        $endDate = now();
        $startDate = now()->subMonths(5);

        $period = \Carbon\CarbonPeriod::create(
            $startDate->startOfMonth(),
            $endDate->endOfMonth(),
        );

        foreach ($period as $date) {
            $monthLabel = $date->format("M Y");

            $pendapatan = BukuBesarPendapatan::where("user_id", $userId)
                ->whereMonth("tanggal", $date->month)
                ->whereYear("tanggal", $date->year)
                ->sum("uang_diterima");

            $pengeluaran = BukuBesarPengeluaran::where("user_id", $userId)
                ->whereMonth("tanggal", $date->month)
                ->whereYear("tanggal", $date->year)
                ->sum("jumlah_pengeluaran");

            $months[] = $monthLabel;
            $pendapatanPerBulan[] = $pendapatan ?? 0;
            $pengeluaranPerBulan[] = $pengeluaran ?? 0;
        }

        // Transaksi Terbaru (misalnya pendapatan dan pengeluaran terbaru, limit 5)
        $transaksiTerbaru = BukuBesarPendapatan::select(
            "id",
            "tanggal",
            "uraian",
            "uang_diterima as jumlah",
            DB::raw("'Pendapatan' as tipe"),
        )
            ->where("user_id", $userId)
            ->union(
                BukuBesarPengeluaran::select(
                    "id",
                    "tanggal",
                    "uraian",
                    "jumlah_pengeluaran as jumlah",
                    DB::raw("'Pengeluaran' as tipe"),
                )->where("user_id", $userId),
            )
            ->latest("tanggal")
            ->limit(5)
            ->get();

        return view(
            "layouts.main",
            compact(
                "barang",
                "kartuBarang",
                "barangDenganKartuTerbaru",
                "totalKas",
                "totalPiutang",
                "totalHutang",
                "totalPersediaan",
                "labaBersih",
                "months",
                "pendapatanPerBulan",
                "pengeluaranPerBulan",
                "transaksiTerbaru",
            ),
        );
    }

    public function chartData(Request $request)
    {
        $userId = Auth::id();
        $periode = (int) $request->get("periode", 6); // default 6 bulan
        $endDate = now();
        $startDate =
            $periode === 1 ? now()->subMonth() : now()->subMonths($periode - 1);

        if ($periode === 1) {
            // === Periode 1 bulan terakhir: tampil per hari ===
            $pendapatan = BukuBesarPendapatan::selectRaw(
                "DATE(tanggal) as tgl, SUM(uang_diterima) as total",
            )
                ->where("user_id", $userId)
                ->whereBetween("tanggal", [$startDate, $endDate])
                ->groupBy("tgl")
                ->orderBy("tgl")
                ->pluck("total", "tgl")
                ->toArray();

            $pengeluaran = BukuBesarPengeluaran::selectRaw(
                "DATE(tanggal) as tgl, SUM(jumlah_pengeluaran) as total",
            )
                ->where("user_id", $userId)
                ->whereBetween("tanggal", [$startDate, $endDate])
                ->groupBy("tgl")
                ->orderBy("tgl")
                ->pluck("total", "tgl")
                ->toArray();

            $labels = [];
            $dataPendapatan = [];
            $dataPengeluaran = [];

            $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $tgl = $date->format("Y-m-d");
                $labels[] = $date->format("d M");
                $dataPendapatan[] = $pendapatan[$tgl] ?? 0;
                $dataPengeluaran[] = $pengeluaran[$tgl] ?? 0;
            }
        } else {
            // === Periode lebih dari 1 bulan: tampil per bulan ===
            $pendapatan = BukuBesarPendapatan::selectRaw(
                "YEAR(tanggal) as tahun, MONTH(tanggal) as bulan, SUM(uang_diterima) as total",
            )
                ->where("user_id", $userId)
                ->whereBetween("tanggal", [$startDate, $endDate])
                ->groupBy("tahun", "bulan")
                ->orderBy("tahun")
                ->orderBy("bulan")
                ->get()
                ->mapWithKeys(
                    fn($row) => [
                        sprintf(
                            "%04d-%02d",
                            $row->tahun,
                            $row->bulan,
                        ) => $row->total,
                    ],
                )
                ->toArray();

            $pengeluaran = BukuBesarPengeluaran::selectRaw(
                "YEAR(tanggal) as tahun, MONTH(tanggal) as bulan, SUM(jumlah_pengeluaran) as total",
            )
                ->where("user_id", $userId)
                ->whereBetween("tanggal", [$startDate, $endDate])
                ->groupBy("tahun", "bulan")
                ->orderBy("tahun")
                ->orderBy("bulan")
                ->get()
                ->mapWithKeys(
                    fn($row) => [
                        sprintf(
                            "%04d-%02d",
                            $row->tahun,
                            $row->bulan,
                        ) => $row->total,
                    ],
                )
                ->toArray();

            $labels = [];
            $dataPendapatan = [];
            $dataPengeluaran = [];

            $period = \Carbon\CarbonPeriod::create(
                $startDate->startOfMonth(),
                "1 month",
                $endDate->endOfMonth(),
            );

            foreach ($period as $date) {
                $key = $date->format("Y-m");
                $labels[] = $date->format("M Y");
                $dataPendapatan[] = $pendapatan[$key] ?? 0;
                $dataPengeluaran[] = $pengeluaran[$key] ?? 0;
            }
        }

        return response()->json([
            "labels" => $labels,
            "pendapatan" => $dataPendapatan,
            "pengeluaran" => $dataPengeluaran,
        ]);
    }
}
