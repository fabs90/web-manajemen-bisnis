<?php

namespace App\Http\Controllers;

use App\Http\Requests\PendapatanRequest;
use App\Models\Barang;
use App\Models\BukuBesarHutang;
use App\Models\BukuBesarKas;
use App\Models\BukuBesarPendapatan;
use App\Models\BukuBesarPiutang;
use App\Models\KartuGudang;
use App\Models\NeracaAwal;
use App\Models\Pelanggan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PendapatanController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $pendapatan = BukuBesarPendapatan::where("user_id", $userId)->get();
        $bunga_bank = BukuBesarPendapatan::where("user_id", $userId)
            ->whereNotNull("bunga_bank")
            ->where("bunga_bank", ">=", 0)
            ->latest("tanggal")
            ->first();

        $totalPendapatan = BukuBesarPendapatan::where("user_id", $userId)->sum(
            "uang_diterima",
        );

        $allDatas = BukuBesarPendapatan::all();
        $dataPiutang = BukuBesarPiutang::where("user_id", $userId)->get();

        return view(
            "keuangan.pendapatan.list",
            compact(
                "pendapatan",
                "bunga_bank",
                "totalPendapatan",
                "allDatas",
                "dataPiutang",
            ),
        );
    }

    public function create()
    {
        // Ambil semua debitur (untuk piutang & kredit)
        $debitur = Pelanggan::where("user_id", auth()->id())
            ->where("jenis", "debitur")
            ->orderBy("nama")
            ->get();

        // Ambil semua barang
        $barang = Barang::where("user_id", auth()->id())
            ->orderBy("nama")
            ->get();

        // Ambil piutang/hutang aktif (saldo > 0) dari transaksi terakhir per pelanggan
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

        return view(
            "keuangan.pendapatan.create",
            compact("debitur", "barang", "listPiutang"),
        );
    }

    public function store(PendapatanRequest $request)
    {
        $validated = $request->validated();
        try {
            DB::beginTransaction();

            switch ($request->jenis_pendapatan) {
                case "tunai":
                    $saldoFinal =
                        $request->biaya_lain +
                        $request->jumlah -
                        $request->potongan_pembelian;

                    $bukuBesarPendapatan = BukuBesarPendapatan::create([
                        "tanggal" => $request->tanggal,
                        "uraian" => $request->uraian_pendapatan,
                        "potongan_pembelian" =>
                            $request->potongan_pembelian ?? 0,
                        "piutang_dagang" => 0,
                        "penjualan_tunai" => $request->jumlah ?? 0,
                        "lain_lain" => $request->biaya_lain ?? 0,
                        "uang_diterima" => $saldoFinal,
                        "bunga_bank" => $request->bunga_bank ?? 0,
                        "user_id" => auth()->id(),
                    ]);

                    $neracaAwalBefore = NeracaAwal::where(
                        "user_id",
                        auth()->id(),
                    )->first();

                    $bukuBesarKas = BukuBesarKas::create([
                        "kode" => Str::uuid(),
                        "uraian" => $request->uraian_pendapatan,
                        "tanggal" => $request->tanggal,
                        "debit" => $saldoFinal,
                        "kredit" => 0,
                        "saldo" => $saldoFinal,
                        "neraca_awal_id" => $neracaAwalBefore->id,
                        "neraca_akhir_id" => null,
                        "user_id" => auth()->id(),
                    ]);

                    break;

                case "piutang":
                    $saldoTerakhir = BukuBesarPiutang::where(
                        "kode",
                        $validated["piutang_aktif"],
                    )
                        ->latest()
                        ->first();

                    $saldoFinal =
                        $request->jumlah +
                        $request->biaya_lain -
                        $request->potongan_pembelian;

                    // Handle Buku Besar Pendapatan
                    $bukuBesarPendapatan = BukuBesarPendapatan::create([
                        "tanggal" => $request->tanggal,
                        "uraian" => $request->uraian_pendapatan,
                        "potongan_pembelian" =>
                            $request->potongan_pembelian ?? 0,
                        "piutang_dagang" => $request->jumlah,
                        "penjualan_tunai" => 0,
                        "lain_lain" => $request->biaya_lain ?? 0,
                        "uang_diterima" => $saldoFinal,
                        "bunga_bank" => $request->bunga_bank ?? 0,
                        "user_id" => auth()->id(),
                    ]);

                    // Handle piutang
                    $bukuBesarPiutang = BukuBesarPiutang::create([
                        "kode" => $saldoTerakhir->kode,
                        "pelanggan_id" => $request->nama_pelanggan,
                        "uraian" => $request->uraian_pendapatan,
                        "tanggal" => $request->tanggal,
                        "debit" => $saldoFinal,
                        "kredit" => 0,
                        "saldo" => $saldoTerakhir->saldo + $saldoFinal,
                        "buku_besar_pendapatan_id" => $bukuBesarPendapatan->id,
                        "user_id" => auth()->id(),
                    ]);

                    $neracaAwalBefore = NeracaAwal::where(
                        "user_id",
                        auth()->id(),
                    )->first();

                    $bukuBesarKas = BukuBesarKas::create([
                        "kode" => $saldoTerakhir->kode,
                        "uraian" => $request->uraian_pendapatan,
                        "tanggal" => $request->tanggal,
                        "debit" => $saldoFinal,
                        "kredit" => 0,
                        "saldo" => $saldoFinal,
                        "neraca_awal_id" => $neracaAwalBefore->id,
                        "neraca_akhir_id" => null,
                        "user_id" => auth()->id(),
                    ]);
                    break;

                case "kredit":
                    $saldoTerakhir = BukuBesarPiutang::select("kode", "saldo")
                        ->where("kode", $validated["hutang_aktif"])
                        ->latest()
                        ->first();

                    $saldoFinal =
                        $request->jumlah +
                        $request->biaya_lain -
                        $request->potongan_pembelian;

                    $bukuBesarPendapatan = BukuBesarPendapatan::create([
                        "tanggal" => $request->tanggal,
                        "uraian" => $request->uraian_pendapatan,
                        "potongan_pembelian" =>
                            $request->potongan_pembelian ?: 0,
                        "piutang_dagang" => $request->jumlah ?: 0,
                        "penjualan_tunai" => 0,
                        "lain_lain" => $request->biaya_lain ?: 0,
                        "uang_diterima" => $saldoFinal,
                        "bunga_bank" => $request->bunga_bank ?: 0,
                        "user_id" => auth()->id(),
                    ]);

                    BukuBesarPiutang::create([
                        "kode" => $saldoTerakhir->kode,
                        "pelanggan_id" => $request->nama_pelanggan,
                        "uraian" => $request->uraian_pendapatan,
                        "tanggal" => $request->tanggal,
                        "debit" => 0,
                        "kredit" => $saldoFinal,
                        "saldo" => $saldoTerakhir->saldo - $saldoFinal,
                        "buku_besar_pendapatan_id" => $bukuBesarPendapatan->id,
                        "user_id" => auth()->id(),
                    ]);

                    $neracaAwalBefore = NeracaAwal::where(
                        "user_id",
                        auth()->id(),
                    )->first();

                    $bukuBesarKas = BukuBesarKas::create([
                        "kode" => $saldoTerakhir->kode,
                        "uraian" => $request->uraian_pendapatan,
                        "tanggal" => $request->tanggal,
                        "debit" => 0,
                        "kredit" => $saldoFinal,
                        "saldo" => $saldoFinal,
                        "neraca_awal_id" => $neracaAwalBefore->id,
                        "neraca_akhir_id" => null,
                        "user_id" => auth()->id(),
                    ]);
                    break;
            }

            if ($request->filled("barang_terjual")) {
                // jangan lupa cek saldo lama nya, query baru get by barang_id
                $detailBarang = Barang::where(
                    "id",
                    $request->barang_terjual,
                )->first();

                $barangItem = KartuGudang::where(
                    "barang_id",
                    $request->barang_terjual,
                )
                    ->latest()
                    ->first();

                // Saldo awal
                $saldoSatuanAwal = $barangItem->saldo_persatuan;
                $saldoKemasanAwal = $barangItem->saldo_perkemasan;
                $unitPerKemasan = $detailBarang->jumlah_unit_per_kemasan;
                $jumlahDijual = $request->jumlah_barang_dijual;

                // Hitung total satuan yang tersedia (dalam satuan kecil)
                $totalSatuanTersedia =
                    $saldoKemasanAwal * $unitPerKemasan + $saldoSatuanAwal;

                // Jika tidak cukup, bisa lempar error
                if ($totalSatuanTersedia < $jumlahDijual) {
                    Log::error("Saldo barang tidak mencukupi");
                    throw new \Exception("Saldo barang tidak mencukupi");
                }

                // Hitung sisa setelah pengeluaran
                $sisaSatuan = $totalSatuanTersedia - $jumlahDijual;

                // Konversi sisa satuan kembali ke kemasan + satuan
                $kemasanBaru = floor(
                    $saldoKemasanAwal - $jumlahDijual / $unitPerKemasan,
                );
                $satuanBaru = $saldoSatuanAwal - $jumlahDijual;

                // Simpan ke Kartu Gudang
                $kartuGudang = KartuGudang::create([
                    "barang_id" => $request->barang_terjual,
                    "tanggal" => $request->tanggal,
                    "diterima" => 0,
                    "dikeluarkan" => $jumlahDijual,
                    "uraian" => $request->uraian_pendapatan,
                    "saldo_persatuan" => $satuanBaru,
                    "saldo_perkemasan" => $kemasanBaru,
                    "buku_besar_pendatapan_id" => $bukuBesarPendapatan->id,
                    "user_id" => auth()->id(),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error("Gagal menyimpan pendapatan", [
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
                "user_id" => auth()->id(),
                "input" => $request->all(),
            ]);

            $pesanError =
                "Terjadi kesalahan saat menyimpan data pendapatan. " .
                "Silakan periksa kembali data yang diisi atau coba beberapa saat lagi.";

            // Kalau error-nya sudah punya pesan khusus (misal dari Exception stok)
            if ($e->getMessage()) {
                $pesanError = $e->getMessage();
            }

            return redirect()->back()->withInput()->with("error", $pesanError);
        }

        return redirect()
            ->route("keuangan.pendapatan.create")
            ->with("success", "Pendapatan berhasil ditambahkan");
    }

    // Tambahkan fungsi destroy di bawah method store
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $pendapatan = BukuBesarPendapatan::where("id", $id)
                ->where("user_id", auth()->id())
                ->firstOrFail();

            // Jika ada relasi dengan BukuBesarPiutang, hapus juga (optional)
            $piutang = BukuBesarPiutang::where("uraian", $pendapatan->uraian)
                ->where("tanggal", $pendapatan->tanggal)
                ->where("user_id", auth()->id())
                ->first();

            if ($piutang) {
                $piutang->delete();
            }

            // Jika ada transaksi stok terkait barang, rollback saldo (optional logic)
            $kartuGudang = KartuGudang::where("uraian", $pendapatan->uraian)
                ->where("tanggal", $pendapatan->tanggal)
                ->where("user_id", auth()->id())
                ->first();

            if ($kartuGudang) {
                $kartuGudang->delete();
            }

            $pendapatan->delete();

            DB::commit();

            return redirect()
                ->route("keuangan.pendapatan.list")
                ->with("success", "Data pendapatan berhasil dihapus.");
        } catch (Exception $e) {
            DB::rollBack();

            Log::error("Gagal menghapus pendapatan", [
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
                "user_id" => auth()->id(),
                "id" => $id,
            ]);

            return redirect()
                ->back()
                ->with(
                    "error",
                    "Terjadi kesalahan saat menghapus data pendapatan.",
                );
        }
    }

    public function createLain()
    {
        return view("keuangan.pendapatan.create_lain");
    }

    public function storeLain(Request $request)
    {
        $validatedData = $request->validate([
            "uraian_pendapatan" => "required|string|max:255",
            "tanggal" => "required|date",
            "jumlah" => "required|numeric",
        ]);

        $bukuBesarPendapatan = BukuBesarPendapatan::create([
            "tanggal" => $request->tanggal,
            "uraian" => $request->uraian_pendapatan,
            "potongan_pembelian" => $request->potongan_pembelian ?? 0,
            "piutang_dagang" => 0,
            "penjualan_tunai" => 0,
            "lain_lain" => $request->jumlah ?? 0,
            "uang_diterima" =>
                ($request->jumlah ?: 0) - ($request->potongan_pembelian ?? 0),
            "bunga_bank" => $request->bunga_bank ?? 0,
            "user_id" => auth()->id(),
        ]);

        return redirect()
            ->route("keuangan.pendapatan.create_lain")
            ->with("success", "Data pendapatan lain berhasil ditambahkan.");
    }
}
