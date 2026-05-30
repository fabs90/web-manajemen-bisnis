<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermintaanKasKecilRequest;
use App\Models\KasKecil;
use App\Models\KasKecilDetail;
use App\Models\KasKecilFormulir;
use App\Services\PermintaanKasKecilService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KasKecilController extends Controller
{
    public function __construct(
        protected PermintaanKasKecilService $service
    ) {}

    public function index(): View
    {
        $userId = Auth::id();
        $kasKecil = KasKecil::with(['kasKecilDetail', 'kasKecilFormulir', 'kasKecilLog'])->where('user_id', $userId)->get();
        $saldoAkhir = KasKecil::where('user_id', $userId)
            ->latest()
            ->value('saldo_akhir');

        return view('administrasi.surat.kas-kecil.index', compact('kasKecil', 'saldoAkhir'));
    }

    public function create(): View
    {
        return view('administrasi.surat.kas-kecil.create');
    }

    public function store(PermintaanKasKecilRequest $request): RedirectResponse
    {
        try {
            $this->service->store($request);

            return back()->with('success', 'Permintaan kas kecil berhasil disimpan.');
        } catch (\Exception $e) {
            Log::error('Gagal simpan kas kecil: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'request' => $request->except(['_token', 'ttd_*']),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Terjadi kesalahan saat menyimpan data.');
        }
    }

    public function generatePdf(int $id)
    {
        $data = KasKecil::with(['kasKecilDetail', 'kasKecilFormulir'])->findOrFail($id);
        $userProfile = Auth::user();

        $pdf = Pdf::loadView('administrasi.surat.kas-kecil.template-pdf', [
            'data' => $data,
            'userProfile' => $userProfile,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('permintaan-kas-kecil-'.$data->id.'.pdf');
    }

    public function destroy(int $id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $kas = KasKecil::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Hapus detail kas kecil
            KasKecilDetail::where('kas_kecil_id', $id)->delete();

            // Ambil formulirnya
            $formulir = KasKecilFormulir::where('kas_kecil_id', $id)->first();

            // Hapus file ttd jika ada
            if ($formulir) {
                $ttdFiles = [
                    $formulir->ttd_nama_pemohon,
                    $formulir->ttd_atasan_langsung,
                    $formulir->ttd_bagian_keuangan,
                ];

                foreach ($ttdFiles as $file) {
                    if ($file) {
                        Storage::disk('public')->delete($file);
                    }
                }

                $formulir->delete();
            }

            $kas->delete();
            DB::commit();

            return redirect()->back()->with('success', 'Data kas kecil berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Delete kas kecil error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal menghapus data kas kecil.');
        }
    }
}
