<?php

namespace App\Services;

use App\Models\SPB\SuratPengirimanBarang;
use App\Models\SPB\SuratPengirimanBarangDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SuratPengirimanBarangService
{
    public function __construct(protected FileUploadService $fileUploadService)
    {
    }

    public function store($data)
    {
        DB::beginTransaction();
        try {
            $userEmail = Auth::user()->email ?? 'system@email.com';

            // Handle TTD Pengirim
            $ttdPengirimPath = null;
            if (request()->hasFile('ttd_pengirim')) {
                $ttdPengirimPath = $this->fileUploadService->upload(
                    request()->file('ttd_pengirim'),
                    'surat/spb/ttd_pengirim',
                    $userEmail
                );
            }

            // Handle TTD Penerima
            $ttdPenerimaPath = null;
            if (request()->hasFile('ttd_penerima')) {
                $ttdPenerimaPath = $this->fileUploadService->upload(
                    request()->file('ttd_penerima'),
                    'surat/spb/ttd_penerima',
                    $userEmail
                );
            }


            // Simpan header SPB
            $spb = SuratPengirimanBarang::create([
                'spp_id' => null,
                'pesanan_penjualan_id' => $data['spp_id'] ?? null,
                'nomor_pengiriman_barang' => $data['nomor_pengiriman_barang'],
                'tanggal_terima' => $data['tanggal_terima'],
                'status_pengiriman' => $data['status_pengiriman'],
                'jenis_pengiriman' => $data['jenis_pengiriman'],
                'keadaan' => $data['keadaan'],
                'keterangan' => $data['keterangan'],
                'nama_penerima' => $data['nama_penerima'],
                'nama_pengirim' => $data['nama_pengirim'],
                'ttd_pengirim' => $ttdPengirimPath,
                'ttd_penerima' => $ttdPenerimaPath,
                'user_id' => auth()->user()->id,
            ]);

            // Simpan detail barang
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    SuratPengirimanBarangDetail::create([
                        'spb_id' => $spb->id,
                        'spp_detail_id' => null,
                        'pesanan_penjualan_detail_id' => $item['spp_detail_id'],
                        'jumlah_dikirim' => $item['jumlah_dikirim'] ?? 0,
                    ]);
                }
            }

            DB::commit();

            return $spb;
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error creating Surat Pengiriman Barang', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function generatePdf($id)
    {
        try {
            $data = SuratPengirimanBarang::with([
                'pesananPembelian.pelanggan',
                'pesananPembelian',
                'pesananPenjualan.pelanggan',
                'pesananPenjualan',
                'suratPengirimanBarangDetail.pesananPembelianDetail',
                'suratPengirimanBarangDetail.pesananPenjualanDetail',
            ])
                ->where('user_id', auth()->id())
                ->findOrFail($id);
            $profileUser = Auth::user();

            $pdf = Pdf::loadView(
                'administrasi.surat.surat-pengiriman-barang.template-pdf',
                compact('data', 'profileUser'),
            )->setPaper('A4', 'portrait');

            return $pdf->download(
                Str::slug('Surat Pengiriman Barang-' .
                    $data->nomor_pengiriman_barang)
                . '.pdf',
            );
        } catch (Exception $e) {
            Log::error('Error generate SPB PDF', [
                'id' => $id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal mendownload PDF SPB!');
        }
    }

    public function update($id, $data)
    {
        DB::beginTransaction();
        try {
            $spb = SuratPengirimanBarang::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $userEmail = Auth::user()->email ?? 'system@email.com';

            // Jangan update foreign key SPP yang tidak boleh berubah
            unset($data['spp_id']);
            unset($data['pesanan_penjualan_id']);

            // Handle TTD Pengirim
            if (request()->hasFile('ttd_pengirim')) {
                if ($spb->ttd_pengirim) {
                    $this->fileUploadService->delete($spb->ttd_pengirim);
                }
                $data['ttd_pengirim'] = $this->fileUploadService->upload(
                    request()->file('ttd_pengirim'),
                    'surat/spb/ttd_pengirim',
                    $userEmail
                );
            } else {
                unset($data['ttd_pengirim']);
            }

            // Handle TTD Penerima
            if (request()->hasFile('ttd_penerima')) {
                if ($spb->ttd_penerima) {
                    $this->fileUploadService->delete($spb->ttd_penerima);
                }
                $data['ttd_penerima'] = $this->fileUploadService->upload(
                    request()->file('ttd_penerima'),
                    'surat/spb/ttd_penerima',
                    $userEmail
                );
            } else {
                unset($data['ttd_penerima']);
            }

            $spb->update($data);

            // Update detail barang
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    if (isset($item['spb_detail_id'])) {
                        $detail = SuratPengirimanBarangDetail::find($item['spb_detail_id']);
                        if ($detail) {
                            $detail->update([
                                'jumlah_dikirim' => $item['jumlah_dikirim'] ?? 0,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed updating SPB: ', [
                'spb_id' => $id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $spb = SuratPengirimanBarang::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();
            $spb->delete();
            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus SPB', [
                'spb_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
