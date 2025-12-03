<?php

namespace App\Services;

use App\Models\Faktur\FakturPenjualan;
use App\Models\Faktur\FakturPenjualanDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdministrasiFakturService
{
    public function store($data)
    {
        DB::beginTransaction();
        try {
            // Simpan header faktur
            $faktur = FakturPenjualan::create([
                "spb_id" => $data["spb_id"],
                "kode_faktur" => $data["kode_faktur"],
                "tanggal_faktur" => $data["tanggal_faktur"],
                "nama_bagian_penjualan" => $data["bagian_penjualan"],
                "user_id" => auth()->user()->id,
            ]);
            // Simpan detail faktur
            foreach ($data["items"] as $row) {
                FakturPenjualanDetail::create([
                    "faktur_penjualan_id" => $faktur->id,
                    "spb_detail_id" => $row["spb_detail_id"],
                    "harga" => $row["harga"],
                    "total" => $row["total"],
                ]);
            }
            DB::commit();
            return $faktur;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function generatePdf($id)
    {
        try {
            $faktur = FakturPenjualan::with([
                "fakturPenjualanDetail.suratPengirimanBarangDetail",
                "suratPengirimanBarang",
                "suratPengirimanBarang.pesananPembelian.pelanggan",
            ])
                ->where("user_id", auth()->id())
                ->findOrFail($id);

            $profileUser = Auth::user();

            $pdf = Pdf::loadView(
                "administrasi.surat.faktur-penjualan.template-pdf",
                compact("faktur", "profileUser"),
            )->setPaper("A4", "portrait");

            return $pdf->download(
                "faktur-penjualan-" . $faktur->kode_faktur . ".pdf",
            );
        } catch (\Exception $e) {
            Log::error("Error generate PDF faktur: " . $e->getMessage());
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Ambil faktur berdasarkan ID dan user yang sedang login
            $faktur = FakturPenjualan::where("user_id", auth()->id())
                ->with("fakturPenjualanDetail")
                ->findOrFail($id);

            // Hapus detail faktur
            $faktur->fakturPenjualanDetail()->delete();

            // Hapus header faktur
            $faktur->delete();

            DB::commit();
            return true;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning(
                "Faktur tidak ditemukan saat destroy: ID $id | User: " .
                    auth()->id(),
            );
            return false;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menghapus faktur: " . $e->getMessage());
            return false;
        }
    }
}
