<?php

namespace App\Services;

use App\Models\{
    BukuBesarKas,
    BukuBesarPiutang,
    BukuBesarHutang,
    BukuBesarPendapatan,
    BukuBesarPengeluaran,
    KartuGudang,
    NeracaAwal,
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KeuanganService
{
    public function hitungLabaRugi($startDate = null, $endDate = null)
    {
        $userId = Auth::id();

        $startDate = $startDate ?? now()->startOfYear()->format("Y-m-d");
        $endDate = $endDate ?? now()->endOfYear()->format("Y-m-d");

        $filter = function ($query) use ($startDate, $endDate) {
            return $query->whereBetween("tanggal", [$startDate, $endDate]);
        };

        // === PENJUALAN ===
        $penjualanKredit = BukuBesarPiutang::where("user_id", $userId)
            ->where($filter)
            ->sum("debit");

        $penjualanTunai = BukuBesarPendapatan::where("user_id", $userId)
            ->where($filter)
            ->sum("penjualan_tunai");

        $bungaPenjualan =
            BukuBesarPendapatan::where("user_id", $userId)
                ->where($filter)
                ->sum("bunga_bank") ?? 0;

        $totalPenjualan = $penjualanKredit + $penjualanTunai + $bungaPenjualan;

        $returPenjualan = BukuBesarPiutang::where("user_id", $userId)
            ->where($filter)
            ->where(function ($q) {
                $q->where("uraian", "like", "%Retur%")->orWhere(
                    "uraian",
                    "like",
                    "%memo%",
                );
            })
            ->sum("kredit"); // ✅ harus kredit, bukan debit

        $potonganPenjualan =
            BukuBesarPendapatan::where("user_id", $userId)
                ->where($filter)
                ->sum("potongan_pembelian") ?? 0;

        $penjualanBersih =
            $totalPenjualan - ($returPenjualan + $potonganPenjualan);

        // === PERSEDIAAN AWAL ===
        $persediaanAwal =
            NeracaAwal::where("user_id", $userId)
                ->where("created_at", "<=", $endDate)
                ->value("total_persediaan") ?? 0;

        // === PEMBELIAN ===
        $pembelianKredit = BukuBesarHutang::where("user_id", $userId)
            ->where($filter)
            ->sum("kredit");

        $pembelianTunai = BukuBesarPengeluaran::where("user_id", $userId)
            ->where($filter)
            ->sum("jumlah_pembelian_tunai");

        $returPembelian = BukuBesarHutang::where("user_id", $userId)
            ->where($filter)
            ->where(function ($q) {
                $q->where("uraian", "like", "%Retur%")->orWhere(
                    "uraian",
                    "like",
                    "%memo%",
                );
            })
            ->sum("debit"); // ✅ harus debit, bukan kredit

        $potonganPembelian =
            BukuBesarPengeluaran::where("user_id", $userId)
                ->where($filter)
                ->sum("potongan_pembelian") ?? 0;

        $pembelianBersih =
            $pembelianKredit +
            $pembelianTunai -
            $returPembelian -
            $potonganPembelian;

        // === PERSEDIAAN AKHIR ===
        $persediaanAkhir = KartuGudang::with("barang")
            ->where("user_id", $userId)
            ->where("tanggal", "<=", $endDate)
            ->get()
            ->groupBy("barang_id")
            ->map(function ($group) {
                $last = $group->sortByDesc("tanggal")->first();
                $hargaPerUnit = $last->barang->harga_beli_per_unit ?? 0;
                $saldoTerakhir = $last->saldo_persatuan ?? 0;
                return $saldoTerakhir * $hargaPerUnit;
            })
            ->sum();

        // === HPP & LABA ===
        $barangTersedia = $persediaanAwal + $pembelianBersih;
        $hpp = $barangTersedia - $persediaanAkhir;
        $labaKotor = $penjualanBersih - $hpp;

        // === BIAYA OPERASIONAL ===
        $biayaOperasional = BukuBesarPengeluaran::where("user_id", $userId)
            ->where($filter)
            ->where(function ($q) {
                $q->where("uraian", "like", "%gaji%")
                    ->orWhere("uraian", "like", "%biaya%")
                    ->orWhere("uraian", "like", "%asuransi%")
                    ->orWhere("uraian", "like", "%pajak%")
                    ->orWhere("uraian", "like", "%air%")
                    ->orWhere("uraian", "like", "%listrik%")
                    ->orWhere("uraian", "like", "%operasional%");
            })
            ->sum("jumlah_pengeluaran");

        $labaOperasional = $labaKotor - $biayaOperasional;

        $pendapatanLain = BukuBesarPendapatan::where("user_id", $userId)
            ->where($filter)
            ->sum("lain_lain");

        $biayaAdministrasiBank = BukuBesarPengeluaran::where("user_id", $userId)
            ->where($filter)
            ->sum("bunga_bank");

        $labaSebelumPajak =
            $labaOperasional + $pendapatanLain - $biayaAdministrasiBank;

        $pajak = $labaSebelumPajak * 0.15;
        $labaSetelahPajak = $labaSebelumPajak - $pajak;

        return compact(
            "penjualanKredit",
            "penjualanTunai",
            "bungaPenjualan",
            "totalPenjualan",
            "returPenjualan",
            "potonganPenjualan",
            "penjualanBersih",
            "persediaanAwal",
            "pembelianKredit",
            "pembelianTunai",
            "returPembelian",
            "potonganPembelian",
            "pembelianBersih",
            "persediaanAkhir",
            "hpp",
            "labaKotor",
            "biayaOperasional",
            "labaOperasional",
            "pendapatanLain",
            "biayaAdministrasiBank",
            "labaSebelumPajak",
            "pajak",
            "labaSetelahPajak",
        );
    }
}
