<?php

namespace App\Http\Controllers;

use App\Models\BukuBesarHutang;
use App\Models\Barang;
use App\Models\BukuBesarKas;
use App\Models\BukuBesarPiutang;
use App\Models\KartuGudang;
use App\Models\NeracaAwal;
use App\Models\NeracaAkhir;
use App\Models\Pelanggan;
use App\Models\RugiLaba;
use App\Http\Requests\AsetHutangRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class AsetHutangController extends Controller
{
    public function index()
    {
        $neracaAwal = NeracaAwal::where("user_id", auth()->id())->get();

        return view("keuangan.aset-hutang.list", compact("neracaAwal"));
    }

    public function create()
    {
        $user = auth()->user()->name;
        $debitur = Pelanggan::where("user_id", auth()->id())
            ->where("jenis", "debitur")
            ->get();
        $kreditur = Pelanggan::where("user_id", auth()->id())
            ->where("jenis", "kreditur")
            ->get();
        $barang = Barang::where("user_id", auth()->id())->get();
        $kartuGudang = KartuGudang::where("user_id", auth()->id())
            ->latest()
            ->get();

        return view(
            "keuangan.aset-hutang.create",
            compact("user", "debitur", "barang", "kartuGudang", "kreditur"),
        );
    }

    public function store(AsetHutangRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();

        try {
            // Hitung total hutang & piutang
            $totalHutang = collect($validated["hutang"] ?? [])->sum("jumlah");
            $totalPiutang = collect($validated["piutang"] ?? [])->sum("jumlah");

            $totalDebit = collect([
                $validated["kas"],
                $totalPiutang,
                $validated["total_persediaan"] ?? 0,
                $validated["tanah_bangunan"] ?? 0,
                $validated["kendaraan"] ?? 0,
                $validated["meubel_peralatan"] ?? 0,
            ])->sum();

            $modal = $totalDebit - $totalHutang;

            // Simpan ke neraca awal
            $neracaAwal = NeracaAwal::create([
                "user_id" => auth()->id(),
                "kas_awal" => $validated["kas"],
                "total_piutang" => $totalPiutang,
                "total_hutang" => $totalHutang,
                "total_persediaan" => $validated["total_persediaan"] ?? 0,
                "modal_awal" => $modal,
                "tanah_bangunan" => $validated["tanah_bangunan"] ?? 0,
                "kendaraan" => $validated["kendaraan"] ?? 0,
                "meubel_peralatan" => $validated["meubel_peralatan"] ?? 0,
                "total_debit" => $totalDebit,
                "total_kredit" => $modal + $totalHutang,
            ]);

            // Pivot Tabel Neraca-Barang
            if (!empty($validated["barang_ids"])) {
                foreach ($validated["barang_ids"] as $barangId) {
                    DB::table("barang_neraca_awal")->insert([
                        "user_id" => auth()->id(),
                        "neraca_awal_id" => $neracaAwal->id,
                        "barang_id" => $barangId,
                        "created_at" => now(),
                        "updated_at" => now(),
                    ]);
                }
            }

            // Buku besar kas
            $kodeTransaksi = Str::uuid(); // gunakan satu kode sama untuk kas & modal

            BukuBesarKas::create([
                "kode" => $kodeTransaksi,
                "tanggal" => now(),
                "uraian" => "Modal awal disetor",
                "debit" => $validated["kas"],
                "kredit" => 0,
                "saldo" => $validated["kas"],
                "neraca_awal_id" => $neracaAwal->id,
                "user_id" => auth()->id(),
            ]);

            // === Tambahkan buku besar modal (lawannya kas) ===
            \App\Models\BukuBesarModal::create([
                "user_id" => auth()->id(),
                "kode" => $kodeTransaksi, // sama dengan kas agar mudah dilacak
                "tanggal" => now(),
                "uraian" => "Modal awal disetor ke kas",
                "debit" => 0,
                "kredit" => $modal, // modal bertambah
                "saldo" => $modal,
                "neraca_awal_id" => $neracaAwal->id,
            ]);

            // Buku besar piutang
            if (!empty($validated["piutang"])) {
                foreach ($validated["piutang"] as $piutang) {
                    if (!empty($piutang["jumlah"])) {
                        BukuBesarPiutang::create([
                            "user_id" => auth()->id(),
                            "kode" => Str::uuid(),
                            "pelanggan_id" => $piutang["nama"],
                            "tanggal" =>
                                $piutang["jatuh_tempo_piutang"] ?? now(),
                            "uraian" => $piutang["uraian"] ?? "-",
                            "saldo" => $piutang["jumlah"],
                            "debit" => $piutang["jumlah"],
                            "kredit" => 0,
                            "neraca_awal_id" => $neracaAwal->id,
                        ]);
                    }
                }
            }

            // Buku besar hutang
            if (!empty($validated["hutang"])) {
                foreach ($validated["hutang"] as $hutang) {
                    if (!empty($hutang["jumlah"])) {
                        BukuBesarHutang::create([
                            "user_id" => auth()->id(),
                            "kode" => Str::uuid(),
                            "pelanggan_id" => $hutang["nama"],
                            "tanggal" => $hutang["jatuh_tempo_hutang"] ?? now(),
                            "uraian" => $hutang["uraian"] ?? "-",
                            "saldo" => $hutang["jumlah"],
                            "debit" => 0,
                            "kredit" => $hutang["jumlah"],
                            "neraca_awal_id" => $neracaAwal->id,
                        ]);
                    }
                }
            }
            DB::commit();
            return redirect()
                ->route("aset-hutang.create")
                ->with("success", "Data berhasil disimpan.");
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Error saving data: " . $e->getMessage());
            return redirect()
                ->route("aset-hutang.create")
                ->withInput()
                ->withErrors("Gagal menyimpan: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $userId = auth()->id();

        // 🔹 Ambil Neraca Awal beserta relasi barang langsung (eager loading)
        $neracaAwal = \App\Models\NeracaAwal::with(
            "barang:id,nama,harga_beli_per_kemas",
        )
            ->where("user_id", $userId)
            ->findOrFail($id);

        // 🔹 Ambil seluruh buku besar sekaligus (bisa caching di masa depan kalau perlu)
        $tanggal = $neracaAwal->created_at->toDateString();

        $bukuBesarKas = \App\Models\BukuBesarKas::where("user_id", $userId)
            ->whereDate("created_at", $tanggal)
            ->get(["id", "uraian", "saldo"]);

        $bukuBesarPiutang = \App\Models\BukuBesarPiutang::where(
            "user_id",
            $userId,
        )
            ->whereDate("created_at", $tanggal)
            ->get(["id", "pelanggan_id", "uraian", "saldo"]);

        $bukuBesarHutang = \App\Models\BukuBesarHutang::where(
            "user_id",
            $userId,
        )
            ->whereDate("created_at", $tanggal)
            ->get(["id", "pelanggan_id", "uraian", "saldo"]);

        // 🔹 Ambil kartu gudang langsung berdasarkan relasi barang_id yang sudah dimiliki neraca_awal
        $barangIds = $neracaAwal->barang->pluck("id");
        $kartuGudang = \App\Models\KartuGudang::where("user_id", $userId)
            ->whereIn("barang_id", $barangIds)
            ->get([
                "id",
                "barang_id",
                "diterima",
                "dikeluarkan",
                "saldo_perkemasan",
            ]);

        $user = auth()->user();

        return view(
            "neraca-awal.show",
            compact(
                "neracaAwal",
                "bukuBesarKas",
                "bukuBesarPiutang",
                "bukuBesarHutang",
                "kartuGudang",
                "user",
            ),
        );
    }
}
