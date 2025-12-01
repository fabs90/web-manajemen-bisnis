<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BukuBesarKas;
use App\Models\BukuBesarPendapatan;
use App\Models\BukuBesarPengeluaran;
use App\Models\KartuGudang;
use App\Models\KasirTransactionLog;
use App\Models\NeracaAwal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KasirController extends Controller
{
    public function index()
    {
        $kasirTransactions = KasirTransactionLog::where("user_id", auth()->id())
            ->orderBy("tanggal_transaksi", "desc")
            ->get();
        return view("keuangan.kasir.index", compact("kasirTransactions"));
    }

    public function create()
    {
        // Ambil semua barang
        $barang = Barang::where("user_id", auth()->id())
            ->orderBy("nama")
            ->get();
        // Pengeluaran Tunai
        $pengeluaranTunai = BukuBesarPengeluaran::where("user_id", auth()->id())
            ->orderBy("tanggal", "desc")
            ->first();
        return view(
            "keuangan.kasir.create",
            compact("barang", "pengeluaranTunai"),
        );
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $kodeTransaksi = Str::uuid();
            $bukuBesarPendapatan = BukuBesarPendapatan::create([
                "tanggal" => now(),
                "uraian" =>
                    "Pendapatan Tunai: " .
                    Carbon::now("Asia/Jakarta")->format("d/m/Y H:i"),
                "potongan_pembelian" => $request->potongan_pembelian
                    ? array_sum($request->potongan_pembelian)
                    : 0,
                "piutang_dagang" => 0,
                "penjualan_tunai" => $request->grand_total ?? 0,
                "lain_lain" => 0,
                "uang_diterima" => $request->grand_total ?? 0,
                "bunga_bank" => 0,
                "user_id" => auth()->id(),
            ]);

            $neracaAwalBefore = NeracaAwal::where(
                "user_id",
                auth()->id(),
            )->first();

            if (!$neracaAwalBefore) {
                throw new \Exception("Data Neraca Awal belum ditemukan.
            Silakan buat Neraca Awal terlebih dahulu sebelum menambahkan pendapatan.");
            }

            $bukuBesarKasBefore = BukuBesarKas::where("user_id", auth()->id())
                ->latest()
                ->first();

            $saldoBaru =
                ($bukuBesarKasBefore->saldo ?? 0) + $request->grand_total;

            $bukuBesarKas = BukuBesarKas::create([
                "kode" => $kodeTransaksi,
                "uraian" =>
                    "Pendapatan Tunai: " .
                    Carbon::now("Asia/Jakarta")->format("d/m/Y H:i"),
                "tanggal" => $bukuBesarPendapatan->tanggal,
                "debit" => $request->grand_total,
                "kredit" => 0,
                "saldo" => $saldoBaru,
                "neraca_awal_id" => $neracaAwalBefore->id,
                "neraca_akhir_id" => null,
                "user_id" => auth()->id(),
            ]);

            // Insert into logs
            KasirTransactionLog::create([
                "pendapatan_id" => $bukuBesarPendapatan->id,
                "buku_besar_kas_id" => $bukuBesarKas->id,
                "uraian" =>
                    "Pendapatan Tunai: " .
                    Carbon::now("Asia/Jakarta")->format("d/m/Y H:i"),
                "tanggal_transaksi" => $bukuBesarPendapatan->tanggal,
                "jumlah" => $request->grand_total,
                "user_id" => auth()->id(),
            ]);

            // Insert barang
            if ($request->filled("id_barang_terjual")) {
                foreach ($request->id_barang_terjual as $index => $barangId) {
                    if (!$barangId) {
                        continue;
                    }

                    $detailBarang = Barang::where("id", $barangId)->first();
                    if (!$detailBarang) {
                        throw new \Exception(
                            "Barang dengan ID {$barangId} tidak ditemukan.",
                        );
                    }

                    $barangItem = KartuGudang::where("barang_id", $barangId)
                        ->latest()
                        ->first();

                    if (!$barangItem) {
                        throw new \Exception(
                            "Kartu gudang untuk barang ID {$barangId} tidak ditemukan.",
                        );
                    }

                    $saldoSatuanAwal = $barangItem->saldo_persatuan;
                    $saldoKemasanAwal = $barangItem->saldo_perkemasan;
                    $unitPerKemasan = $detailBarang->jumlah_unit_per_kemasan;
                    $jumlahDijual = $request->jumlah_barang_dijual[$index] ?? 0;
                    if ($saldoSatuanAwal < $jumlahDijual) {
                        throw new \Exception(
                            "Saldo barang '{$detailBarang->nama}' tidak mencukupi. Tersedia: {$saldoSatuanAwal}, Dibutuhkan: {$jumlahDijual}",
                        );
                    }

                    $saldoPerKemasanBaru =
                        $saldoKemasanAwal -
                        ceil($jumlahDijual / $unitPerKemasan);

                    $saldoSatuanBaru = $saldoSatuanAwal - $jumlahDijual;

                    KartuGudang::create([
                        "barang_id" => $barangId,
                        "tanggal" => $bukuBesarPendapatan->tanggal,
                        "diterima" => 0,
                        "dikeluarkan" => $jumlahDijual,
                        "uraian" =>
                            "Pendapatan Kasir Tunai: " .
                            $detailBarang->nama .
                            " - " .
                            Carbon::now("Asia/Jakarta")->format("d/m/Y H:i"),
                        "saldo_persatuan" => $saldoSatuanBaru,
                        "saldo_perkemasan" => $saldoPerKemasanBaru,
                        "buku_besar_pendapatan_id" => $bukuBesarPendapatan->id,
                        "user_id" => auth()->id(),
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error occurred while processing request", [
                "error" => $e->getMessage(),
            ]);
            return redirect()
                ->back()
                ->with("error", "Terjadi Error: " . $e->getMessage());
        }
        return redirect()->back()->with("success", "Transaksi berhasil");
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // delete logs
            $logs = KasirTransactionLog::where("id", $id)->first();
            // delete pendapatan
            $bukuBesarPendapatan = BukuBesarPendapatan::where(
                "id",
                $logs->bukuBesarPendapatan->id,
            )->first();
            // buku besar kas
            $bukuBesarKas = BukuBesarKas::where(
                "id",
                $logs->bukuBesarKas->id,
            )->first();
            $bukuBesarKas->delete();
            $bukuBesarPendapatan->delete();
            $logs->delete();
            DB::commit();
            return redirect()
                ->back()
                ->with("success", "Hapus transaksi berhasil");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error occurred while processing request", [
                "error" => $e->getMessage(),
            ]);
            return redirect()
                ->back()
                ->with("error", "Terjadi Error: " . $e->getMessage());
        }
    }
}