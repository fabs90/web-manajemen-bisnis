<?php
namespace App\Http\Controllers;

use App\Http\Requests\PengeluaranRequest;
use App\Models\Barang;
use App\Models\BukuBesarHutang;
use App\Models\BukuBesarKas;
use App\Models\BukuBesarPengeluaran;
use App\Models\KartuGudang;
use App\Models\NeracaAwal;
use App\Models\Pelanggan;
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

        $allDatas = BukuBesarPengeluaran::all();
        $dataHutang = BukuBesarHutang::where("user_id", $userId)->get();
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

            $jumlahUang =
                ($validated["jumlah"] ?? 0) +
                ($validated["biaya_lain"] ?? 0) -
                ($validated["potongan_pembelian"] ?? 0);

            $saldoKasBaru = ($bukuBesarKasBefore->saldo ?? 0) - $jumlahUang;

            switch ($validated["jenis_keperluan"]) {
                // ==============================
                // CASE 1: PENGELUARAN LAIN-LAIN
                // ==============================
                case "lain_lain":
                    $bukuBesarPengeluaran = BukuBesarPengeluaran::create([
                        "user_id" => auth()->id(),
                        "tanggal" => $validated["tanggal"],
                        "uraian" => $validated["uraian_pengeluaran"],
                        "potongan_pembelian" =>
                            $validated["potongan_pembelian"] ?? 0,
                        "jumlah_hutang" => 0,
                        "jumlah_pembelian_tunai" => $validated["jumlah"] ?? 0,
                        "lain_lain" => $jumlahUang,
                        "bunga_bank" => $validated["bunga_bank"] ?? 0,
                        "jumlah_pengeluaran" => $jumlahUang,
                    ]);

                    if ($validated["jenis_pengeluaran"] == "tunai") {
                        // Pengeluaran tunai → kas berkurang
                        BukuBesarKas::create([
                            "kode" => Str::uuid(),
                            "tanggal" => $validated["tanggal"],
                            "uraian" => $validated["uraian_pengeluaran"],
                            "debit" => 0,
                            "kredit" => $jumlahUang,
                            "saldo" => $saldoKasBaru,
                            "neraca_awal_id" => $neracaAwal->id ?? null,
                            "user_id" => auth()->id(),
                        ]);
                    } else {
                        // Pengeluaran kredit → hutang bertambah
                        BukuBesarHutang::create([
                            "kode" => Str::uuid(),
                            "pelanggan_id" => $validated["nama_kreditur"],
                            "uraian" => $validated["uraian_pengeluaran"],
                            "tanggal" => $validated["tanggal"],
                            "debit" => 0,
                            "kredit" => $jumlahUang,
                            "saldo" => $jumlahUang,
                            "buku_besar_pengeluaran_id" =>
                                $bukuBesarPengeluaran->id,
                            "user_id" => auth()->id(),
                        ]);
                    }
                    break;

                // ==================================
                // CASE 2: MEMBAYAR HUTANG
                // ==================================
                case "membayar_hutang":
                    $bukuBesarPengeluaran = BukuBesarPengeluaran::create([
                        "user_id" => auth()->id(),
                        "tanggal" => $validated["tanggal"],
                        "uraian" => $validated["uraian_pengeluaran"],
                        "potongan_pembelian" =>
                            $validated["potongan_pembelian"] ?? 0,
                        "jumlah_hutang" => $jumlahUang,
                        "jumlah_pembelian_tunai" => 0,
                        "lain_lain" => 0,
                        "bunga_bank" => $validated["bunga_bank"] ?? 0,
                        "jumlah_pengeluaran" => $jumlahUang,
                    ]);

                    $dataHutangBefore = BukuBesarHutang::findOrFail(
                        $validated["hutang_id"],
                    );

                    // Kurangi saldo hutang
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
                    $bukuBesarPengeluaran = BukuBesarPengeluaran::create([
                        "user_id" => auth()->id(),
                        "tanggal" => $validated["tanggal"],
                        "uraian" => $validated["uraian_pengeluaran"],
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
                        "bunga_bank" => $validated["bunga_bank"] ?? 0,
                        "jumlah_pengeluaran" => $jumlahUang,
                    ]);

                    if ($validated["jenis_pengeluaran"] == "kredit") {
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
                                    "Pembelian barang: " .
                                    $validated["uraian_pengeluaran"],
                                "tanggal" => $validated["tanggal"],
                                "debit" => 0,
                                "kredit" => $validated["jumlah"],
                                "saldo" => $saldoHutangBaru,
                                "buku_besar_pengeluaran_id" =>
                                    $bukuBesarPengeluaran->id,
                                "user_id" => auth()->id(),
                            ]);
                        } else {
                            BukuBesarHutang::create([
                                "kode" => Str::uuid(),
                                "pelanggan_id" => $validated["nama_kreditur"],
                                "uraian" =>
                                    "Pembelian barang: " .
                                    $validated["uraian_pengeluaran"],
                                "tanggal" => $validated["tanggal"],
                                "debit" => 0,
                                "kredit" => $validated["jumlah"],
                                "saldo" => $validated["jumlah"],
                                "buku_besar_pengeluaran_id" =>
                                    $bukuBesarPengeluaran->id,
                                "user_id" => auth()->id(),
                            ]);
                        }
                    }

                    // tidak usah pakai kas karena hutan bukan pengelauran lgsg
                    $this->tambahBarangKeGudang($validated);
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

        $detailBarang = Barang::where("id", $validated["barang_dibeli"])
            ->where("user_id", auth()->id())
            ->firstOrFail();

        $barangItem = KartuGudang::where(
            "barang_id",
            $validated["barang_dibeli"],
        )
            ->where("user_id", auth()->id())
            ->latest()
            ->first();

        // Ambil data dasar
        $pcsDibeli = (int) $validated["jumlah_barang_dibeli"];
        $pcsPerKemasan = (int) $detailBarang->jumlah_unit_per_kemasan;

        // Hitung konversi
        $kemasanMasuk = intdiv($pcsDibeli, $pcsPerKemasan);
        $sisaSatuanMasuk = $pcsDibeli % $pcsPerKemasan;

        $saldoKemasanSekarang = $barangItem->saldo_perkemasan ?? 0;
        $saldoSatuanSekarang = $barangItem->saldo_persatuan ?? 0;

        $saldoSatuanBaru = $saldoSatuanSekarang + $sisaSatuanMasuk;
        $konversiKeKemasan = intdiv($saldoSatuanBaru, $pcsPerKemasan);
        $saldoSatuanBaru = $saldoSatuanBaru % $pcsPerKemasan;

        $saldoKemasanBaru =
            $saldoKemasanSekarang + $kemasanMasuk + $konversiKeKemasan;

        // Validasi batas stok maksimum (opsional)
        if (
            !empty($detailBarang->jumlah_max) &&
            $saldoKemasanBaru > $detailBarang->jumlah_max
        ) {
            throw new \Exception(
                "Stok barang '{$detailBarang->nama}' melebihi batas maksimum ({$detailBarang->jumlah_max} kemasan).",
            );
        }

        // Simpan ke kartu gudang
        KartuGudang::create([
            "barang_id" => $validated["barang_dibeli"],
            "tanggal" => $validated["tanggal"],
            "diterima" => $pcsDibeli,
            "dikeluarkan" => 0,
            "uraian" => $validated["uraian_pengeluaran"],
            "saldo_persatuan" => $saldoSatuanBaru,
            "saldo_perkemasan" => $saldoKemasanBaru,
            "user_id" => auth()->id(),
        ]);
    }

    public function destroy($id)
    {
        $pengeluaran = BukuBesarPengeluaran::findOrFail($id);
        $pengeluaran->delete();

        return redirect()
            ->route("keuangan.pengeluaran.list")
            ->with("success", "Data pengeluaran berhasil dihapus.");
    }
}
