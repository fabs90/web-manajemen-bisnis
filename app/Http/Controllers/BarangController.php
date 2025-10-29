<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BukuBesarPendapatan;
use App\Models\KartuGudang;
use Illuminate\Http\Request;
use Log;

class BarangController extends Controller
{
    public function index()
    {
        $barang = Barang::where("user_id", auth()->id())->get();

        return view("barang.index", compact("barang"));
    }

    public function create()
    {
        return view("barang.create");
    }

    public function store(Request $request)
    {
        $request->validate([
            "kode_barang" =>
                "nullable|string|max:100|unique:barang,kode_barang",
            "nama" => "required|string|max:255",
            "jumlah_max" => "required|integer|min:0",
            "jumlah_min" => "required|integer|min:0",
            "jumlah_unit_per_kemasan" => "required|integer|min:0",
            "harga_beli_per_kemas" => "required|numeric|min:0",
            "harga_beli_per_unit" => "required|numeric|min:0",
            "harga_jual_per_unit" => "required|numeric|min:0",
        ]);

        try {
            $data = Barang::create([
                "kode_barang" => $request->input("kode_barang"),
                "nama" => $request->input("nama"),
                "jumlah_max" => $request->input("jumlah_max"),
                "jumlah_min" => $request->input("jumlah_min"),
                "jumlah_unit_per_kemasan" => $request->input(
                    "jumlah_unit_per_kemasan",
                ),
                "harga_beli_per_kemas" => $request->input(
                    "harga_beli_per_kemas",
                ),
                "harga_beli_per_unit" => $request->input("harga_beli_per_unit"),
                "harga_jual_per_unit" => $request->input("harga_jual_per_unit"),
                "user_id" => auth()->id(),
            ]);

            if (!$data) {
                return back()
                    ->withErrors([
                        "error" =>
                            "Terjadi kesalahan saat menyimpan data barang.",
                    ])
                    ->withInput();
            }

            return redirect()
                ->route("barang.create")
                ->with("success", "Barang berhasil ditambahkan.");
        } catch (\Exception $e) {
            Log::error("Gagal menyimpan barang: " . $e->getMessage());
            return back()
                ->with([
                    "error" => "Terjadi kesalahan saat menyimpan data barang.: {$e->getMessage()}",
                ])
                ->withInput();
        }
    }

    public function indexKartuGudang()
    {
        $barang = Barang::where("user_id", auth()->id())->get();
        $kartuGudang = KartuGudang::where("user_id", auth()->id())->get();

        // $saldoMinimumUnit = $barang->jumlah_min * $barang->jumlah_unit_per_kemasan;
        // if($saldoPersatuan <= $saldoMinimumUnit) {
        //     muncul warning
        // }

        return view("kartu-gudang.index", compact("barang", "kartuGudang"));
    }

    public function createKartuGudang($barang_id)
    {
        $barang = Barang::find($barang_id);
        return view("kartu-gudang.create", compact("barang"));
    }

    public function storeKartuGudang(Request $request, $barangId)
    {
        $validate = $request->validate([
            "tanggal" => "required|date",
            "uraian" => "required|string",
            "diterima" => "required|integer",
            "dikeluarkan" => "required|integer",
            "saldo_persatuan" => "required|integer",
        ]);

        try {
            $data = KartuGudang::create([
                "user_id" => auth()->id(),
                "barang_id" => $barangId,
                "tanggal" => $request->tanggal,
                "uraian" => $request->uraian,
                "diterima" => $request->saldo_persatuan,
                "dikeluarkan" => $request->dikeluarkan,
                "saldo_persatuan" => $request->saldo_persatuan,
                "saldo_perkemasan" => $request->diterima,
            ]);

            return redirect()
                ->route("kartu-gudang.create", ["barang_id" => $barangId])
                ->with(
                    "success",
                    "Kartu gudang {{$data->barang->nama}} berhasil ditambahkan.",
                );
        } catch (\Exception $e) {
            return back()
                ->withErrors([
                    "error" => "Terjadi kesalahan saat menyimpan data kartu gudang.: {$e->getMessage()}",
                ])
                ->withInput();
        }
    }
}
