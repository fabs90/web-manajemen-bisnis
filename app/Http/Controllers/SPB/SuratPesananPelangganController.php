<?php

namespace App\Http\Controllers\SPB;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuratPesananPenjualanRequest;
use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\SPP\SuratPesananPenjualan;
use App\Services\SuratPesananPenjualanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SuratPesananPelangganController extends Controller
{
    public function create(): View
    {
        $pelanggan = Pelanggan::where('user_id', auth()->id())->where('jenis', 'debitur')->get();
        $barang = Barang::where('user_id', auth()->id())->get();
        $sppList = SuratPesananPenjualan::where('user_id', auth()->id())->get();

        return view('administrasi.surat.surat-pengiriman-barang.create-spp-pelanggan', compact('pelanggan', 'barang', 'sppList'));
    }

    public function store(SuratPesananPenjualanRequest $request, SuratPesananPenjualanService $service): RedirectResponse
    {
        $request = $request->validated();
        try {
            $service->storePelanggan($request);

            return redirect()
                ->route('administrasi.spb.create')
                ->with(
                    'success',
                    'Pesanan Pembelian dari Pelanggan berhasil ditambahkan. Silakan pilih pesanan tersebut untuk membuat SPB.'
                );
        } catch (\Throwable $th) {
            report($th);
            Log::error(
                'Gagal menambahkan SPP Pelanggan: '.
                $th->getMessage(),
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menambahkan Pesanan Pembelian dari Pelanggan: '.
                    $th->getMessage(),
                );
        }
    }

    public function destroy($id, SuratPesananPenjualanService $service): RedirectResponse
    {
        try {
            $service->destroy($id);

            return back()->with('success', 'Surat Pesanan Penjualan berhasil dihapus.');
        } catch (\Throwable $th) {
            report($th);
            Log::error('Gagal menghapus SPP Pelanggan: '.$th->getMessage());

            return back()->with('error', 'Gagal menghapus Pesanan Pembelian dari Pelanggan: '.$th->getMessage());
        }
    }

    public function generatePdf($id, SuratPesananPenjualanService $service)
    {
        try {
            return $service->generatePdf($id);
        } catch (\Throwable $th) {
            report($th);
            Log::error('Gagal generate PDF SPP Pelanggan: '.$th->getMessage());

            return back()->with('error', 'Gagal generate PDF Pesanan Pembelian dari Pelanggan.');
        }
    }
}
