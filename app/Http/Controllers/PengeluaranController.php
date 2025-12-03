<?php
namespace App\Http\Controllers;

use App\Http\Requests\PengeluaranRequest;
use App\Models\Barang;
use App\Models\BukuBesarHutang;
use App\Models\BukuBesarKas;
use App\Models\BukuBesarPengeluaran;
use App\Models\KartuGudang;
use App\Models\KasKecil;
use App\Models\NeracaAwal;
use App\Models\Pelanggan;
use App\Models\PengisianKasKecilLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PengeluaranController extends Controller
{
    public function list()
    {
        $userId = auth()->id();
        $pengeluaran = BukuBesarPengeluaran::where("user_id", $userId)->get();
        $totalPengeluaran = BukuBesarPengeluaran::where(
            "user_id",
            $userId,
        )->sum("jumlah_pengeluaran");

        $allDatas = BukuBesarPengeluaran::where("user_id", $userId)->get();
        $dataHutang = \App\Models\BukuBesarHutang::where("user_id", $userId)
            ->with("pelanggan:id,nama")
            ->get()
            ->groupBy("pelanggan_id");

        return view(
            "keuangan.pengeluaran.list",
            compact(
                "pengeluaran",
                "totalPengeluaran",
                "allDatas",
                "dataHutang",
            ),
        );
    }

    public function create()
    {
        $debitur = Pelanggan::where("user_id", auth()->id())
            ->where("jenis", "debitur")
            ->get();

        $kreditur = Pelanggan::where("user_id", auth()->id())
            ->where("jenis", "kreditur")
            ->get();

        $barang = Barang::where("user_id", auth()->id())->get();

        $listHutang = BukuBesarHutang::select(
            "id",
            "pelanggan_id",
            "uraian",
            "tanggal",
            "saldo",
        )
            ->where("saldo", ">", 0)
            ->whereIn("id", function ($query) {
                $query
                    ->select(DB::raw("MAX(id)"))
                    ->from("buku_besar_hutang")
                    ->groupBy("pelanggan_id");
            })
            ->latest()
            ->get();

        return view(
            "keuangan.pengeluaran.create",
            compact("debitur", "kreditur", "barang", "listHutang"),
        );
    }

    public function store(PengeluaranRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $neracaAwal = NeracaAwal::where("user_id", auth()->id())->first();
            $bukuBesarKasBefore = BukuBesarKas::where("user_id", auth()->id())
                ->latest()
                ->first();

            switch ($validated["jenis_keperluan"]) {
                // ==============================
                // CASE 1: PENGELUARAN LAIN-LAIN
                // ==============================
                case "lain_lain":
                    $jumlahUang =
                        ($validated["jumlah"] ?? 0) +
                        ($validated["biaya_lain"] ?? 0) -
                        ($validated["potongan_pembelian"] ?? 0);
                    $saldoKasBaru =
                        ($bukuBesarKasBefore->saldo ?? 0) - $jumlahUang;

                    BukuBesarKas::create([
                        "kode" => Str::uuid(),
                        "tanggal" => $validated["tanggal"],
                        "uraian" =>
                            "Pengeluaran lain-lain: " .
                            $validated["uraian_pengeluaran"],
                        "debit" => 0,
                        "kredit" => $jumlahUang,
                        "saldo" => $saldoKasBaru,
                        "neraca_awal_id" => $neracaAwal->id ?? null,
                        "user_id" => auth()->id(),
                    ]);

                    if ($validated["jenis_pengeluaran"] == "tunai") {
                        // Pengeluaran tunai → kas berkurang
                        $bukuBesarPengeluaran = BukuBesarPengeluaran::create([
                            "user_id" => auth()->id(),
                            "tanggal" => $validated["tanggal"],
                            "uraian" =>
                                "Pengeluaran lain-lain: " .
                                $validated["uraian_pengeluaran"],
                            "potongan_pembelian" =>
                                $validated["potongan_pembelian"] ?? 0,
                            "jumlah_hutang" => 0,
                            "jumlah_pembelian_tunai" => 0,
                            "lain_lain" => $jumlahUang,
                            "admin_bank" => $validated["admin_bank"] ?? 0,
                            "jumlah_pengeluaran" => $jumlahUang,
                        ]);
                    } else {
                        // Pengeluaran kredit → hutang bertambah
                        // $bukuBesarPengeluaran = BukuBesarPengeluaran::create([
                        //     "user_id" => auth()->id(),
                        //     "tanggal" => $validated["tanggal"],
                        //     "uraian" => $validated["uraian_pengeluaran"],
                        //     "potongan_pembelian" =>
                        //         $validated["potongan_pembelian"] ?? 0,
                        //     "jumlah_hutang" => 0,
                        //     "jumlah_pembelian_tunai" => 0,
                        //     "lain_lain" => $jumlahUang,
                        //     "admin_bank" => $validated["admin_bank"] ?? 0,
                        //     "jumlah_pengeluaran" => $jumlahUang,
                        // ]);
                        $existingHutang = BukuBesarHutang::where(
                            "pelanggan_id",
                            $validated["nama_kreditur"],
                        )
                            ->latest()
                            ->first();

                        if ($existingHutang) {
                            $saldoHutangBaru =
                                $existingHutang->saldo + $validated["jumlah"];
                            BukuBesarHutang::create([
                                "kode" => Str::uuid(),
                                "pelanggan_id" => $validated["nama_kreditur"],
                                "uraian" =>
                                    "Hutang lain-lain: " .
                                    $validated["uraian_pengeluaran"],
                                "tanggal" => $validated["tanggal"],
                                "debit" => 0,
                                "kredit" => $jumlahUang,
                                "saldo" => $saldoHutangBaru,
                                "buku_besar_pengeluaran_id" => null,
                                "user_id" => auth()->id(),
                            ]);
                        } else {
                            BukuBesarHutang::create([
                                "kode" => Str::uuid(),
                                "pelanggan_id" => $validated["nama_kreditur"],
                                "uraian" =>
                                    "Hutang lain-lain: " .
                                    $validated["uraian_pengeluaran"],
                                "tanggal" => $validated["tanggal"],
                                "debit" => 0,
                                "kredit" => $jumlahUang,
                                "saldo" => $jumlahUang,
                                "buku_besar_pengeluaran_id" => null,
                                "user_id" => auth()->id(),
                            ]);
                        }
                    }
                    break;

                // ==================================
                // CASE 2: MEMBAYAR HUTANG
                // ==================================
                case "membayar_hutang":
                    $jumlahUang =
                        ($validated["jumlah"] ?? 0) +
                        ($validated["biaya_lain"] ?? 0) -
                        ($validated["potongan_pembelian"] ?? 0);
                    $saldoKasBaru =
                        ($bukuBesarKasBefore->saldo ?? 0) - $jumlahUang;

                    $bukuBesarPengeluaran = BukuBesarPengeluaran::create([
                        "user_id" => auth()->id(),
                        "tanggal" => $validated["tanggal"],
                        "uraian" =>
                            "Pelunasan hutang: " .
                            $validated["uraian_pengeluaran"],
                        "potongan_pembelian" =>
                            $validated["potongan_pembelian"] ?? 0,
                        "jumlah_hutang" => $jumlahUang,
                        "jumlah_pembelian_tunai" => 0,
                        "lain_lain" => 0,
                        "admin_bank" => $validated["admin_bank"] ?? 0,
                        "jumlah_pengeluaran" => $jumlahUang,
                    ]);

                    $dataHutangBefore = BukuBesarHutang::findOrFail(
                        $validated["hutang_id"],
                    );

                    BukuBesarHutang::create([
                        "kode" => $dataHutangBefore->kode,
                        "pelanggan_id" => $dataHutangBefore->pelanggan_id,
                        "tanggal" => $validated["tanggal"],
                        "uraian" =>
                            "Pelunasan hutang: " .
                            $validated["uraian_pengeluaran"],
                        "debit" => $jumlahUang,
                        "kredit" => 0,
                        "saldo" => $dataHutangBefore->saldo - $jumlahUang,
                        "buku_besar_pengeluaran_id" =>
                            $bukuBesarPengeluaran->id,
                        "user_id" => auth()->id(),
                    ]);

                    // Kurangi kas
                    BukuBesarKas::create([
                        "kode" => $dataHutangBefore->kode,
                        "tanggal" => $validated["tanggal"],
                        "uraian" =>
                            "Pelunasan hutang: " .
                            $validated["uraian_pengeluaran"],
                        "debit" => 0,
                        "kredit" => $jumlahUang,
                        "saldo" => $saldoKasBaru,
                        "neraca_awal_id" => $neracaAwal->id ?? null,
                        "user_id" => auth()->id(),
                    ]);

                    // Jika centang “ada barang baru”
                    if ($request->filled("ada_barang_baru")) {
                        $this->tambahBarangKeGudang($validated);
                    }
                    break;

                // ==================================
                // CASE 3: MEMBELI BARANG
                // ==================================
                case "membeli_barang":
                    $jumlahManual = $validated["jumlah_manual"] ?? 0;
                    $jumlahUang =
                        ($validated["jumlah"] ?? 0) +
                        $jumlahManual +
                        ($validated["biaya_lain"] ?? 0) -
                        ($validated["potongan_pembelian"] ?? 0);
                    $saldoKasBaru =
                        ($bukuBesarKasBefore->saldo ?? 0) - $jumlahUang;

                    $latestBukuBesarKas = BukuBesarKas::where(
                        "user_id",
                        auth()->id(),
                    )
                        ->latest()
                        ->first();

                    $saldoKasSebelumnya = $latestBukuBesarKas->saldo ?? 0;
                    $saldoKasBaru = $saldoKasSebelumnya - $jumlahUang;

                    $recordKas = BukuBesarKas::create([
                        "kode" => Str::uuid(),
                        "uraian" =>
                            "Pengeluaran membeli barang - " .
                            now()->format("d/m/Y") .
                            " - " .
                            $validated["uraian_pengeluaran"],
                        "tanggal" => now(),
                        "debit" => $jumlahUang,
                        "kredit" => 0,
                        "saldo" => $saldoKasBaru,
                        "neraca_awal_id" => null,
                        "neraca_akhir_id" => null,
                        "user_id" => auth()->id(),
                    ]);

                    if ($validated["jenis_pengeluaran"] == "tunai") {
                        $bukuBesarPengeluaran = BukuBesarPengeluaran::create([
                            "user_id" => auth()->id(),
                            "tanggal" => $validated["tanggal"],
                            "uraian" =>
                                "Pengeluaran membeli barang - " .
                                now()->format("d/m/Y") .
                                " - " .
                                $validated["uraian_pengeluaran"],
                            "potongan_pembelian" =>
                                $validated["potongan_pembelian"] ?? 0,
                            "jumlah_hutang" =>
                                $validated["jenis_pengeluaran"] == "kredit"
                                    ? $validated["jumlah"]
                                    : 0,
                            "jumlah_pembelian_tunai" =>
                                $validated["jenis_pengeluaran"] == "tunai"
                                    ? $validated["jumlah"]
                                    : 0,
                            "lain_lain" => $validated["biaya_lain"] ?? 0,
                            "admin_bank" => $validated["admin_bank"] ?? 0,
                            "jumlah_pengeluaran" => $jumlahUang,
                        ]);
                    } else {
                        $existingHutang = BukuBesarHutang::where(
                            "pelanggan_id",
                            $validated["nama_kreditur"],
                        )
                            ->latest()
                            ->first();

                        if ($existingHutang) {
                            $saldoHutangBaru =
                                $existingHutang->saldo + $validated["jumlah"];

                            BukuBesarHutang::create([
                                "kode" => $existingHutang->kode,
                                "pelanggan_id" => $validated["nama_kreditur"],
                                "uraian" =>
                                    "Hutang membeli barang - " .
                                    now()->format("d/m/Y") .
                                    " - " .
                                    $validated["uraian_pengeluaran"],
                                "tanggal" => $validated["tanggal"],
                                "debit" => 0,
                                "kredit" => $validated["jumlah"],
                                "saldo" => $saldoHutangBaru,
                                "buku_besar_pengeluaran_id" => null,
                                "user_id" => auth()->id(),
                            ]);
                        } else {
                            BukuBesarHutang::create([
                                "kode" => Str::uuid(),
                                "pelanggan_id" => $validated["nama_kreditur"],
                                "uraian" =>
                                    "Hutang membeli barang - " .
                                    now()->format("d/m/Y") .
                                    " - " .
                                    $validated["uraian_pengeluaran"],
                                "tanggal" => $validated["tanggal"],
                                "debit" => 0,
                                "kredit" => $validated["jumlah"],
                                "saldo" => $validated["jumlah"],
                                "buku_besar_pengeluaran_id" => null,
                                "user_id" => auth()->id(),
                            ]);
                        }
                    }
                    $this->tambahBarangKeGudang($validated);
                    break;
                // ==================================
                // CASE 4: MENGISI KAS KECIL
                // ==================================
                case "kas_kecil":
                    $jumlahUang =
                        ($validated["jumlah"] ?? 0) +
                        ($validated["biaya_lain"] ?? 0) -
                        ($validated["potongan_pembelian"] ?? 0);
                    $kodeRef = Str::uuid();
                    $latestSaldoKasKecil = KasKecil::where(
                        "user_id",
                        auth()->id(),
                    )
                        ->latest()
                        ->first();

                    if ($latestSaldoKasKecil) {
                        $kasKecil = KasKecil::create([
                            "user_id" => auth()->id(),
                            "tanggal" => now(),
                            "nomor_referensi" => $kodeRef,
                            "penerimaan" => $jumlahUang,
                            "pengeluaran" => 0,
                            "saldo_akhir" =>
                                $latestSaldoKasKecil->saldo_akhir + $jumlahUang,
                        ]);
                    } else {
                        $kasKecil = KasKecil::create([
                            "user_id" => auth()->id(),
                            "tanggal" => now(),
                            "nomor_referensi" => $kodeRef,
                            "penerimaan" => $jumlahUang,
                            "pengeluaran" => 0,
                            "saldo_akhir" => $jumlahUang,
                        ]);
                    }

                    $latestBukuBesarKas = BukuBesarKas::where(
                        "user_id",
                        auth()->id(),
                    )
                        ->latest()
                        ->first();

                    $saldoKasSebelumnya = $latestBukuBesarKas->saldo ?? 0;
                    $saldoKasBaru = $saldoKasSebelumnya - $jumlahUang;

                    $recordKas = BukuBesarKas::create([
                        "kode" => Str::uuid(),
                        "uraian" =>
                            "Pengeluaran ke kas kecil - " .
                            now()->format("d/m/Y") .
                            " - " .
                            $validated["uraian_pengeluaran"],
                        "tanggal" => now(),
                        "debit" => $jumlahUang,
                        "kredit" => 0,
                        "saldo" => $saldoKasBaru,
                        "neraca_awal_id" => null,
                        "neraca_akhir_id" => null,
                        "user_id" => auth()->id(),
                    ]);

                    PengisianKasKecilLog::create([
                        "buku_besar_kas_id" => $recordKas->id, // aman, tidak null
                        "kas_kecil_id" => $kasKecil->id,
                        "uraian" =>
                            "Pengisian kas kecil: " .
                            now()->format("d/m/Y") .
                            " - " .
                            $validated["uraian_pengeluaran"],
                        "jumlah" => $jumlahUang,
                        "tanggal_transaksi" => now(),
                        "user_id" => auth()->id(),
                    ]);

                    if ($validated["jenis_pengeluaran"] == "kredit") {
                        // cek saldo lama
                        $exisingSaldoHutang = BukuBesarHutang::where(
                            "user_id",
                            auth()->id(),
                        )
                            ->where("pelanggan_id", $validated["nama_kreditur"])
                            ->latest()
                            ->first();

                        $saldoHutangBaru = $exisingSaldoHutang->saldo
                            ? $exisingSaldoHutang->saldo + $jumlahUang
                            : $jumlahUang;

                        $bukuBesarHutang = BukuBesarHutang::create([
                            "pelanggan_id" => $validated["nama_kreditur"],
                            "kode" => $recordKas->kode,
                            "uraian" =>
                                "Pengisian kas kecil secara kredit: " .
                                now()->format("d/m/Y") .
                                " - " .
                                $validated["uraian_pengeluaran"],
                            "tanggal" => now(),
                            "debit" => 0,
                            "kredit" => $jumlahUang,
                            "saldo" => $saldoHutangBaru,
                            "buku_besar_pendapatan_id" => null,
                            "buku_besar_pengeluaran_id" => null,
                            "neraca_awal_id" => null,
                            "neraca_akhir_id" => null,
                            "user_id" => auth()->id(),
                        ]);
                    } else {
                        $bukuBesarPengeluaran = BukuBesarPengeluaran::create([
                            "user_id" => auth()->id(),
                            "tanggal" => $validated["tanggal"],
                            "uraian" =>
                                "Pengisian kas kecil: " .
                                now()->format("d/m/Y") .
                                " - " .
                                $validated["uraian_pengeluaran"],
                            "potongan_pembelian" =>
                                $validated["potongan_pembelian"] ?? 0,
                            "jumlah_hutang" =>
                                $validated["jenis_pengeluaran"] == "kredit"
                                    ? $validated["jumlah"]
                                    : 0,
                            "jumlah_pembelian_tunai" =>
                                $validated["jenis_pengeluaran"] == "tunai"
                                    ? $validated["jumlah"]
                                    : 0,
                            "lain_lain" => $validated["biaya_lain"] ?? 0,
                            "admin_bank" => $validated["admin_bank"] ?? 0,
                            "jumlah_pengeluaran" => $jumlahUang,
                        ]);
                    }
                    break;
            }

            DB::commit();
            return redirect()
                ->route("keuangan.pengeluaran.create")
                ->with("success", "Data pengeluaran berhasil disimpan.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal menyimpan pengeluaran", [
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
                "input" => $request->all(),
                "user_id" => auth()->id(),
            ]);
            return redirect()
                ->back()
                ->with(
                    "error",
                    "Gagal menyimpan pengeluaran: " . $e->getMessage(),
                );
        }
    }

    private function tambahBarangKeGudang(array $validated)
    {
        // Cek apakah ada data barang dikirim
        if (
            empty($validated["barang_dibeli"]) ||
            empty($validated["jumlah_barang_dibeli"])
        ) {
            return;
        }

        // Loop setiap barang
        foreach ($validated["barang_dibeli"] as $index => $barangId) {
            $jumlahDibeli = (int) $validated["jumlah_barang_dibeli"][$index];

            $detailBarang = Barang::where("id", $barangId)
                ->where("user_id", auth()->id())
                ->firstOrFail();

            $barangItem = KartuGudang::where("barang_id", $barangId)
                ->where("user_id", auth()->id())
                ->latest()
                ->first();

            $pcsPerKemasan = $detailBarang->jumlah_unit_per_kemasan;
            $saldoSatuanBaru = $barangItem->saldo_persatuan + $jumlahDibeli;

            $saldoPerKemasanBaru =
                $barangItem->saldo_perkemasan +
                round($jumlahDibeli / $pcsPerKemasan, 0);

            // Simpan ke kartu gudang
            KartuGudang::create([
                "barang_id" => $barangId,
                "tanggal" => $validated["tanggal"],
                "diterima" => $jumlahDibeli,
                "dikeluarkan" => 0,
                "uraian" => $validated["uraian_pengeluaran"],
                "saldo_persatuan" => $saldoSatuanBaru,
                "saldo_perkemasan" => $saldoPerKemasanBaru,
                "user_id" => auth()->id(),
            ]);
        }
    }

    public function destroy($id)
    {
        $pengeluaran = BukuBesarPengeluaran::findOrFail($id);
        $pengeluaran->delete();

        return redirect()
            ->route("keuangan.pengeluaran.list")
            ->with("success", "Data pengeluaran berhasil dihapus.");
    }

    public function destroyHutang($id)
    {
        $hutang = BukuBesarHutang::where("id", $id)
            ->where("user_id", auth()->id())
            ->first();

        if (!$hutang) {
            return redirect()
                ->route("keuangan.pengeluaran.list")
                ->with("error", "Data tidak ditemukan atau tidak milik Anda.");
        }

        $hutang->delete();

        return redirect()
            ->route("keuangan.pengeluaran.list")
            ->with("success", "Data hutang berhasil dihapus.");
    }
}
