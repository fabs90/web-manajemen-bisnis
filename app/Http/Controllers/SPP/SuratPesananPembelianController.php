<?php

namespace App\Http\Controllers\SPP;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuratPesananPembelianRequest;
use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\SPP\PesananPembelian;
use App\Services\SuratPesananPembelianService;
use Illuminate\Support\Facades\Log;

class SuratPesananPembelianController extends Controller
{
    public function index()
    {
        $data = PesananPembelian::with('pelanggan', 'pesananPembelianDetail')
            ->where('user_id', auth()->user()->id)
            ->get();

        return view(
            'administrasi.surat.surat-pesanan-pembelian.index',
            compact('data'),
        );
    }

    public function create()
    {
        $pelanggans = Pelanggan::where('user_id', auth()->id())->get();
        $barang = Barang::where('user_id', auth()->id())->get();

        return view('administrasi.surat.surat-pesanan-pembelian.create', compact('pelanggans', 'barang'));
    }

    public function store(
        SuratPesananPembelianRequest $request,
        SuratPesananPembelianService $service,
    ) {
        try {
            $service->store($request);

            return redirect()
                ->route('administrasi.spp.index')
                ->with(
                    'success',
                    'Surat Pesanan Pembelian (SPP) berhasil ditambahkan.',
                );
        } catch (\Throwable $th) {
            Log::error(
                'Gagal menambahkan Surat Pesanan Pembelian (SPP): '.
                $th->getMessage(),
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menambahkan Surat Pesanan Pembelian (SPP): '.
                    $th->getMessage(),
                );
        }
    }

    public function destroy($sppId, SuratPesananPembelianService $service)
    {
        try {
            $service->destroy($sppId);

            return redirect()
                ->route('administrasi.spp.index')
                ->with(
                    'success',
                    'Surat Pesanan Pembelian (SPP) berhasil dihapus.',
                );
        } catch (\Throwable $th) {
            Log::error(
                'Gagal menghapus Surat Pesanan Pembelian (SPP): '.
                $th->getMessage(),
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menghapus Surat Pesanan Pembelian (SPP): '.
                    $th->getMessage(),
                );
        }
    }

    public function generatePdf($sppId, SuratPesananPembelianService $service)
    {
        try {
            return $service->generatePdf($sppId);
        } catch (\Throwable $th) {
            Log::error(
                'Gagal generate PDF Surat Pesanan Pembelian (SPP): '.
                $th->getMessage(),
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal generate PDF Surat Pesanan Pembelian (SPP)',
                );
        }
    }
}
