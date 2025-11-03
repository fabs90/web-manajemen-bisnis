<?php

namespace App\Http\Controllers;

use App\Models\BukuBesarHutang;
use App\Models\BukuBesarKas;
use App\Models\BukuBesarPendapatan;
use App\Models\BukuBesarPiutang;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReturController extends Controller
{
    public function list()
    {
        $returPenjualan = BukuBesarPiutang::where("user_id", auth()->id())
            ->where(function ($query) {
                $query
                    ->where("uraian", "like", "%retur%")
                    ->orWhere("uraian", "like", "%memo%");
            })
            ->get();

        return view("retur-kredit.list", compact("returPenjualan"));
    }

    public function create()
    {
        $debitur = Pelanggan::where("user_id", auth()->id())
            ->where("jenis", "debitur")
            ->orderBy("nama")
            ->get();
        $listPiutang = BukuBesarPiutang::where("user_id", auth()->id())
            ->where("saldo", ">", 0)
            ->whereIn("id", function ($query) {
                $query
                    ->select(DB::raw("MAX(id)"))
                    ->from("buku_besar_piutang")
                    ->where("user_id", auth()->id())
                    ->groupBy("pelanggan_id");
            })
            ->with("pelanggan") // Eager load relasi pelanggan
            ->latest()
            ->get();

        return view("retur-kredit.create", compact("debitur", "listPiutang"));
    }

    public function store(Request $request)
    {
        $request->validate([
            "hutang_aktif" => "required",
            "retur_jumlah" => "required|numeric|min:1",
            "retur_penanganan" => "required|in:kurangi_piutang,tunai_kembali",
            "retur_keterangan" => "nullable|string|max:255",
            "tanggal" => "required|date",
        ]);

        try {
            DB::beginTransaction();

            $returJumlah = (float) str_replace(
                ["Rp ", ".", ","],
                "",
                $request->retur_jumlah,
            );
            $kodePiutang = $request->hutang_aktif;
            $pelangganId = $request->nama_pelanggan;
            $keterangan =
                $request->retur_keterangan ?: "Retur penjualan kredit";

            // Ambil saldo terakhir
            $saldoTerakhir = BukuBesarPiutang::where("kode", $kodePiutang)
                ->latest()
                ->first();
            if (!$saldoTerakhir || $saldoTerakhir->saldo < $returJumlah) {
                throw new \Exception(
                    "Saldo piutang tidak mencukupi untuk retur.",
                );
            }

            // 1. Buat entri pendapatan (negatif untuk retur)
            $pendapatan = BukuBesarPendapatan::create([
                "tanggal" => $request->tanggal,
                "uraian" => "Retur Kredit - {$keterangan}",
                "potongan_pembelian" => 0,
                "piutang_dagang" => 0,
                "penjualan_tunai" => 0,
                "lain_lain" => 0,
                "uang_diterima" =>
                    $request->retur_penanganan === "tunai_kembali"
                        ? -$returJumlah
                        : 0,
                "bunga_bank" => 0,
                "user_id" => auth()->id(),
            ]);

            // 2. Catat di buku piutang
            $saldoBaru = $saldoTerakhir->saldo;

            if ($request->retur_penanganan === "kurangi_piutang") {
                // Tambah kembali ke piutang
                $saldoBaru -= $returJumlah;
            } else {
                $saldoBaru -= $returJumlah;
            }

            BukuBesarPiutang::create([
                "kode" => $kodePiutang,
                "pelanggan_id" => $pelangganId,
                "uraian" => "Retur: {$keterangan}",
                "tanggal" => $request->tanggal,
                "debit" => 0,
                "kredit" => $returJumlah,
                "saldo" => $saldoBaru,
                "buku_besar_pendapatan_id" => $pendapatan->id,
                "user_id" => auth()->id(),
            ]);

            if ($request->retur_penanganan === "tunai_kembali") {
                $kasLama = BukuBesarKas::latest()->first();
                $saldoKasBaru = $kasLama->saldo - $returJumlah;

                BukuBesarKas::create([
                    "kode" => Str::uuid(),
                    "uraian" => "Pengembalian tunai retur piutang: {$keterangan}",
                    "tanggal" => $request->tanggal,
                    "debit" => 0,
                    "kredit" => $returJumlah,
                    "saldo" => $saldoKasBaru,
                    "neraca_awal_id" => $kasLama->neraca_awal_id,
                    "user_id" => auth()->id(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route("retur.create")
                ->with("success", "Retur berhasil dicatat.");
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function create_pengeluaran()
    {
        $kreditur = Pelanggan::where("user_id", auth()->id())
            ->where("jenis", "kreditur")
            ->orderBy("nama")
            ->get();

        $listHutang = BukuBesarHutang::where("user_id", auth()->id())
            ->where("saldo", ">", 0)
            ->whereIn("id", function ($query) {
                $query
                    ->select(DB::raw("MAX(id)"))
                    ->from("buku_besar_hutang")
                    ->where("user_id", auth()->id())
                    ->groupBy("pelanggan_id");
            })
            ->with("pelanggan") // Eager load relasi pelanggan
            ->latest()
            ->get();
        return view(
            "retur-kredit.create-pengeluaran",
            compact("kreditur", "listHutang"),
        );
    }

    public function store_pengeluaran(Request $request)
    {
        $request->validate([
            "tanggal" => "required|date",
            "nama_pelanggan" => "required|exists:pelanggan,id",
            "hutang_aktif" => "required",
            "retur_jumlah" => "required|numeric|min:1",
            "retur_penanganan" => "required|in:kurangi_hutang,tunai_kembali",
            "retur_keterangan" => "nullable|string|max:255",
        ]);

        try {
            DB::beginTransaction();

            // === Format & variable dasar ===
            $returJumlah = (float) preg_replace(
                "/[^0-9]/",
                "",
                $request->retur_jumlah,
            );
            $kodeHutang = $request->hutang_aktif;
            $pelangganId = $request->nama_pelanggan;
            $keterangan =
                $request->retur_keterangan ?: "Retur pengeluaran ke kreditur";
            $userId = auth()->id();

            // === Ambil saldo hutang terakhir ===
            $saldoTerakhir = BukuBesarHutang::where("kode", $kodeHutang)
                ->where("pelanggan_id", $pelangganId)
                ->where("user_id", $userId)
                ->orderByDesc("id")
                ->first();

            if (!$saldoTerakhir) {
                throw new \Exception("Data hutang tidak ditemukan.");
            }

            if (
                $saldoTerakhir->saldo < $returJumlah &&
                $request->retur_penanganan === "tunai_kembali"
            ) {
                throw new \Exception(
                    "Jumlah retur melebihi saldo hutang aktif.",
                );
            }

            $pendapatan = BukuBesarPendapatan::create([
                "tanggal" => $request->tanggal,
                "uraian" => "Retur Hutang - {$keterangan}",
                "potongan_pembelian" => 0,
                "piutang_dagang" => 0,
                "penjualan_tunai" => 0,
                "lain_lain" => 0,
                "uang_diterima" =>
                    $request->retur_penanganan === "tunai_kembali"
                        ? -$returJumlah // uang keluar, bukan masuk
                        : 0,
                "bunga_bank" => 0,
                "user_id" => $userId,
            ]);

            $debit = 0;
            $kredit = 0;
            $saldoBaru = $saldoTerakhir->saldo;

            if ($request->retur_penanganan === "kurangi_hutang") {
                // Hutang berkurang karena retur
                $kredit = $returJumlah;
                $saldoBaru -= $returJumlah;
            } else {
                // Tunai kembali → hutang berkurang juga
                $kredit = $returJumlah;
                $saldoBaru -= $returJumlah;
            }
            BukuBesarHutang::create([
                "kode" => $kodeHutang,
                "pelanggan_id" => $pelangganId,
                "uraian" => "Retur: {$keterangan}",
                "tanggal" => $request->tanggal,
                "debit" => $debit,
                "kredit" => $kredit,
                "saldo" => $saldoBaru,
                "buku_besar_pendapatan_id" => $pendapatan->id,
                "user_id" => $userId,
            ]);

            if ($request->retur_penanganan === "tunai_kembali") {
                $kasLama = BukuBesarKas::where("user_id", $userId)
                    ->latest("id")
                    ->first();

                $saldoKasLama = $kasLama?->saldo ?? 0;
                $saldoKasBaru = $saldoKasLama + $returJumlah;

                BukuBesarKas::create([
                    "kode" => Str::uuid(),
                    "uraian" => "Pengembalian tunai retur hutang: {$keterangan}",
                    "tanggal" => $request->tanggal,
                    "debit" => 0,
                    "kredit" => $returJumlah,
                    "saldo" => $saldoKasBaru,
                    "neraca_awal_id" => $kasLama->neraca_awal_id,
                    "user_id" => $userId,
                ]);
            }

            DB::commit();

            return redirect()
                ->route("retur.create-pengeluaran")
                ->with("success", "Retur pengeluaran berhasil disimpan.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal simpan retur pengeluaran", [
                "error" => $e->getMessage(),
                "user_id" => auth()->id(),
                "input" => $request->all(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with("error", $e->getMessage() ?: "Gagal menyimpan retur.");
        }
    }
}
