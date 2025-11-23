<?php

namespace App\Http\Controllers;

use App\Models\BukuBesarHutang;
use App\Models\BukuBesarKas;
use App\Models\BukuBesarPendapatan;
use App\Models\BukuBesarPengeluaran;
use App\Models\BukuBesarPiutang;
use App\Models\KartuGudang;
use App\Models\NeracaAwal;
use App\Models\RugiLaba;
use App\Services\KeuanganService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NeracaAkhirController extends Controller
{
    public function index(KeuanganService $keuangan)
    {
        $userId = auth()->id();

        // Ambil data laba rugi biar pajak & laba sama persis
        $dataLabaRugi = $keuangan->hitungLabaRugi();

        // === KAS ===
        $kasNeracaAwal = NeracaAwal::where("user_id", $userId)->first();
        $kasAwal = $kasNeracaAwal->kas_awal ?? 0;

        // === Total Pendapatan ===
        $totalPendapatan = BukuBesarPendapatan::where("user_id", $userId)->sum(
            "uang_diterima",
        );

        // === Total Pengeluaran ===
        $totalPengeluaran = BukuBesarPengeluaran::where(
            "user_id",
            $userId,
        )->sum("jumlah_pengeluaran");

        // === Pendapatan Bunga ===
        $pendapatanBunga = BukuBesarPendapatan::where("user_id", $userId)->sum(
            "bunga_bank",
        );

        // === Biaya Administrasi Bank ===
        $biayaAdminBank = BukuBesarPengeluaran::where("user_id", $userId)->sum(
            "admin_bank",
        ); // meskipun kolomnya salah nama, ini adalah biaya bank

        $saldoKas =
            $kasAwal +
            $totalPendapatan -
            $totalPengeluaran +
            $pendapatanBunga -
            $biayaAdminBank;

        // $kasTerakhir = BukuBesarKas::where("user_id", $userId)
        //     ->latest("created_at")
        //     ->first();
        // $saldoKas = $kasTerakhir->saldo ?? 0;

        // === PIUTANG ===
        $totalPiutang = BukuBesarPiutang::where("user_id", $userId)
            ->select(DB::raw("MAX(id) as id"))
            ->groupBy("pelanggan_id")
            ->pluck("id");
        $saldoPiutang = BukuBesarPiutang::whereIn("id", $totalPiutang)->sum(
            "saldo",
        );

        // === HUTANG ===
        $totalHutang = BukuBesarHutang::where("user_id", $userId)
            ->select(DB::raw("MAX(id) as id"))
            ->groupBy("pelanggan_id")
            ->pluck("id");
        $saldoHutang = BukuBesarHutang::whereIn("id", $totalHutang)->sum(
            "saldo",
        );

        // === PERSEDIAAN BARANG ===
        // Old Ways..
        // $persediaan = KartuGudang::with("barang")
        //     ->where("user_id", $userId)
        //     ->select("barang_id", DB::raw("MAX(id) as id"))
        //     ->groupBy("barang_id")
        //     ->pluck("id");

        // $nilaiPersediaan = KartuGudang::whereIn("id", $persediaan)
        //     ->with("barang")
        //     ->get()
        //     ->sum(
        //         fn($i) => ($i->saldo_persatuan ?? 0) *
        //             ($i->barang->harga_beli_per_unit ?? 0),
        //     );
        $nilaiPersediaan = KartuGudang::where("user_id", $userId)
            ->whereIn("id", function ($query) use ($userId) {
                $query
                    ->select(DB::raw("MAX(id)"))
                    ->from("kartu_gudang")
                    ->where("user_id", $userId)
                    ->groupBy("barang_id");
            })
            ->with("barang:id,harga_beli_per_unit")
            ->get()
            ->sum(
                fn($i) => ($i->saldo_persatuan ?? 0) *
                    ($i->barang->harga_beli_per_unit ?? 0),
            );

        // === AKTIVA TETAP ===
        $neracaAwal = NeracaAwal::where("user_id", $userId)->first();
        $tanah = $neracaAwal->tanah_bangunan ?? 0;
        $kendaraan = $neracaAwal->kendaraan ?? 0;
        $peralatan = $neracaAwal->meubel_peralatan ?? 0;

        // === LABA & PAJAK
        $pajak = $dataLabaRugi["pajak"];
        $labaBersih = $dataLabaRugi["labaSetelahPajak"];

        // === TOTAL ===
        $totalAktiva =
            $saldoKas +
            $saldoPiutang +
            $nilaiPersediaan +
            $tanah +
            $kendaraan +
            $peralatan;

        $modal = $totalAktiva - $saldoHutang - $labaBersih - $pajak;
        $totalPasiva = $saldoHutang + $labaBersih + $pajak + $modal;

        return view(
            "keuangan.neraca-akhir.index",
            compact(
                "saldoKas",
                "saldoPiutang",
                "saldoHutang",
                "nilaiPersediaan",
                "tanah",
                "kendaraan",
                "peralatan",
                "totalAktiva",
                "totalPasiva",
                "pajak",
                "labaBersih",
                "modal",
            ),
        );
    }
}
