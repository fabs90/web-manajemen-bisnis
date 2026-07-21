<?php

namespace App\Http\Controllers\SPB;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuratPengirimanBarangRequest;
use App\Models\SPB\SuratPengirimanBarang;
use App\Models\SPP\SuratPesananPenjualan;
use App\Services\SuratPengirimanBarangService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SuratPengirimanBarangController extends Controller
{
    public function index()
    {
        $suratPengirimanBarang = SuratPengirimanBarang::with([
            'pesananPenjualan',
            'pesananPenjualan.pelanggan',
            'user',
        ])
            ->where('user_id', auth()->id())
            ->get();

        return view(
            'administrasi.surat.surat-pengiriman-barang.index',
            compact('suratPengirimanBarang'),
        );
    }

    public function create()
    {
        $newSpp = SuratPesananPenjualan::with([
            'pelanggan',
            'details',
        ])
            ->where('user_id', auth()->id())
            ->whereDoesntHave('suratPengirimanBarang')
            ->get();

        $dataSpp = collect();

        foreach ($newSpp as $item) {
            $dataSpp->push((object) [
                'id' => $item->id,
                'nomor_pesanan_pembelian' => $item->nomor_pesanan_penjualan,
                'tanggal_kirim_pesanan_pembelian' => $item->tanggal_kirim_pesanan_penjualan ? $item->tanggal_kirim_pesanan_penjualan->format('Y-m-d') : null,
                'pelanggan' => $item->pelanggan,
                'pesananPembelianDetail' => $item->details->map(fn($d) => (object) [
                    'id' => $d->id,
                    'nama_barang' => $d->nama_barang,
                    'kuantitas' => $d->kuantitas,
                ]),
            ]);
        }

        return view('administrasi.surat.surat-pengiriman-barang.create', compact('dataSpp'));
    }

    public function edit($id)
    {
        $dataSpb = SuratPengirimanBarang::with([
            'pesananPenjualan',
            'suratPengirimanBarangDetail.pesananPenjualanDetail',
        ])->where('id', $id)->firstOrFail();

        return view('administrasi.surat.surat-pengiriman-barang.edit', compact('dataSpb'));
    }

    public function generatePdf($id)
    {
        try {
            $service = app(SuratPengirimanBarangService::class);

            return $service->generatePdf($id);
        } catch (Throwable $th) {
            Log::error(
                'Gagal generate PDF Surat Pengiriman): ' . $th->getMessage(),
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal generate PDF Surat Pengiriman Barang (SPB)',
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
                ->route('administrasi.spb.index')
                ->with(
                    'success',
                    'Surat Pengiriman Barang (SPB) berhasil ditambahkan.',
                );
        } catch (Throwable $th) {
            Log::error(
                'Gagal menambahkan Surat Pengiriman Barang (SPB): ' .
                $th->getMessage(),
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menambahkan Surat Pengiriman Barang (SPB), terjadi kesalahan. Silakan coba lagi.',
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
                ->route('administrasi.spb.index')
                ->with(
                    'success',
                    'Surat Pengiriman Barang (SPB) berhasil diubah.',
                );
        } catch (Throwable $th) {
            Log::error(
                'Gagal mengubah Surat Pengiriman Barang (SPB): ' .
                $th->getMessage(),
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal mengubah Surat Pengiriman Barang (SPB): ' .
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
                ->route('administrasi.spb.index')
                ->with(
                    'success',
                    'Surat Pengiriman Barang (SPB) berhasil dihapus.',
                );
        } catch (Throwable $th) {
            Log::error(
                'Gagal menghapus Surat Pengiriman Barang (SPB): ' .
                $th->getMessage(),
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menghapus Surat Pengiriman Barang (SPB): ' .
                    $th->getMessage(),
                );
        }
    }
}