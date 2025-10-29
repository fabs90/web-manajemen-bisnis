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
            ->where("jenis", "debitur")
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
            $jumlahUang =
                $validated["jumlah"] +
                $validated["biaya_lain"] -
                $validated["potongan_pembelian"];

            $neracaAwal = NeracaAwal::where("user_id", auth()->id())->first();
            $bukuBesarKasBefore = BukuBesarkas::where("user_id", auth()->id())
                ->latest()
                ->first();

            switch ($validated["jenis_keperluan"]) {
                case "lain_lain":
                    $bukuBesarPengeluaran = BukuBesarPengeluaran::create([
                        "user_id" => auth()->id(),
                        "tanggal" => $validated["tanggal"],
                        "uraian" => $validated["uraian_pengeluaran"],
                        "potongan_pembelian" =>
                            $validated["potongan_pembelian"],
                        "jumlah_hutang" => 0,
                        "jumlah_pembelian_tunai" => $validated["jumlah"],
                        "lain_lain" => $jumlahUang,
                        "bunga_bank" => $validated["bunga_bank"],
                        "jumlah_pengeluaran" => $jumlahUang,
                    ]);
                    if ($validated["jenis_pengeluaran"] == "kredit") {
                        if (!$request->filled("hutang_id")) {
                            $bukuBesarKredit = BukuBesarHutang::create([
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

                            $bukuBesarKas = BukuBesarKas::create([
                                "kode" => $bukuBesarKredit->kode,
                                "tanggal" => $validated["tanggal"],
                                "uraian" => $validated["uraian_pengeluaran"],
                                "debit" => 0,
                                "kredit" => $jumlahUang,
                                "saldo" =>
                                    $bukuBesarKasBefore->saldo - $jumlahUang,
                                "neraca_awal_id" => $neracaAwal->id,
                                "user_id" => auth()->id(),
                            ]);
                            break;
                        }
                    }
                    $bukuBesarKas = BukuBesarKas::create([
                        "kode" => Str::uuid(),
                        "tanggal" => $validated["tanggal"],
                        "uraian" => $validated["uraian_pengeluaran"],
                        "debit" => 0,
                        "kredit" => $jumlahUang,
                        "saldo" => $bukuBesarKasBefore->saldo - $jumlahUang,
                        "neraca_awal_id" => $neracaAwal->id,
                        "user_id" => auth()->id(),
                    ]);
                    break;

                case "membayar_hutang":
                    $bukuBesarPengeluaran = BukuBesarPengeluaran::create([
                        "user_id" => auth()->id(),
                        "tanggal" => $validated["tanggal"],
                        "uraian" => $validated["uraian_pengeluaran"],
                        "potongan_pembelian" =>
                            $validated["potongan_pembelian"],
                        "jumlah_hutang" => $jumlahUang,
                        "jumlah_pembelian_tunai" => 0,
                        "lain_lain" => 0,
                        "bunga_bank" => $validated["bunga_bank"],
                        "jumlah_pengeluaran" => $jumlahUang,
                    ]);
                    $dataHutangBefore = BukuBesarHutang::where(
                        "id",
                        $validated["hutang_id"],
                    )->first();

                    if ($validated["jenis_pengeluaran"] == "kredit") {
                        $bukuBesarKredit = BukuBesarHutang::create([
                            "kode" => $dataHutangBefore->kode,
                            "pelanggan_id" => $dataHutangBefore->pelanggan->id,
                            "uraian" => $validated["uraian_pengeluaran"],
                            "tanggal" => $validated["tanggal"],
                            "debit" => 0,
                            "kredit" => $jumlahUang,
                            "saldo" => $jumlahUang + $dataHutangBefore->saldo,
                            "buku_besar_pengeluaran_id" =>
                                $bukuBesarPengeluaran->id,
                            "user_id" => auth()->id(),
                        ]);

                        $bukuBesarKas = BukuBesarKas::create([
                            "kode" => $bukuBesarKredit->kode,
                            "tanggal" => $validated["tanggal"],
                            "uraian" => $validated["uraian_pengeluaran"],
                            "debit" => 0,
                            "kredit" => $jumlahUang,
                            "saldo" => $bukuBesarKasBefore->saldo - $jumlahUang,
                            "neraca_awal_id" => $neracaAwal->id,
                            "user_id" => auth()->id(),
                        ]);
                        break;
                    }

                    $bukuBesarHutang = BukuBesarHutang::create([
                        "kode" => $dataHutangBefore->kode,
                        "pelanggan_id" => $dataHutangBefore->pelanggan->id,
                        "tanggal" => $validated["tanggal"],
                        "uraian" => $validated["uraian_pengeluaran"],
                        "debit" =>
                            $validated["jumlah"] + $validated["biaya_lain"],
                        "kredit" => 0,
                        "saldo" => $dataHutangBefore->saldo - $jumlahUang,
                        "buku_besar_pengeluaran_id" =>
                            $bukuBesarPengeluaran->id,

                        "user_id" => auth()->id(),
                    ]);

                    $bukuBesarKas = BukuBesarKas::create([
                        "kode" => $dataHutangBefore->kode,
                        "tanggal" => $validated["tanggal"],
                        "uraian" => $validated["uraian_pengeluaran"],
                        "debit" => 0,
                        "kredit" => $jumlahUang,
                        "saldo" => $bukuBesarKasBefore->saldo - $jumlahUang,
                        "neraca_awal_id" => $neracaAwal->id,
                        "user_id" => auth()->id(),
                    ]);

                    break;

                case "membeli_barang":
                    if ($validated["jenis_pengeluaran"] == "tunai") {
                        $bukuBesarPengeluaran = BukuBesarPengeluaran::create([
                            "user_id" => auth()->id(),
                            "tanggal" => $validated["tanggal"],
                            "uraian" => $validated["uraian_pengeluaran"],
                            "potongan_pembelian" =>
                                $validated["potongan_pembelian"] ?? 0,
                            "jumlah_hutang" => 0,
                            "jumlah_pembelian_tunai" => $validated["jumlah"],
                            "lain_lain" => $validated["biaya_lain"] ?? 0,
                            "bunga_bank" => $validated["bunga_bank"] ?? 0,
                            "jumlah_pengeluaran" => $jumlahUang,
                        ]);

                        $bukuBesarKas = BukuBesarKas::create([
                            "kode" => Str::uuid(),
                            "tanggal" => $validated["tanggal"],
                            "uraian" => $validated["uraian_pengeluaran"],
                            "debit" => 0,
                            "kredit" => $jumlahUang,
                            "saldo" => $bukuBesarKasBefore->saldo - $jumlahUang,
                            "neraca_awal_id" => $neracaAwal->id,
                            "user_id" => auth()->id(),
                        ]);
                    } else {
                        $bukuBesarPengeluaran = BukuBesarPengeluaran::create([
                            "user_id" => auth()->id(),
                            "tanggal" => $validated["tanggal"],
                            "uraian" => $validated["uraian_pengeluaran"],
                            "potongan_pembelian" =>
                                $validated["potongan_pembelian"] ?? 0,
                            "jumlah_hutang" => $validated["jumlah"],
                            "jumlah_pembelian_tunai" => 0,
                            "lain_lain" => $validated["biaya_lain"] ?? 0,
                            "bunga_bank" => $validated["bunga_bank"] ?? 0,
                            "jumlah_pengeluaran" => $jumlahUang,
                        ]);

                        $bukuBesarKredit = BukuBesarHutang::create([
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

                        $bukuBesarKas = BukuBesarKas::create([
                            "kode" => $bukuBesarKredit->kode,
                            "tanggal" => $validated["tanggal"],
                            "uraian" => $validated["uraian_pengeluaran"],
                            "debit" => 0,
                            "kredit" => $jumlahUang,
                            "saldo" => $bukuBesarKasBefore->saldo - $jumlahUang,
                            "neraca_awal_id" => $neracaAwal->id,
                            "user_id" => auth()->id(),
                        ]);
                    }
                    // Masukan ke kartu gudang
                    $detailBarang = Barang::where(
                        "id",
                        $validated["barang_dibeli"],
                    )->first();

                    $barangItem = KartuGudang::where(
                        "barang_id",
                        $validated["barang_dibeli"],
                    )
                        ->latest()
                        ->first();

                    // Hitung Berapa Kemasan yg dibutuhkan 1 kemasan
                    // 1. Hitung berapa kemasan penuh dan sisa satuan dari barang yang dibeli
                    $pcsDibeli = $validated["jumlah_barang_dibeli"]; // misal: 100 pcs
                    $pcsPerKemasan = $detailBarang->jumlah_unit_per_kemasan; // 12

                    $kemasanPenuhMasuk = floor($pcsDibeli / $pcsPerKemasan); // 8 kemasan
                    $sisaSatuanMasuk = $pcsDibeli % $pcsPerKemasan; // 4 pcs

                    // 2. Ambil saldo gudang saat ini
                    $saldoKemasanSekarang = $barangItem->saldo_perkemasan ?? 0; // misal: 5
                    $saldoSatuanSekarang = $barangItem->saldo_persatuan ?? 0; // misal: 2

                    // 3. Hitung saldo baru
                    $saldoSatuanBaru = $saldoSatuanSekarang + $sisaSatuanMasuk; // 2 + 4 = 6

                    // Jika satuan >= 1 kemasan → konversi ke kemasan penuh
                    $konversiKeKemasan = floor(
                        $saldoSatuanBaru / $pcsPerKemasan,
                    ); // 6 / 12 = 0
                    $saldoSatuanBaru = $saldoSatuanBaru % $pcsPerKemasan; // 6

                    $saldoKemasanBaru =
                        $saldoKemasanSekarang +
                        $kemasanPenuhMasuk +
                        $konversiKeKemasan;
                    // 5 + 8 + 0 = 13 kemasan

                    if ($saldoKemasanBaru > $detailBarang->jumlah_max) {
                        Log::error(
                            "Saldo barang melebihi batas maksimum pembelian!, $detailBarang->jumlah_max",
                        );
                        throw new \Exception(
                            "Saldo barang melebihi batas maksimum pembelian,  $detailBarang->jumlah_max",
                        );
                    }

                    $kartuGudang = KartuGudang::create([
                        "barang_id" => $validated["barang_dibeli"],
                        "tanggal" => $validated["tanggal"],
                        "diterima" => $pcsDibeli,
                        "dikeluarkan" => 0,
                        "uraian" => $validated["uraian_pengeluaran"],
                        "saldo_persatuan" =>
                            $saldoSatuanSekarang +
                            $validated["jumlah_barang_dibeli"],
                        "saldo_perkemasan" => $saldoKemasanBaru,
                        "user_id" => auth()->id(),
                    ]);
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
                "user_id" => auth()->id(),
                "input" => $request->all(),
            ]);

            return redirect()
                ->back()
                ->with("error", "Gagal menyimpan pengeluaran");
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
}
