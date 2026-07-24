<?php

namespace App\Http\Controllers\Faktur;

use App\Http\Controllers\Controller;
use App\Models\Faktur\FakturPenjualan;
use App\Models\SPB\SuratPengirimanBarang;
use App\Services\AdministrasiFakturService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdministrasiFakturController extends Controller
{
    public function index()
    {
        $fakturPenjualan = FakturPenjualan::with([
            'suratPengirimanBarang.pesananPenjualan.pelanggan',
        ])
            ->where('user_id', auth()->id())
            ->get();

        return view('administrasi.surat.faktur-penjualan.index', compact('fakturPenjualan'));
    }

    public function create()
    {
        $dataSpb = SuratPengirimanBarang::whereHas('pesananPenjualan', function ($query) {
            $query->whereNotNull('pelanggan_id');
        })
            ->whereDoesntHave('fakturPenjualan')
            ->with([
                'pesananPenjualan.pelanggan',
                'suratPengirimanBarangDetail.pesananPenjualanDetail',
            ])
            ->where('user_id', auth()->id())
            ->get()
            ->map(function ($spb) {
                return [
                    'id' => $spb->id,
                    'nomor_pengiriman_barang' => $spb->nomor_pengiriman_barang,
                    'nama_pengirim' => $spb->nama_pengirim,
                    'pesanan_penjualan' => [
                        'pelanggan' => [
                            'nama' => $spb->pesananPenjualan->pelanggan->nama ?? '',
                            'alamat' => $spb->pesananPenjualan->pelanggan->alamat ?? '',
                        ],
                    ],
                    'surat_pengiriman_barang_detail' => $spb->suratPengirimanBarangDetail->map(function ($detail) {
                        return [
                            'id' => $detail->id,
                            'jumlah_dikirim' => $detail->jumlah_dikirim,
                            'pesanan_penjualan_detail' => [
                                'nama_barang' => $detail->pesananPenjualanDetail->nama_barang ?? '',
                                'kuantitas' => $detail->pesananPenjualanDetail->kuantitas ?? 0,
                                'harga' => $detail->pesananPenjualanDetail->harga ?? 0,
                            ],
                        ];
                    }),
                ];
            });

        return view('administrasi.surat.faktur-penjualan.create', compact('dataSpb'));
    }

    public function store(Request $request)
    {
        try {
            $manajemenRapatServices = app(AdministrasiFakturService::class);
            $manajemenRapatServices->store($request->all());

            return redirect()
                ->route('administrasi.faktur-penjualan.index')
                ->with('success', 'Faktur penjualan berhasil ditambahkan.');
        } catch (\Throwable $th) {
            report($th);
            Log::error(
                'Gagal menambahkan faktur penjualan: '.$th->getMessage(),
            );

            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan faktur penjualan.');
        }
    }

    public function generatePdf($id)
    {
        $manajemenRapatServices = app(AdministrasiFakturService::class);

        return $manajemenRapatServices->generatePdf($id);
    }

    public function destroy($id)
    {
        // Implement destroy logic here
        try {
            $manajemenRapatServices = app(AdministrasiFakturService::class);
            $manajemenRapatServices->destroy($id);

            return redirect()
                ->route('administrasi.faktur-penjualan.index')
                ->with('success', 'Faktur penjualan berhasil dihapus🗑️.');
        } catch (\Throwable $th) {
            report($th);
            Log::error(
                'Gagal menghapus faktur penjualan: '.$th->getMessage(),
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menghapus faktur penjualan: '.$th->getMessage(),
                );
        }
    }
}
