<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\SPP\SuratPesananPenjualan;
use App\Models\SPP\SuratPesananPenjualanDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuratPesananPenjualanService
{
    public function __construct(protected FileUploadService $fileUploadService) {}

    public function storePelanggan($request): SuratPesananPenjualan
    {
        DB::beginTransaction();
        try {
            $isRequest = $request instanceof Request;

            $pelangganId = $isRequest ? $request->pelanggan_id : $request['pelanggan_id'];
            $nomorPesanan = $isRequest ? $request->nomor_pesanan_pembelian : $request['nomor_pesanan_pembelian'];
            $tanggalPesanan = $isRequest ? $request->tanggal_pesanan_pembelian : $request['tanggal_pesanan_pembelian'];
            $tanggalKirim = $isRequest ? ($request->tanggal_kirim_pesanan_pembelian ?? $request->tanggal_pesanan_pembelian) : ($request['tanggal_kirim_pesanan_pembelian'] ?? $request['tanggal_pesanan_pembelian']);
            $namaBagian = $isRequest ? ($request->nama_pelanggan ?? null) : ($request['nama_pelanggan'] ?? null);
            $detailItems = $isRequest ? $request->detail : $request['detail'];

            $suratPesananPenjualan = SuratPesananPenjualan::create([
                'pelanggan_id' => $pelangganId,
                'jenis' => 'transaksi_masuk',
                'nomor_pesanan_penjualan' => $nomorPesanan,
                'tanggal_pesanan_penjualan' => $tanggalPesanan,
                'tanggal_kirim_pesanan_penjualan' => $tanggalKirim,
                'nama_bagian_pembelian' => $namaBagian,
                'ttd_bagian_pembelian' => null,
                'user_id' => auth()->id(),
            ]);

            $totalSalesAmount = 0;
            $totalHppAmount = 0;

            if (isset($detailItems) && is_array($detailItems)) {
                foreach ($detailItems as $item) {
                    $kuantitas = $this->cleanRupiah($item['kuantitas']);
                    $harga = $this->cleanRupiah($item['harga']);
                    $diskon = $item['diskon'] ?? 0;
                    $total = $this->cleanRupiah($item['total']);

                    $totalSalesAmount += $total;

                    SuratPesananPenjualanDetail::create([
                        'pesanan_penjualan_id' => $suratPesananPenjualan->id,
                        'barang_id' => $item['barang_id'] ?? null,
                        'nama_barang' => $item['nama_barang'],
                        'kuantitas' => $kuantitas,
                        'harga' => $harga,
                        'diskon' => $diskon,
                        'total' => $total,
                    ]);

                    // Hapus logika Update Stok & Kartu Gudang dari SPP
                    // Stok dikurangkan saat membuat Surat Pengiriman Barang (SPB)
                }
            }

            // Hapus logika Journaling dari SPP
            // Penjurnalan (Piutang, Penjualan, dll) dilakukan saat membuat Faktur Penjualan

            DB::commit();

            return $suratPesananPenjualan;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($sppId): bool
    {
        DB::beginTransaction();
        try {
            $suratPesananPenjualan = SuratPesananPenjualan::with('details')
                ->where('user_id', auth()->id())
                ->findOrFail($sppId);

            // Hapus SPB (cascade) - harusnya di model ada event delete tapi kita hapus manual jika ada
            if ($suratPesananPenjualan->suratPengirimanBarang && $suratPesananPenjualan->suratPengirimanBarang->count() > 0) {
                foreach ($suratPesananPenjualan->suratPengirimanBarang as $spb) {
                    app(SuratPengirimanBarangService::class)->destroy($spb->id);
                }
            }

            // Hapus detail SPP setelah SPB dihapus (karena SPB butuh data detail SPP untuk reversal stok)
            $suratPesananPenjualan->details()->delete();

            // Hapus SPP Pelanggan
            $suratPesananPenjualan->delete();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function cleanRupiah(string|int $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        return (int) preg_replace("/\D/", '', $value);
    }
}
