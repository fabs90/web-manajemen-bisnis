<?php

namespace App\Http\Controllers\Faktur;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\AdministrasiFakturService;
use App\Http\Controllers\Controller;
use App\Models\Faktur\FakturPenjualan;
use App\Models\SPB\SuratPengirimanBarang;

class AdministrasiFakturController extends Controller
{
    public function index()
    {
        $fakturPenjualan = FakturPenjualan::with([
            'suratPengirimanBarang.pesananPembelian.pelanggan'
        ])
            ->where("user_id", auth()->id())
            ->latest()
            ->get();

        return view("administrasi.surat.faktur-penjualan.index", compact("fakturPenjualan"));
    }

    public function create()
    {
        $dataSpb = SuratPengirimanBarang::where(function ($query) {
            $query->whereHas('pesananPembelian.pelanggan')
                ->orWhereHas('pesananPembelian.supplier');
        })
            ->whereDoesntHave('fakturPenjualan')
            ->with([
                'pesananPembelian.pelanggan',
                'pesananPembelian.supplier',
                'suratPengirimanBarangDetail.pesananPembelianDetail',
            ])
            ->orderBy('id', 'DESC')
            ->get()
            ->map(function ($spb) {
                return [
                    'id' => $spb->id,
                    'nomor_pengiriman_barang' => $spb->nomor_pengiriman_barang,
                    'nama_pengirim' => $spb->nama_pengirim,
                    'pesanan_pembelian' => [
                        'jenis' => $spb->pesananPembelian->jenis,
                        'pelanggan' => $spb->pesananPembelian->pelanggan ? [
                            'nama' => $spb->pesananPembelian->pelanggan->nama,
                            'alamat' => $spb->pesananPembelian->pelanggan->alamat,
                        ] : null,
                        'supplier' => $spb->pesananPembelian->supplier ? [
                            'nama' => $spb->pesananPembelian->supplier->nama,
                            'alamat' => $spb->pesananPembelian->supplier->alamat,
                        ] : null,
                    ],
                    'surat_pengiriman_barang_detail' => $spb->suratPengirimanBarangDetail->map(function ($detail) {
                        return [
                            'id' => $detail->id,
                            'jumlah_dikirim' => $detail->jumlah_dikirim,
                            'pesanan_pembelian_detail' => [
                                'nama_barang' => $detail->pesananPembelianDetail->nama_barang,
                                'kuantitas' => $detail->pesananPembelianDetail->kuantitas,
                                'harga' => $detail->pesananPembelianDetail->harga,
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
            dd($request->all());
            $manajemenRapatServices = app(AdministrasiFakturService::class);
            $manajemenRapatServices->store($request->all());
            return redirect()
                ->route("administrasi.faktur-penjualan.index")
                ->with("success", "Faktur penjualan berhasil ditambahkan.");
        } catch (\Throwable $th) {
            Log::error(
                "Gagal menambahkan faktur penjualan: " . $th->getMessage(),
            );
            return back()
                ->withInput()
                ->with("error", "Gagal menambahkan faktur penjualan.");
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
                ->route("administrasi.faktur-penjualan.index")
                ->with("success", "Faktur penjualan berhasil dihapus🗑️.");
        } catch (\Throwable $th) {
            Log::error(
                "Gagal menghapus faktur penjualan: " . $th->getMessage(),
            );
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Gagal menghapus faktur penjualan: " . $th->getMessage(),
                );
        }
    }
}