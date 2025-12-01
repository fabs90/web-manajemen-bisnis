<?php

namespace App\Http\Controllers;

use App\Models\BukuBesarKas;
use App\Models\KasKecil;
use App\Models\NeracaAwal;
use App\Models\PengisianKasKecilLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ManajemenKasKecilController extends Controller
{
    public function index()
    {
        $kasKecilLogs = PengisianKasKecilLog::where(
            "user_id",
            auth()->id(),
        )->get();

        $saldoAkhir = KasKecil::where("user_id", auth()->id())->latest()->value("saldo_akhir");
        return view("keuangan.kas-kecil.index", compact("kasKecilLogs", "saldoAkhir"));
    }

    public function create()
    {
        return view("keuangan.kas-kecil.create");
    }

    public function store(Request $request)
    {
        // find the latest buku besar kas saldo
        $bukuBesarKasOld = BukuBesarKas::where("user_id", auth()->id())
            ->latest()
            ->first();
        $kodeTransaksi = Str::uuid();
        DB::beginTransaction();
        try {
            $neracaAwalBefore = NeracaAwal::where(
                "user_id",
                auth()->id(),
            )->first();

            if (!$neracaAwalBefore) {
                throw new \Exception("Data Neraca Awal belum ditemukan.
            Silakan buat Neraca Awal terlebih dahulu sebelum menambahkan pendapatan.");
            }
            // kurangin nilai saldo
            $latestSaldo = $bukuBesarKasOld->saldo - $request->jumlah;
            $bukuBesarKas = BukuBesarKas::create([
                "kode" => $kodeTransaksi,
                "tanggal" => now(),
                "uraian" => "Menambahkan Kas Kecil: " . $request->uraian,
                "debit" => 0,
                "kredit" => $request->jumlah,
                "saldo" => $latestSaldo,
                "neraca_awal_id" => $neracaAwalBefore->id,
                "user_id" => auth()->id(),
            ]);
            $latestSaldoKasKecil = KasKecil::where("user_id", auth()->id())
                ->latest()
                ->first();
            $saldoAkhir =
                ($latestSaldoKasKecil->saldo_akhir ?? 0) + $request->jumlah;
            // Tambah ke kas kecil
            $kasKecil = KasKecil::create([
                "user_id" => auth()->id(),
                "tanggal" => now(),
                "nomor_referensi" => $kodeTransaksi,
                "penerimaan" => $request->jumlah,
                "pengeluaran" => 0,
                "saldo_akhir" => $saldoAkhir,
            ]);
            // Tambah ke logs
            PengisianKasKecilLog::create([
                "buku_besar_kas_id" => $bukuBesarKas->id,
                "kas_kecil_id" => $kasKecil->id,
                "uraian" => "Pengisian Kas Kecil - " . now()->format("d/m/Y"),
                "jumlah" => $request->jumlah,
                "tanggal_transaksi" => now(),
                "user_id" => auth()->id(),
            ]);
            DB::commit();
            return redirect()
                ->route("keuangan.pengeluaran-kas-kecil.index")
                ->with("success", "Kas kecil berhasil ditambahkan");
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Error creating kas kecil: " . $e->getMessage());
            return redirect()
                ->back()
                ->with("error", "Failed to create kas kecil");
        }
    }
}
