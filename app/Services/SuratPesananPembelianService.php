<?php

namespace App\Services;

use App\Models\SPP\PesananPembelian;
use App\Models\SPP\PesananPembelianDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuratPesananPembelianService
{
    public function store($request)
    {
        DB::beginTransaction();
        try {
            $suratPesananPembelian = PesananPembelian::create([
                "pelanggan_id" => $request->pelanggan_id,
                "nomor_pesanan_pembelian" => $request->nomor_pesanan_pembelian,
                "tanggal_pesanan_pembelian" =>
                    $request->tanggal_pesanan_pembelian,
                "tanggal_kirim_pesanan_pembelian" =>
                    $request->tanggal_kirim_pesanan_pembelian,
                "nama_bagian_pembelian" => $request->nama_bagian_pembelian,
                "user_id" => auth()->id(),
            ]);
            foreach ($request->detail as $item) {
                PesananPembelianDetail::create([
                    "spp_id" => $suratPesananPembelian->id,
                    "nama_barang" => $item["nama_barang"],
                    "kuantitas" => $this->cleanRupiah($item["kuantitas"]),
                    "harga" => $this->cleanRupiah($item["harga"]),
                    "diskon" => $item["diskon"] ?? 0,
                    "total" => $this->cleanRupiah($item["total"]),
                ]);
            }

            DB::commit();
            return $suratPesananPembelian;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($sppId)
    {
        try {
            $suratPesananPembelian = PesananPembelian::where(
                "user_id",
                auth()->user()->id,
            )->findOrFail($sppId);
            $suratPesananPembelian->pesananPembelianDetail()->delete();
            $suratPesananPembelian->delete();
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function generatePdf($sppId)
    {
        try {
            $data = PesananPembelian::with(
                "pelanggan",
                "pesananPembelianDetail",
            )
                ->where("user_id", auth()->user()->id)
                ->findOrFail($sppId);

            $profileUser = Auth::user();

            $pdf = Pdf::loadView(
                "administrasi.surat.surat-pesanan-pembelian.template-pdf",
                compact("data", "profileUser"),
            )->setPaper("A4", "portrait");

            return $pdf->download(
                "surat-pesanan-pembelian-" .
                    $data->nomor_pesanan_pembelian .
                    ".pdf",
            );
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function cleanRupiah(string|int $value): int
    {
        return (int) preg_replace("/\D/", "", $value);
    }
}
