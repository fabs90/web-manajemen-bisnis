<?php

namespace App\Services;

use App\Models\{
    BukuBesarPiutang,
    BukuBesarHutang,
    BukuBesarPendapatan,
    BukuBesarPengeluaran,
    KartuGudang,
    NeracaAwal,
};
use Illuminate\Support\Facades\{Auth, DB};

class KeuanganService
{
    private const TAX_RATE = 0.15;

    public function hitungLabaRugi($startDate = null, $endDate = null)
    {
        $userId = Auth::id();
        $startDate = $startDate ?? now()->startOfYear()->format("Y-m-d");
        $endDate = $endDate ?? now()->endOfYear()->format("Y-m-d");
        $dateRange = [$startDate, $endDate];

        // === GATHER METRICS ===
        $sales = $this->getSalesMetrics($userId, $dateRange);
        $purchases = $this->getPurchaseMetrics($userId, $dateRange);
        $inventory = $this->getInventoryMetrics($userId, $endDate);
        $operating = $this->getOperatingMetrics($userId, $dateRange);

        // === CALCULATE SUMMARY ===
        $penjualanBersih =
            $sales["totalPenjualan"] -
            $sales["returPenjualan"] +
            $sales["potonganPenjualan"];

        $pembelianBersih =
            $purchases["pembelianKredit"] +
            $purchases["pembelianTunai"] -
            $purchases["returPembelian"] -
            $purchases["potonganPembelian"];

        $barangTersedia = $inventory["persediaanAwal"] + $pembelianBersih;
        $hpp = $barangTersedia - $inventory["persediaanAkhir"];
        $labaKotor = $penjualanBersih - $hpp;

        $labaOperasional = $labaKotor - $operating["biayaOperasional"];

        $labaSebelumPajak =
            $labaOperasional +
            $operating["pendapatanLain"] -
            $operating["biayaAdministrasiBank"];

        $pajak = $labaSebelumPajak * self::TAX_RATE;
        $labaSetelahPajak = $labaSebelumPajak - $pajak;

        return array_merge($sales, $purchases, $inventory, $operating, [
            "penjualanBersih" => $penjualanBersih,
            "pembelianBersih" => $pembelianBersih,
            "hpp" => $hpp,
            "labaKotor" => $labaKotor,
            "labaOperasional" => $labaOperasional,
            "labaSebelumPajak" => $labaSebelumPajak,
            "pajak" => $pajak,
            "labaSetelahPajak" => $labaSetelahPajak,
        ]);
    }

    private function getSalesMetrics(int $userId, array $dateRange): array
    {
        $penjualanKredit = BukuBesarPiutang::where("user_id", $userId)
            ->whereBetween("tanggal", $dateRange)
            ->sum("debit");

        $penjualanTunai = BukuBesarPendapatan::where("user_id", $userId)
            ->whereBetween("tanggal", $dateRange)
            ->sum("penjualan_tunai");

        $bungaPenjualan =
            BukuBesarPendapatan::where("user_id", $userId)
                ->whereBetween("tanggal", $dateRange)
                ->sum("bunga_bank") ?? 0;

        $totalPenjualan = $penjualanKredit + $penjualanTunai + $bungaPenjualan;

        $returPenjualan = BukuBesarPiutang::where("user_id", $userId)
            ->whereBetween("tanggal", $dateRange)
            ->where(function ($q) {
                $q->where("uraian", "like", "%Retur%")->orWhere(
                    "uraian",
                    "like",
                    "%memo%",
                );
            })
            ->sum("kredit");

        $potonganPenjualan =
            BukuBesarPendapatan::where("user_id", $userId)
                ->whereBetween("tanggal", $dateRange)
                ->sum("potongan_pembelian") ?? 0;

        return compact(
            "penjualanKredit",
            "penjualanTunai",
            "bungaPenjualan",
            "totalPenjualan",
            "returPenjualan",
            "potonganPenjualan",
        );
    }

    private function getPurchaseMetrics(int $userId, array $dateRange): array
    {
        $pembelianKredit =
            BukuBesarHutang::where("user_id", $userId)
                ->whereBetween("tanggal", $dateRange)
                ->where("uraian", "not like", "%saldo awal%")
                ->sum("kredit") ?? 0;

        $pembelianTunai =
            BukuBesarPengeluaran::where("user_id", $userId)
                ->whereBetween("tanggal", $dateRange)
                ->sum("jumlah_pembelian_tunai") ?? 0;

        $returPembelian = BukuBesarHutang::where("user_id", $userId)
            ->whereBetween("tanggal", $dateRange)
            ->where(function ($q) {
                $q->where("uraian", "like", "%Retur%")->orWhere(
                    "uraian",
                    "like",
                    "%memo%",
                );
            })
            ->sum("debit");

        $potonganPembelian =
            BukuBesarPengeluaran::where("user_id", $userId)
                ->whereBetween("tanggal", $dateRange)
                ->sum("potongan_pembelian") ?? 0;

        return compact(
            "pembelianKredit",
            "pembelianTunai",
            "returPembelian",
            "potonganPembelian",
        );
    }

    private function getInventoryMetrics(int $userId, string $endDate): array
    {
        $persediaanAwal =
            NeracaAwal::where("user_id", $userId)
                ->where("created_at", "<=", $endDate)
                ->value("total_persediaan") ?? 0;

        $persediaanAkhir = KartuGudang::where("user_id", $userId)
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

        return compact("persediaanAwal", "persediaanAkhir");
    }

    private function getOperatingMetrics(int $userId, array $dateRange): array
    {
        $biayaOperasional = BukuBesarPengeluaran::where("user_id", $userId)
            ->whereBetween("tanggal", $dateRange)
            ->sum("lain_lain");

        $pendapatanLain =
            BukuBesarPendapatan::where("user_id", $userId)
                ->whereBetween("tanggal", $dateRange)
                ->sum("lain_lain") ?? 0;

        $biayaAdministrasiBank =
            BukuBesarPengeluaran::where("user_id", $userId)
                ->whereBetween("tanggal", $dateRange)
                ->sum("admin_bank") ?? 0;

        return compact(
            "biayaOperasional",
            "pendapatanLain",
            "biayaAdministrasiBank",
        );
    }
}
