<?php

namespace App\Http\Controllers\SuratKeluar;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuratKeluarRequest;
use App\Models\AgendaSuratKeluar;
use App\Services\SuratKeluarService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SuratKeluarController extends Controller
{
    public function __construct(protected SuratKeluarService $suratKeluarService) {}

    public function index()
    {
        $suratKeluar = AgendaSuratKeluar::with('emailLogs')
            ->where('user_id', auth()->id())
            ->select('id', 'nomor_surat', 'tanggal_surat', 'nama_penerima', 'email_penerima', 'perihal', 'tembusan', 'file_lampiran')
            ->get();

        return view(
            'administrasi.surat.surat-keluar.index',
            compact('suratKeluar'),
        );
    }

    public function create()
    {
        return view('administrasi.surat.surat-keluar.create');
    }

    public function store(SuratKeluarRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $user = Auth::user();
            $fileLampiran = $request->file('file_lampiran');
            $ttdFile = $request->file('ttd');

            $this->suratKeluarService->store($data, $user, $fileLampiran, $ttdFile);

            DB::commit();

            return redirect()
                ->route('administrasi.surat-keluar.index')
                ->with('success', 'Surat keluar berhasil dikirim ke penerima.');
        } catch (Throwable $e) {
            report($e);
            DB::rollBack();
            Log::error('Store surat keluar error: '.$e->getMessage());

            return back()->with(
                'error',
                $e->getMessage(),
            );
        }
    }

    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {
            $surat = AgendaSuratKeluar::where('user_id', auth()->id())->findOrFail($id);

            $this->suratKeluarService->delete($surat);

            DB::commit();

            return redirect()
                ->route('administrasi.surat-keluar.index')
                ->with('success', 'Surat keluar berhasil dihapus.');
        } catch (Throwable $e) {
            report($e);
            DB::rollBack();
            Log::error('Gagal menghapus Surat Keluar: '.$e->getMessage());

            return back()->with(
                'error',
                'Terjadi kesalahan saat menghapus surat.',
            );
        }
    }

    public function downloadPdf(int $id)
    {
        return $this->suratKeluarService->generatePdf($id);
    }

    public function viewSignature(int $id)
    {
        $surat = AgendaSuratKeluar::where('user_id', auth()->id())->findOrFail($id);

        abort_unless($surat->ttd, 404);

        $filePath = storage_resolve_path($surat->ttd);

        abort_unless($filePath && file_exists($filePath), 404);

        return response()->file($filePath);
    }

    public function viewLampiran(int $id)
    {
        $surat = AgendaSuratKeluar::where('user_id', auth()->id())->findOrFail($id);

        abort_unless($surat->file_lampiran, 404);

        $filePath = storage_resolve_path($surat->file_lampiran);

        abort_unless($filePath && file_exists($filePath), 404);

        $fileName = basename($surat->file_lampiran);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $inlineExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

        if (! in_array($extension, $inlineExtensions, true)) {
            return response()->download($filePath, $fileName);
        }

        return response()->file($filePath, [
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }
}
