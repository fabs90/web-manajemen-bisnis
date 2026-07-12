<?php

namespace App\Http\Controllers\Memo;

use App\Http\Controllers\Controller;
use App\Models\Faktur\FakturPenjualan;
use App\Models\MemoKredit\MemoKredit;
use App\Models\SPP\SuratPesananPembelian;
use App\Models\SPP\SuratPesananPenjualanDetail;
use App\Services\MemoKreditService;
use Illuminate\Http\Request;

class MemoKreditController extends Controller
{
    public function index()
    {
        return view('administrasi.surat.memo-kredit.index');
    }

    // Memo Kredit kepada Pelanggan (Mengurangi piutang pelanggan)
    public function pelanggan()
    {
        $memoKredit = MemoKredit::with('fakturPenjualan.suratPengirimanBarang.pesananPenjualan')
            ->where('user_id', auth()->id())
            ->get();
        $fakturPenjualan = FakturPenjualan::with([
            'suratPengirimanBarang.pesananPenjualan.pelanggan',
        ])
            ->where('user_id', auth()->id())
            ->whereDoesntHave('memoKredit')
            ->get();

        return view(
            'administrasi.surat.memo-kredit.pelanggan',
            compact('memoKredit', 'fakturPenjualan'),
        );
    }

    // Memo kredit dari penjual (Mengurangi hutang sendiri ke penjual | Retur Pembelian)
    public function penjual()
    {
        $returPembelian = \App\Models\ReturPembelian::with('pesananPembelian.supplier')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
        $suratPesananPembelian = SuratPesananPembelian::with('supplier', 'pesananPembelianDetail')
            ->where('user_id', auth()->id())
            ->whereDoesntHave('returPembelian')
            ->get();

        return view('administrasi.surat.memo-kredit.penjual', compact('suratPesananPembelian', 'returPembelian'));
    }

    public function createPenjual($sppId)
    {
        $spp = SuratPesananPembelian::with('supplier', 'pesananPembelianDetail', 'pesananPembelianDetail.barang')
            ->findOrFail($sppId);

        return view('administrasi.surat.memo-kredit.create-penjual', compact('spp'));
    }

    public function storePenjual(Request $request, \App\Services\ReturPembelianService $services)
    {
        $request->validate([
            'barang_id' => 'required|array',
            'jumlah_dikembalikan' => 'required|array',
        ]);

        $serviceApp = $services->store($request);
        if ($serviceApp) {
            return redirect()
                ->route('administrasi.memo-kredit.penjual')
                ->with('success', 'Memo Kredit dari Penjual berhasil disimpan!');
        }

        return redirect()
            ->route('administrasi.memo-kredit.create-penjual', ['sppId' => $request->spp_id])
            ->with('error', 'Memo Kredit dari Penjual gagal disimpan!')->withInput();
    }

    public function destroyPenjual($returId, \App\Services\ReturPembelianService $services)
    {
        $serviceApp = $services->destroy($returId);
        if ($serviceApp) {
            return redirect()
                ->route('administrasi.memo-kredit.penjual')
                ->with('success', 'Memo Kredit dari Penjual berhasil dihapus!');
        }

        return redirect()
            ->route('administrasi.memo-kredit.penjual')
            ->with('error', 'Memo Kredit dari Penjual gagal dihapus!');
    }

    public function generatePdfPenjual($returId, \App\Services\ReturPembelianService $services)
    {
        return $services->generatePdf($returId);
    }

    public function create($fakturId)
    {
        $faktur = FakturPenjualan::with(
            'suratPengirimanBarang',
            'suratPengirimanBarang.pesananPenjualan',
            'suratPengirimanBarang.suratPengirimanBarangDetail.pesananPenjualanDetail'
        )->findOrFail($fakturId);

        return view('administrasi.surat.memo-kredit.create', compact('faktur'));
    }

    public function store(Request $request, MemoKreditService $services)
    {
        $request->validate([
            'barang_id' => 'required|array',
            'jumlah_dikembalikan' => 'required|array',
        ]);

        foreach ($request->barang_id as $index => $barangDetailId) {
            $qty = $request->jumlah_dikembalikan[$index] ?? 0;
            $sppDetail = SuratPesananPenjualanDetail::find($barangDetailId);

            if ($sppDetail && $qty > $sppDetail->kuantitas) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Jumlah dikembalikan untuk barang {$sppDetail->nama_barang} tidak boleh melebihi jumlah pesanan ({$sppDetail->kuantitas}).");
            }
        }

        $serviceApp = $services->store($request);
        if ($serviceApp) {
            return redirect()
                ->route('administrasi.memo-kredit.pelanggan')
                ->with('success', 'Memo Kredit berhasil disimpan!');
        }

        return redirect()
            ->route('administrasi.memo-kredit.create', ['fakturId' => $request->faktur_id])
            ->with('error', 'Memo Kredit gagal disimpan!')->withInput();
    }

    public function destroy($fakturId, MemoKreditService $services)
    {
        $serviceApp = $services->destroy($fakturId);
        if ($serviceApp) {
            return redirect()
                ->route('administrasi.memo-kredit.pelanggan')
                ->with('success', 'Memo Kredit berhasil dihapus!');
        }

        return redirect()
            ->route('administrasi.memo-kredit.pelanggan')
            ->with('error', 'Memo Kredit gagal dihapus!');
    }

    public function generatePdf($fakturId, MemoKreditService $services)
    {
        return $services->generatePdf($fakturId);
    }
}
