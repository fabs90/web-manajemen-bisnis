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

        $allDatas = BukuBesarPendapatan::where("user_id", $userId)->get();
        $dataPiutang = \App\Models\BukuBesarPiutang::where("user_id", $userId)
            ->with("pelanggan:id,nama")
            ->get()
            ->groupBy("pelanggan_id");

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
            $kodeTransaksi = Str::uuid();

            switch ($request->jenis_pendapatan) {
                case "tunai":
                    $saldoFinal =
                        $request->biaya_lain +
                        $request->jumlah -
                        ($request->potongan_pembelian
                            ? array_sum($request->potongan_pembelian)
                            : 0);

                    $bukuBesarPendapatan = BukuBesarPendapatan::create([
                        "kode" => $kodeTransaksi,
                        "tanggal" => $request->tanggal,
                        "uraian" => $request->uraian_pendapatan,
                        "potongan_pembelian" => $request->potongan_pembelian
                            ? array_sum($request->potongan_pembelian)
                            : 0,
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

                    if (!$neracaAwalBefore) {
                        throw new \Exception("Data Neraca Awal belum ditemukan.
                    Silakan buat Neraca Awal terlebih dahulu sebelum menambahkan pendapatan.");
                    }

                    $bukuBesarKasBefore = BukuBesarKas::where(
                        "user_id",
                        auth()->id(),
                    )
                        ->latest()
                        ->first();

                    $saldoBaru =
                        ($bukuBesarKasBefore->saldo ?? 0) + $saldoFinal;

                    BukuBesarKas::create([
                        "kode" => $kodeTransaksi,
                        "uraian" => $request->uraian_pendapatan,
                        "tanggal" => $request->tanggal,
                        "debit" => $saldoFinal,
                        "kredit" => 0,
                        "saldo" => $saldoBaru,
                        "neraca_awal_id" => $neracaAwalBefore->id,
                        "neraca_akhir_id" => null,
                        "user_id" => auth()->id(),
                    ]);
                    break;

                case "piutang":
                    $piutangTambahan = $request->filled("jumlah_piutang")
                        ? $request->jumlah_piutang
                        : 0;
                    $saldoFinal =
                        $request->jumlah +
                        $piutangTambahan +
                        $request->biaya_lain -
                        ($request->potongan_pembelian
                            ? array_sum($request->potongan_pembelian)
                            : 0);

                    $bukuBesarPendapatan = BukuBesarPendapatan::create([
                        "kode" => $kodeTransaksi,
                        "tanggal" => $request->tanggal,
                        "uraian" => $request->uraian_pendapatan,
                        "potongan_pembelian" => $request->potongan_pembelian
                            ? array_sum($request->potongan_pembelian)
                            : 0, // Sum potongan
                        "piutang_dagang" => $request->jumlah,
                        "penjualan_tunai" => 0,
                        "lain_lain" => $request->biaya_lain ?? 0,
                        "uang_diterima" => $saldoFinal,
                        "bunga_bank" => $request->bunga_bank ?? 0,
                        "user_id" => auth()->id(),
                    ]);

                    $piutangAktif = $validated["piutang_aktif"] ?? null;
                    if ($piutangAktif) {
                        // Tambah saldo piutang lama
                        $piutangLama = BukuBesarPiutang::where(
                            "kode",
                            $piutangAktif,
                        )->first();
                        if ($piutangLama) {
                            BukuBesarPiutang::create([
                                "kode" => $piutangLama->kode,
                                "pelanggan_id" => $piutangLama->pelanggan_id,
                                "debit" => $validated["jumlah"],
                                "kredit" => 0,
                                "saldo" => $piutangLama->saldo + $saldoFinal,
                                "uraian" => $validated["uraian_pendapatan"],
                                "tanggal" => $validated["tanggal"],
                                "buku_besar_pendapatan_id" =>
                                    $bukuBesarPendapatan->id,
                                "user_id" => auth()->id(),
                            ]);
                        }
                    } else {
                        // Piutang baru
                        BukuBesarPiutang::create([
                            "kode" => Str::uuid(),
                            "pelanggan_id" => $validated["nama_pelanggan"],
                            "debit" => $validated["jumlah"],
                            "kredit" => 0,
                            "saldo" => $saldoFinal,
                            "uraian" => $validated["uraian_pendapatan"],
                            "tanggal" => $validated["tanggal"],
                            "buku_besar_pendapatan_id" =>
                                $bukuBesarPendapatan->id,
                            "user_id" => auth()->id(),
                        ]);
                    }

                    $neracaAwalBefore = NeracaAwal::where(
                        "user_id",
                        auth()->id(),
                    )->first();

                    if (!$neracaAwalBefore) {
                        throw new \Exception("Data Neraca Awal belum ditemukan.
                    Silakan buat Neraca Awal terlebih dahulu sebelum menambahkan pendapatan.");
                    }

                    $bukuBesarKasBefore = BukuBesarKas::where(
                        "user_id",
                        auth()->id(),
                    )
                        ->latest()
                        ->first();

                    // 🔹 Kas tidak berubah karena piutang belum diterima
                    BukuBesarKas::create([
                        "kode" => $kodeTransaksi,
                        "uraian" =>
                            "Penjualan Piutang: " . $request->uraian_pendapatan,
                        "tanggal" => $request->tanggal,
                        "debit" => 0,
                        "kredit" => 0,
                        "saldo" => $bukuBesarKasBefore->saldo ?? 0,
                        "neraca_awal_id" => $neracaAwalBefore->id,
                        "user_id" => auth()->id(),
                    ]);
                    break;

                case "kredit":
                    // Ambil data piutang lama (yang dibayar oleh debitur)
                    $piutangLama = BukuBesarPiutang::where(
                        "kode",
                        $validated["hutang_aktif"],
                    )
                        ->latest()
                        ->first();

                    if (!$piutangLama) {
                        throw new \Exception(
                            "Data piutang tidak ditemukan untuk pelunasan.",
                        );
                    }

                    $kreditTambahan = $request->filled("jumlah_kredit")
                        ? $request->jumlah_kredit
                        : 0;

                    $saldoFinal =
                        $request->jumlah +
                        $kreditTambahan +
                        $request->biaya_lain -
                        ($request->potongan_pembelian
                            ? array_sum($request->potongan_pembelian)
                            : 0);

                    // Catat di Buku Besar Pendapatan (karena ada uang masuk)
                    $bukuBesarPendapatan = BukuBesarPendapatan::create([
                        "kode" => $kodeTransaksi,
                        "tanggal" => $request->tanggal,
                        "uraian" =>
                            "Pelunasan Piutang: " . $request->uraian_pendapatan,
                        "potongan_pembelian" => $request->potongan_pembelian
                            ? array_sum($request->potongan_pembelian)
                            : 0, // Sum potongan
                        "piutang_dagang" => $saldoFinal,
                        "penjualan_tunai" => 0,
                        "lain_lain" => $request->biaya_lain ?: 0,
                        "uang_diterima" => $saldoFinal,
                        "bunga_bank" => $request->bunga_bank ?: 0,
                        "user_id" => auth()->id(),
                    ]);

                    // Di sini piutang berkurang (kredit bertambah)
                    $saldoBaruPiutang = $piutangLama->saldo - $saldoFinal;
                    if ($saldoBaruPiutang < 0) {
                        throw new \Exception(
                            "Nilai pelunasan melebihi saldo piutang.",
                        );
                    }

                    BukuBesarPiutang::create([
                        "kode" => $piutangLama->kode, // tetap pakai kode piutang lama
                        "pelanggan_id" => $piutangLama->pelanggan_id,
                        "uraian" =>
                            "Pelunasan Piutang: " . $request->uraian_pendapatan,
                        "tanggal" => $request->tanggal,
                        "debit" => 0,
                        "kredit" => $saldoFinal,
                        "saldo" => $saldoBaruPiutang,
                        "buku_besar_pendapatan_id" => $bukuBesarPendapatan->id,
                        "user_id" => auth()->id(),
                    ]);

                    // 🔹 Kas bertambah karena uang masuk
                    $neracaAwalBefore = NeracaAwal::where(
                        "user_id",
                        auth()->id(),
                    )->first();

                    if (!$neracaAwalBefore) {
                        throw new \Exception("Data Neraca Awal belum ditemukan.
                    Silakan buat Neraca Awal terlebih dahulu sebelum menambahkan pendapatan.");
                    }

                    $bukuBesarKasBefore = BukuBesarKas::where(
                        "user_id",
                        auth()->id(),
                    )
                        ->latest()
                        ->first();

                    $saldoBaruKas =
                        ($bukuBesarKasBefore->saldo ?? 0) + $saldoFinal;

                    BukuBesarKas::create([
                        "kode" => $kodeTransaksi,
                        "uraian" =>
                            "Penerimaan dari Pelunasan Piutang (" .
                            $request->uraian_pendapatan .
                            ")",
                        "tanggal" => $request->tanggal,
                        "debit" => $saldoFinal, // kas bertambah
                        "kredit" => 0,
                        "saldo" => $saldoBaruKas,
                        "neraca_awal_id" => $neracaAwalBefore->id,
                        "user_id" => auth()->id(),
                    ]);
                    break;
            }

            if (
                $request->filled("barang_terjual") &&
                is_array($request->barang_terjual)
            ) {
                foreach ($request->barang_terjual as $index => $barangId) {
                    if (!$barangId) {
                        continue;
                    } // Skip jika null

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

                    // Saldo awal
                    $saldoSatuanAwal = $barangItem->saldo_persatuan;
                    $saldoKemasanAwal = $barangItem->saldo_perkemasan;
                    $unitPerKemasan = $detailBarang->jumlah_unit_per_kemasan;
                    $jumlahDijual = $request->jumlah_barang_dijual[$index] ?? 0;

                    // Hitung total satuan yang tersedia (dalam satuan kecil)
                    $totalSatuanTersedia =
                        $saldoKemasanAwal * $unitPerKemasan + $saldoSatuanAwal;

                    // Jika tidak cukup, lempar error
                    if ($totalSatuanTersedia < $jumlahDijual) {
                        throw new \Exception(
                            "Saldo barang '{$detailBarang->nama}' tidak mencukupi. Tersedia: {$totalSatuanTersedia}, Dibutuhkan: {$jumlahDijual}",
                        );
                    }

                    // Hitung sisa setelah pengeluaran
                    $sisaSatuan = $totalSatuanTersedia - $jumlahDijual;

                    // Konversi sisa satuan kembali ke kemasan + satuan
                    $kemasanBaru = floor($sisaSatuan / $unitPerKemasan);
                    $satuanBaru = $saldoSatuanAwal - $jumlahDijual;

                    // Simpan ke Kartu Gudang
                    KartuGudang::create([
                        "barang_id" => $barangId,
                        "tanggal" => $request->tanggal,
                        "diterima" => 0,
                        "dikeluarkan" => $jumlahDijual,
                        "uraian" =>
                            $request->uraian_pendapatan .
                            " - " .
                            $detailBarang->nama,
                        "saldo_persatuan" => $satuanBaru,
                        "saldo_perkemasan" => $kemasanBaru,
                        "buku_besar_pendapatan_id" => $bukuBesarPendapatan->id,
                        "user_id" => auth()->id(),
                    ]);
                }
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
