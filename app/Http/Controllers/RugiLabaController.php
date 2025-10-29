<?php

namespace App\Http\Controllers;

use App\Models\BukuBesarHutang;
use App\Models\BukuBesarPendapatan;
use App\Models\BukuBesarPengeluaran;
use App\Models\BukuBesarPiutang;
use App\Models\KartuGudang;
use App\Models\NeracaAwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class RugiLabaController extends Controller
{
    public function index(Request $request)
    {
        // === Ambil tanggal dari request, default: tahun berjalan ===
        $startDate = $request->input(
            "start_date",
            now()->startOfYear()->format("Y-m-d"),
        );
        $endDate = $request->input(
            "end_date",
            now()->endOfYear()->format("Y-m-d"),
        );

        $userId = Auth::id();

        // Helper: query dengan filter tanggal
        $filter = function ($query) use ($startDate, $endDate) {
            return $query->whereBetween("tanggal", [$startDate, $endDate]);
        };

        // 1.A Penjualan Kredit
        $penjualanKredit = BukuBesarPiutang::where("user_id", $userId)
            ->where($filter)
            ->sum("debit");

        // 1.B Penjualan Tunai
        $penjualanTunai = BukuBesarPendapatan::where("user_id", $userId)
            ->where($filter)
            ->sum("penjualan_tunai");

        // Bunga Penjualan
        $bungaPenjualan =
            BukuBesarPendapatan::where("user_id", $userId)
                ->where($filter)
                ->sum("bunga_bank") ?? 0;

        $totalPenjualan = $penjualanKredit + $penjualanTunai + $bungaPenjualan;

        // 2. Retur Penjualan
        $returPenjualan =
            BukuBesarPiutang::where("user_id", $userId)
                ->where($filter)
                ->where(function ($q) {
                    $q->where("uraian", "like", "%retur%")->orWhere(
                        "uraian",
                        "like",
                        "%memo%",
                    );
                })
                ->sum("kredit") ?? 0;

        // 3. Potongan Penjualan
        $potonganPenjualan =
            BukuBesarPendapatan::where("user_id", $userId)
                ->where($filter)
                ->sum("potongan_pembelian") ?? 0;

        // 4. Penjualan Bersih
        $penjualanBersih =
            $totalPenjualan - ($returPenjualan + $potonganPenjualan);

        // 5. Persediaan Awal → Ambil dari Neraca Awal sebelum periode
        $persediaanBarangDaganganAwal =
            NeracaAwal::where("user_id", $userId)
                ->where("created_at", "<", $startDate)
                ->latest("created_at")
                ->value("total_persediaan") ?? 0;

        // 6.A Pembelian Kredit
        $pembelianSecaraKredit = BukuBesarHutang::where("user_id", $userId)
            ->where($filter)
            ->sum("kredit");

        // 6.B Pembelian Tunai
        $pembelianSecaraTunai = BukuBesarPengeluaran::where("user_id", $userId)
            ->where($filter)
            ->sum("jumlah_pembelian_tunai");

        // 7. Retur Pembelian
        $returPembelian =
            BukuBesarHutang::where("user_id", $userId)
                ->where($filter)
                ->where(function ($q) {
                    $q->where("uraian", "like", "%retur%")->orWhere(
                        "uraian",
                        "like",
                        "%memo%",
                    );
                })
                ->sum("debit") ?? 0;

        // 8. Potongan Pembelian
        $potonganPembelian =
            BukuBesarPengeluaran::where("user_id", $userId)
                ->where($filter)
                ->sum("potongan_pembelian") ?? 0;

        // 9. Pembelian Bersih
        $pembelianBersih =
            $pembelianSecaraKredit +
            $pembelianSecaraTunai -
            $returPembelian -
            $potonganPembelian;

        // 10. Barang Tersedia untuk Dijual
        $barangTersediaDijual =
            $persediaanBarangDaganganAwal + $pembelianBersih;

        // 11. Persediaan Akhir → dari Kartu Gudang per akhir periode
        $persediaanBarangDagangan = KartuGudang::with("barang")
            ->where("user_id", $userId)
            ->where("tanggal", "<=", $endDate)
            ->get()
            ->groupBy("barang_id")
            ->map(function ($group) {
                $last = $group->sortByDesc("tanggal")->first();
                return ($last->saldo_persatuan ?? 0) *
                    ($last->barang->harga_beli_per_unit ?? 0);
            })
            ->sum();

        // HPP
        $hpp = $barangTersediaDijual - $persediaanBarangDagangan;

        // Laba Kotor
        $labaKotor = $penjualanBersih - $hpp;

        // Biaya Operasional
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

        // Laba Operasional
        $labaOperasional = $labaKotor - $biayaOperasional;

        // Pendapatan Lain
        $pendapatanLain = BukuBesarPendapatan::where("user_id", $userId)
            ->where($filter)
            ->sum("lain_lain");

        // Biaya Admin Bank
        $biayaAdministrasiBank = BukuBesarPengeluaran::where("user_id", $userId)
            ->where($filter)
            ->sum("bunga_bank");

        // Laba Sebelum Pajak
        $labaSebelumPajak =
            $labaOperasional + $pendapatanLain - $biayaAdministrasiBank;

        // Pajak 15%
        $pajak = $labaSebelumPajak > 0 ? $labaSebelumPajak * 0.15 : 0;

        // Laba Setelah Pajak
        $labaSetelahPajak = $labaSebelumPajak - $pajak;

        return view(
            "keuangan.rugi-laba.list",
            compact(
                "penjualanKredit",
                "penjualanTunai",
                "bungaPenjualan",
                "totalPenjualan",
                "returPenjualan",
                "potonganPenjualan",
                "penjualanBersih",
                "persediaanBarangDaganganAwal",
                "pembelianSecaraKredit",
                "pembelianSecaraTunai",
                "returPembelian",
                "potonganPembelian",
                "pembelianBersih",
                "barangTersediaDijual",
                "persediaanBarangDagangan",
                "hpp",
                "labaKotor",
                "biayaOperasional",
                "labaOperasional",
                "pendapatanLain",
                "biayaAdministrasiBank",
                "labaSebelumPajak",
                "pajak",
                "labaSetelahPajak",
                "startDate",
                "endDate",
            ),
        );
    }

    public function exportToPdf(Request $request)
    {
        // === Ambil parameter tanggal sama seperti di index() ===
        $startDate = $request->input(
            "start_date",
            now()->startOfYear()->format("Y-m-d"),
        );
        $endDate = $request->input(
            "end_date",
            now()->endOfYear()->format("Y-m-d"),
        );

        // === Jalankan ulang perhitungan laba rugi dari index() ===
        // Supaya hasil di PDF sama persis dengan di tampilan web
        $userId = Auth::id();

        $filter = function ($query) use ($startDate, $endDate) {
            return $query->whereBetween("tanggal", [$startDate, $endDate]);
        };

        // === Sama persis seperti di index() ===
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

        $returPenjualan =
            BukuBesarPiutang::where("user_id", $userId)
                ->where($filter)
                ->where(function ($q) {
                    $q->where("uraian", "like", "%retur%")->orWhere(
                        "uraian",
                        "like",
                        "%memo%",
                    );
                })
                ->sum("kredit") ?? 0;

        $potonganPenjualan =
            BukuBesarPendapatan::where("user_id", $userId)
                ->where($filter)
                ->sum("potongan_pembelian") ?? 0;

        $penjualanBersih =
            $totalPenjualan - ($returPenjualan + $potonganPenjualan);

        $persediaanBarangDaganganAwal =
            NeracaAwal::where("user_id", $userId)
                ->where("created_at", "<", $startDate)
                ->latest("created_at")
                ->value("total_persediaan") ?? 0;

        $pembelianSecaraKredit = BukuBesarHutang::where("user_id", $userId)
            ->where($filter)
            ->sum("kredit");

        $pembelianSecaraTunai = BukuBesarPengeluaran::where("user_id", $userId)
            ->where($filter)
            ->sum("jumlah_pembelian_tunai");

        $returPembelian =
            BukuBesarHutang::where("user_id", $userId)
                ->where($filter)
                ->where(function ($q) {
                    $q->where("uraian", "like", "%retur%")->orWhere(
                        "uraian",
                        "like",
                        "%memo%",
                    );
                })
                ->sum("debit") ?? 0;

        $potonganPembelian =
            BukuBesarPengeluaran::where("user_id", $userId)
                ->where($filter)
                ->sum("potongan_pembelian") ?? 0;

        $pembelianBersih =
            $pembelianSecaraKredit +
            $pembelianSecaraTunai -
            $returPembelian -
            $potonganPembelian;

        $barangTersediaDijual =
            $persediaanBarangDaganganAwal + $pembelianBersih;

        $persediaanBarangDagangan = KartuGudang::with("barang")
            ->where("user_id", $userId)
            ->where("tanggal", "<=", $endDate)
            ->get()
            ->groupBy("barang_id")
            ->map(function ($group) {
                $last = $group->sortByDesc("tanggal")->first();
                return ($last->saldo_persatuan ?? 0) *
                    ($last->barang->harga_beli_per_unit ?? 0);
            })
            ->sum();

        $hpp = $barangTersediaDijual - $persediaanBarangDagangan;
        $labaKotor = $penjualanBersih - $hpp;

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
            ->sum("lain_lain");

        $labaOperasional = $labaKotor - $biayaOperasional;

        $pendapatanLain = BukuBesarPendapatan::where("user_id", $userId)
            ->where($filter)
            ->sum("lain_lain");

        $biayaAdministrasiBank = BukuBesarPengeluaran::where("user_id", $userId)
            ->where($filter)
            ->sum("bunga_bank");

        $labaSebelumPajak =
            $labaOperasional + $pendapatanLain - $biayaAdministrasiBank;

        $pajak = $labaSebelumPajak > 0 ? $labaSebelumPajak * 0.15 : 0;
        $labaSetelahPajak = $labaSebelumPajak - $pajak;

        // === Masukkan semua variabel ke dalam array $data ===
        $data = compact(
            "penjualanKredit",
            "penjualanTunai",
            "bungaPenjualan",
            "totalPenjualan",
            "returPenjualan",
            "potonganPenjualan",
            "penjualanBersih",
            "persediaanBarangDaganganAwal",
            "pembelianSecaraKredit",
            "pembelianSecaraTunai",
            "returPembelian",
            "potonganPembelian",
            "pembelianBersih",
            "barangTersediaDijual",
            "persediaanBarangDagangan",
            "hpp",
            "labaKotor",
            "biayaOperasional",
            "labaOperasional",
            "pendapatanLain",
            "biayaAdministrasiBank",
            "labaSebelumPajak",
            "pajak",
            "labaSetelahPajak",
            "startDate",
            "endDate",
        );

        // === Buat PDF dari view laporan yang sama ===
        $pdf = Pdf::loadView("keuangan.rugi-laba.pdf", $data)->setPaper(
            "a4",
            "portrait",
        );

        // === Download PDF ===
        return $pdf->download(
            "laporan-rugi-laba-" . now()->format("Y") . ".pdf",
        );
    }
}
