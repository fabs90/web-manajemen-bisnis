<?php

namespace App\Http\Controllers\SPB;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuratPengirimanBarangRequest;
use App\Models\SPB\SuratPengirimanBarang;
use App\Services\SuratPengirimanBarangService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SuratPengirimanBarangController extends Controller
{
    public function index()
    {
        $suratPengirimanBarang = SuratPengirimanBarang::with([
            'pesananPembelian',
            'pesananPembelian.pelanggan',
            'pesananPenjualan',
            'pesananPenjualan.pelanggan',
            'user',
        ])
            ->latest()
            ->get();

        return view(
            'administrasi.surat.surat-pengiriman-barang.index',
            compact('suratPengirimanBarang'),
        );
    }

    public function create()
    {
        // 1. Old SPP Pelanggan
        $oldSpp = \App\Models\SPP\PesananPembelian::with([
            'pelanggan',
            'pesananPembelianDetail',
        ])
            ->where('user_id', auth()->id())
            ->where('jenis', 'transaksi_masuk')
            ->whereNotNull('pelanggan_id')
            ->whereDoesntHave('suratPengirimanBarang')
            ->latest()
            ->get();

        // 2. New SPP Pelanggan
        $newSpp = \App\Models\SPP\SuratPesananPenjualan::with([
            'pelanggan',
            'details',
        ])
            ->where('user_id', auth()->id())
            ->whereDoesntHave('suratPengirimanBarang')
            ->latest()
            ->get();

        // Map both to a consistent structure for the select list
        $dataSpp = collect();

        foreach ($oldSpp as $item) {
            $dataSpp->push((object) [
                'id' => 'pembelian_'.$item->id,
                'nomor_pesanan_pembelian' => $item->nomor_pesanan_pembelian,
                'tanggal_kirim_pesanan_pembelian' => $item->tanggal_kirim_pesanan_pembelian ? $item->tanggal_kirim_pesanan_pembelian->format('Y-m-d') : null,
                'pelanggan' => $item->pelanggan,
                'pesananPembelianDetail' => $item->pesananPembelianDetail->map(fn ($d) => (object) [
                    'id' => 'pembelian_'.$d->id,
                    'nama_barang' => $d->nama_barang,
                    'kuantitas' => $d->kuantitas,
                ]),
            ]);
        }

        foreach ($newSpp as $item) {
            $dataSpp->push((object) [
                'id' => 'penjualan_'.$item->id,
                'nomor_pesanan_pembelian' => $item->nomor_pesanan_penjualan,
                'tanggal_kirim_pesanan_pembelian' => $item->tanggal_kirim_pesanan_penjualan ? $item->tanggal_kirim_pesanan_penjualan->format('Y-m-d') : null,
                'pelanggan' => $item->pelanggan,
                'pesananPembelianDetail' => $item->details->map(fn ($d) => (object) [
                    'id' => 'penjualan_'.$d->id,
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
            'pesananPembelian',
            'pesananPenjualan',
            'suratPengirimanBarangDetail.pesananPembelianDetail',
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
                'Gagal generate PDF Surat Pengiriman): '.$th->getMessage(),
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
                'Gagal menambahkan Surat Pengiriman Barang (SPB): '.
                $th->getMessage(),
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menambahkan Surat Pengiriman Barang (SPB): '.
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
                ->route('administrasi.spb.index')
                ->with(
                    'success',
                    'Surat Pengiriman Barang (SPB) berhasil diubah.',
                );
        } catch (Throwable $th) {
            Log::error(
                'Gagal mengubah Surat Pengiriman Barang (SPB): '.
                $th->getMessage(),
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal mengubah Surat Pengiriman Barang (SPB): '.
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
        } catch (\Throwable $th) {
            Log::error(
                'Gagal menghapus Surat Pengiriman Barang (SPB): '.
                $th->getMessage(),
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menghapus Surat Pengiriman Barang (SPB): '.
                    $th->getMessage(),
                );
        }
    }
}
