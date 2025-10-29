<?php

namespace App\Http\Controllers;

use App\Models\BukuBesarHutang;
use App\Models\BukuBesarKas;
use App\Models\BukuBesarPendapatan;
use App\Models\BukuBesarPengeluaran;
use App\Models\BukuBesarPiutang;
use App\Models\KartuGudang;
use App\Models\RugiLaba;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NeracaAkhirController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // === 1. Ambil saldo terakhir Kas ===
        $kasTerakhir = BukuBesarKas::where("user_id", $userId)
            ->latest("created_at")
            ->first();
        $saldoKas = $kasTerakhir->saldo ?? 0;

        // === 2. Ambil total saldo Piutang ===
        $totalPiutang = BukuBesarPiutang::where("user_id", $userId)
            ->select(DB::raw("MAX(id) as id"))
            ->groupBy("pelanggan_id")
            ->pluck("id");
        $saldoPiutang = BukuBesarPiutang::whereIn("id", $totalPiutang)->sum(
            "saldo",
        );

        // === 3. Ambil total saldo Hutang ===
        $totalHutang = BukuBesarHutang::where("user_id", $userId)
            ->select(DB::raw("MAX(id) as id"))
            ->groupBy("pelanggan_id")
            ->pluck("id");
        $saldoHutang = BukuBesarHutang::whereIn("id", $totalHutang)->sum(
            "saldo",
        );

        // === 4. Ambil nilai persediaan barang terakhir (Harga Beli x Saldo Gudang) ===
        $persediaan = KartuGudang::with("barang")
            ->where("user_id", $userId)
            ->select("barang_id", DB::raw("MAX(id) as id"))
            ->groupBy("barang_id")
            ->pluck("id");

        $nilaiPersediaan = KartuGudang::whereIn("id", $persediaan)
            ->with("barang")
            ->get()
            ->sum(function ($item) {
                return ($item->saldo_persatuan ?? 0) *
                    ($item->barang->harga_beli_per_unit ?? 0);
            });

        // === 5. Hitung Laba Rugi dari Buku Besar ===

        // Pendapatan: penjualan tunai + piutang + bunga bank + lain-lain
        $totalPendapatan = BukuBesarPendapatan::where("user_id", $userId)->sum(
            "uang_diterima",
        );

        // Pengeluaran: semua pengeluaran total (HPP + operasional + bunga + gaji + lain-lain)
        $totalPengeluaran = BukuBesarPengeluaran::where(
            "user_id",
            $userId,
        )->sum("jumlah_pengeluaran");

        // Laba kotor & bersih
        $labaKotor = $totalPendapatan - $totalPengeluaran;
        $pajak = $labaKotor > 0 ? $labaKotor * 0.15 : 0;
        $labaBersih = $labaKotor - $pajak;

        // === 6. Hitung total Aktiva & Pasiva ===
        $totalAktiva = $saldoKas + $saldoPiutang + $nilaiPersediaan;
        $totalPasiva = $saldoHutang + $labaBersih;

        return view("keuangan.neraca-akhir.index", [
            "saldoKas" => $saldoKas,
            "saldoPiutang" => $saldoPiutang,
            "saldoHutang" => $saldoHutang,
            "nilaiPersediaan" => $nilaiPersediaan,
            "totalPendapatan" => $totalPendapatan,
            "totalPengeluaran" => $totalPengeluaran,
            "labaKotor" => $labaKotor,
            "pajak" => $pajak,
            "labaBersih" => $labaBersih,
            "totalAktiva" => $totalAktiva,
            "totalPasiva" => $totalPasiva,
        ]);
    }
}
