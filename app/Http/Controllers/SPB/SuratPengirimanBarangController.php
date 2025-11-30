<?php

namespace App\Http\Controllers\SPB;

use App\Http\Controllers\Controller;
use App\Models\Faktur\FakturPenjualan;
use App\Services\SuratPengirimanBarangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SuratPengirimanBarangController extends Controller
{
    public function index()
    {
        $dataFaktur = FakturPenjualan::with(
            "fakturPenjualanDetail",
            "suratPengirimanBarang",
        )
            ->where("user_id", auth()->id())
            ->get();
        return view(
            "administrasi.surat.surat-pengiriman-barang.index",
            compact("dataFaktur"),
        );
    }

    public function create()
    {
        $dataFaktur = FakturPenjualan::with(
            "fakturPenjualanDetail",
            "suratPengirimanBarang",
        )
            ->whereDoesntHave("suratPengirimanBarang")
            ->where("user_id", auth()->id())
            ->get();

        return view(
            "administrasi.surat.surat-pengiriman-barang.create",
            compact("dataFaktur"),
        );
    }

    public function getDetail($id)
    {
        $faktur = FakturPenjualan::with("fakturPenjualanDetail")
            ->where("user_id", auth()->id())
            ->findOrFail($id);

        return response()->json([
            "faktur" => $faktur,
            "details" => $faktur->fakturPenjualanDetail,
        ]);
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
                    "Gagal generate PDF Surat Pengiriman Barang (SPB): " .
                        $th->getMessage(),
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
