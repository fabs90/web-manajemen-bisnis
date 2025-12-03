<?php

namespace App\Http\Controllers\SPB;

use App\Http\Controllers\Controller;
use App\Models\Faktur\FakturPenjualan;
use App\Models\SPB\SuratPengirimanBarang;
use App\Models\SPP\PesananPembelian;
use App\Services\SuratPengirimanBarangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SuratPengirimanBarangController extends Controller
{
    public function index()
    {
        $suratPengirimanBarang = SuratPengirimanBarang::with([
            "pesananPembelian",
            "pesananPembelian.pelanggan",
            "user",
        ])
            ->latest()
            ->get();

        return view(
            "administrasi.surat.surat-pengiriman-barang.index",
            compact("suratPengirimanBarang"),
        );
    }

    public function create()
    {
        $dataSpp = PesananPembelian::with([
            "pelanggan",
            "pesananPembelianDetail",
        ])
            ->where("user_id", auth()->id())
            ->whereDoesntHave("suratPengirimanBarang")
            ->latest()
            ->get();

        return view(
            "administrasi.surat.surat-pengiriman-barang.create",
            compact("dataSpp"),
        );
    }

    public function generatePdf($id)
    {
        try {
            $srvice = new SuratPengirimanBarangService();
            return $srvice->generatePdf($id);
        } catch (Throwable $th) {
            Log::error(
                "Gagal generate PDF Surat Pengiriman): " . $th->getMessage(),
            );
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Gagal generate PDF Surat Pengiriman Barang (SPB)",
                );
        }
    }

    public function store(Request $request)
    {
        try {
            $service = app(SuratPengirimanBarangService::class);
            $service->store($request->all());
            return redirect()
                ->route("administrasi.spb.index")
                ->with(
                    "success",
                    "Surat Pengiriman Barang (SPB) berhasil ditambahkan.",
                );
        } catch (\Throwable $th) {
            Log::error(
                "Gagal menambahkan Surat Pengiriman Barang (SPB): " .
                    $th->getMessage(),
            );
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Gagal menambahkan Surat Pengiriman Barang (SPB): " .
                        $th->getMessage(),
                );
        }
    }

    public function destroy($id)
    {
        try {
            $service = app(SuratPengirimanBarangService::class);
            $service->destroy($id);
            return redirect()
                ->route("administrasi.spb.index")
                ->with(
                    "success",
                    "Surat Pengiriman Barang (SPB) berhasil dihapus.",
                );
        } catch (\Throwable $th) {
            Log::error(
                "Gagal menghapus Surat Pengiriman Barang (SPB): " .
                    $th->getMessage(),
            );
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Gagal menghapus Surat Pengiriman Barang (SPB): " .
                        $th->getMessage(),
                );
        }
    }
}
