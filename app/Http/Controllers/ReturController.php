<?php

namespace App\Http\Controllers;

use App\Models\BukuBesarHutang;
use App\Models\BukuBesarPendapatan;
use App\Models\BukuBesarPiutang;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                $saldoBaru += $returJumlah;
            } else {
                $saldoBaru -= $returJumlah;
            }
            // Jika tunai_kembali → piutang tetap berkurang (sudah dilunasi), hanya kas keluar

            BukuBesarPiutang::create([
                "kode" => $kodePiutang,
                "pelanggan_id" => $pelangganId,
                "uraian" => "Retur: {$keterangan}",
                "tanggal" => $request->tanggal,
                "debit" =>
                    $request->retur_penanganan === "kurangi_piutang"
                        ? $returJumlah
                        : 0,
                "kredit" =>
                    $request->retur_penanganan === "tunai_kembali"
                        ? $returJumlah
                        : 0,
                "saldo" => $saldoBaru,
                "buku_besar_pendapatan_id" => $pendapatan->id,
                "user_id" => auth()->id(),
            ]);

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

            // Bersihkan format rupiah
            $returJumlah = (float) preg_replace(
                "/[^0-9]/",
                "",
                $request->retur_jumlah,
            );
            $kodeHutang = $request->hutang_aktif;
            $pelangganId = $request->nama_pelanggan;
            $keterangan =
                $request->retur_keterangan ?: "Retur pengeluaran ke kreditur";

            // === Ambil saldo terakhir dari BukuBesarHutang ===
            $saldoTerakhir = BukuBesarHutang::where("kode", $kodeHutang)
                ->where("pelanggan_id", $pelangganId)
                ->where("user_id", auth()->id())
                ->orderByDesc("id")
                ->first();

            if (!$saldoTerakhir) {
                throw new \Exception("Data hutang tidak ditemukan.");
            }

            if ($saldoTerakhir->saldo < $returJumlah) {
                throw new \Exception(
                    "Jumlah retur melebihi saldo hutang aktif.",
                );
            }

            // === 1. Catat di Buku Besar Pendapatan (sebagai retur pengeluaran) ===
            $pendapatan = BukuBesarPendapatan::create([
                "tanggal" => $request->tanggal,
                "uraian" => "Retur Hutang - {$keterangan}",
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

            // === 2. Update Buku Besar Hutang ===
            $debit = 0;
            $kredit = 0;
            $saldoBaru = $saldoTerakhir->saldo;

            if ($request->retur_penanganan === "kurangi_hutang") {
                // Kembalikan ke hutang (tambah hutang)
                $debit = $returJumlah;
                $saldoBaru += $returJumlah;
            } else {
                // Tunai kembali → hutang berkurang, kas keluar
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
                "user_id" => auth()->id(),
            ]);

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
