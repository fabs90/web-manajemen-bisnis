<?php

namespace App\Services;

use App\Models\Faktur\FakturPenjualan;
use App\Models\SPB\SuratPengirimanBarang;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuratPengirimanBarangService
{
    public function store($data)
    {
        DB::beginTransaction();
        try {
            $spb = SuratPengirimanBarang::create([
                "faktur_penjualan_id" => $data["faktur_id"],
                "nomor_surat" => $data["nomor_surat"],
                "user_id" => auth()->id(),
                "tanggal_barang_diterima" => $data["tanggal_barang_diterima"],
                "keadaan" => $data["keadaan"],
                "keterangan" => $data["keterangan"],
                "nama_penerima" => $data["nama_penerima"],
                "nama_pengirim" => $data["nama_pengirim"],
            ]);
            $spb->save();
            DB::commit();
            return $spb;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error creating Surat Pengiriman Barang", [
                "error" => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function generatePdf($id)
    {
        try {
            $data = SuratPengirimanBarang::with(["fakturPenjualan"])
                ->where("user_id", auth()->id())
                ->findOrFail($id);
            $profileUser = Auth::user();
            $pdf = Pdf::loadView(
                "administrasi.surat.surat-pengiriman-barang.template-pdf",
                compact("data", "profileUser"),
            );

            return $pdf->download(
                "surat-pengiriman-barang-" . $data->id . ".pdf",
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Terjadi error saat generate pdf: " . $e->getMessage());
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $spb = SuratPengirimanBarang::findOrFail($id);
            $spb->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception(
                "Gagal menghapus Surat Pengiriman Barang! " . $e->getMessage(),
            );
        }
    }
}
