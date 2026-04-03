<?php

namespace App\Services;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\{Auth, DB, Log};
use App\Models\Faktur\FakturPenjualan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class AdministrasiFakturService
{
    public function store($data)
    {
        DB::beginTransaction();
        try {
            $faktur = FakturPenjualan::create([
                "spb_id" => $data["spb_id"],
                "kode_faktur" => $data["kode_faktur"],
                "tanggal_faktur" => $data["tanggal_faktur"],
                "nama_bagian_penjualan" => $data['bagian_penjualan'],
                "user_id" => auth()->user()->id,
            ]);
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
                Str::slug("Faktur Penjualan-" .
                    $faktur->kode_faktur)
                . ".pdf",
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
            $faktur = FakturPenjualan::where("user_id", auth()->id())
                ->findOrFail($id);
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