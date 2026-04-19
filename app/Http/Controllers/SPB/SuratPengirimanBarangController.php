<?php

namespace App\Http\Controllers\SPB;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuratPengirimanBarangRequest;
use App\Models\SPB\SuratPengirimanBarang;
use App\Models\SPP\PesananPembelian;
use App\Services\SuratPengirimanBarangService;
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
    public function createTransaksiKeluar()
    {
        $dataSpp = PesananPembelian::with([
            "supplier",
            "pesananPembelianDetail",
        ])
            ->where("user_id", auth()->id())
            ->where('jenis', 'transaksi_keluar')
            ->whereNotNull("supplier_id")
            ->whereDoesntHave("suratPengirimanBarang")
            ->latest()
            ->get();

        return view('administrasi.surat.surat-pengiriman-barang.create-transaksi-keluar', compact('dataSpp'));
    }

    public function createTransaksiMasuk()
    {
        $dataSpp = PesananPembelian::with([
            "pelanggan",
            "pesananPembelianDetail",
        ])
            ->where("user_id", auth()->id())
            ->where("jenis", "transaksi_masuk")
            ->whereNotNull("pelanggan_id")
            ->whereDoesntHave("suratPengirimanBarang")
            ->latest()
            ->get();
        return view('administrasi.surat.surat-pengiriman-barang.create-transaksi-masuk', compact('dataSpp'));
    }

    public function editTransaksiMasuk($id)
    {
        $dataSpb = SuratPengirimanBarang::with([
            "pesananPembelian",
            "suratPengirimanBarangDetail.pesananPembelianDetail",
        ])->where("id", $id)->first();
        return view('administrasi.surat.surat-pengiriman-barang.edit', compact('dataSpb'));
    }

    public function generatePdf($id)
    {
        try {
            $service = app(SuratPengirimanBarangService::class);
            return $service->generatePdf($id);
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

    public function store(SuratPengirimanBarangRequest $request)
    {
        $data = $request->validated();
        try {
            $service = app(SuratPengirimanBarangService::class);
            $service->store($data);
            return redirect()
                ->route("administrasi.spb.index")
                ->with(
                    "success",
                    "Surat Pengiriman Barang (SPB) berhasil ditambahkan.",
                );
        } catch (Throwable $th) {
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

    public function update(SuratPengirimanBarangRequest $request, $id)
    {
        $data = $request->validated();
        try {
            $service = app(SuratPengirimanBarangService::class);
            $service->update($id, $data);
            return redirect()
                ->route("administrasi.spb.index")
                ->with(
                    "success",
                    "Surat Pengiriman Barang (SPB) berhasil diubah.",
                );
        } catch (Throwable $th) {
            Log::error(
                "Gagal mengubah Surat Pengiriman Barang (SPB): " .
                $th->getMessage(),
            );
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Gagal mengubah Surat Pengiriman Barang (SPB): " .
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