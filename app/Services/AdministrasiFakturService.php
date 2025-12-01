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
                "kode_faktur" => $data["kode_faktur"],
                "pelanggan_id" => $data["pelanggan_id"],
                "nomor_pesanan" => $data["nomor_pesanan"] ?? null,
                "nomor_spb" => $data["nomor_spb"] ?? null,
                "tanggal" => $data["tanggal"],
                "jenis_pengiriman" => $data["jenis_pengiriman"] ?? null,
                "nama_bagian_penjualan" =>
                    $data["nama_bagian_penjualan"] ?? null,
                "user_id" => auth()->id(),
            ]);
            // Simpan detail faktur
            foreach ($data["detail"] as $row) {
                FakturPenjualanDetail::create([
                    "faktur_penjualan_id" => $faktur->id,
                    "jumlah_dipesan" => $row["jumlah_dipesan"] ?? 0,
                    "jumlah_dikirim" => $row["jumlah_dikirim"] ?? 0,
                    "nama_barang" => $row["nama_barang"],
                    "harga" => $row["harga"] ?? 0,
                    "diskon" => $row["diskon"] ?? 0,
                    "total" => $row["total"] ?? 0,
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
            $faktur = FakturPenjualan::with(["fakturPenjualanDetail"])
                ->where("user_id", auth()->id())
                ->findOrFail($id);
            $profileUser = Auth::user();
            $pdf = Pdf::loadView(
                "administrasi.surat.faktur-penjualan.template-pdf",
                compact("faktur", "profileUser"),
            );

            return $pdf->download("faktur-penjualan-" . $faktur->id . ".pdf");
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
            $faktur = FakturPenjualan::where("user_id", auth()->id())->find(
                $id,
            );
            if (!$faktur) {
                throw new ModelNotFoundException("Faktur not found");
            }
            // delete faktur detai
            $faktur->fakturPenjualanDetail()->delete();
            $faktur->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}
