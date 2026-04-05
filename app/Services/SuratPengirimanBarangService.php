<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\{Auth, DB, Log};
use App\Models\SPB\{SuratPengirimanBarang, SuratPengirimanBarangDetail};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class SuratPengirimanBarangService
{
    public function store($data)
    {
        DB::beginTransaction();

        try {
            // Simpan header SPB
            $spb = SuratPengirimanBarang::create([
                "spp_id" => $data["spp_id"],
                "nomor_pengiriman_barang" => $data["nomor_pengiriman_barang"],
                "tanggal_terima" => $data["tanggal_terima"],
                "status_pengiriman" => $data["status_pengiriman"],
                "jenis_pengiriman" => $data["jenis_pengiriman"],
                "keadaan" => $data["keadaan"],
                "keterangan" => $data["keterangan"],
                "nama_penerima" => $data["nama_penerima"],
                "nama_pengirim" => $data["nama_pengirim"],
                "user_id" => auth()->user()->id,
            ]);

            // Simpan detail barang
            if (isset($data["items"]) && is_array($data["items"])) {
                foreach ($data["items"] as $item) {
                    SuratPengirimanBarangDetail::create([
                        "spb_id" => $spb->id,
                        "spp_detail_id" => $item["spp_detail_id"],
                        "jumlah_dikirim" => $item["jumlah_dikirim"] ?? 0,
                    ]);
                }
            }

            DB::commit();
            return $spb;
        } catch (Exception $e) {
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
            $data = SuratPengirimanBarang::with([
                "pesananPembelian.pelanggan",
                "suratPengirimanBarangDetail.pesananPembelianDetail",
            ])
                ->where("user_id", auth()->id())
                ->findOrFail($id);
            $profileUser = Auth::user();

            $pdf = Pdf::loadView(
                "administrasi.surat.surat-pengiriman-barang.template-pdf",
                compact("data", "profileUser"),
            )->setPaper("A4", "portrait");

            return $pdf->download(
                Str::slug("Surat Pengiriman Barang-" .
                    $data->nomor_pengiriman_barang)
                . ".pdf",
            );
        } catch (Exception $e) {
            Log::error("Error generate SPB PDF", [
                "id" => $id,
                "error" => $e->getMessage(),
                "line" => $e->getLine(),
                "file" => $e->getFile(),
            ]);

            return redirect()
                ->back()
                ->with("error", "Gagal mendownload PDF SPB!");
        }
    }

    public function update($id, $data)
    {
        DB::beginTransaction();
        try {
            $spb = SuratPengirimanBarang::where("id", $id)
                ->where("user_id", auth()->id())
                ->firstOrFail();
            $spb->update($data);
            DB::commit();
            return true;
        } catch (Exception $e) {
            Log::error("Failed updating SPB: ", [
                "spb_id" => $id,
                "error" => $e->getMessage(),
                "line" => $e->getLine(),
                "file" => $e->getFile(),
            ]);
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $spb = SuratPengirimanBarang::where("id", $id)
                ->where("user_id", auth()->id())
                ->firstOrFail();
            $spb->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Gagal menghapus SPB", [
                "spb_id" => $id,
                "user_id" => auth()->id(),
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
