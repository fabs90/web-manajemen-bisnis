<?php

namespace App\Http\Controllers\SuratKeluar;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuratKeluarRequest;
use App\Jobs\SendSuratKeluarJob;
use App\Models\AgendaSuratKeluar;
use App\Models\SuratKeluarEmailLog;
use App\Services\FileUploadService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

final class SuratKeluarController extends Controller
{
    public function __construct(protected FileUploadService $fileUploadService) {}

    public function index()
    {
        $suratKeluar = AgendaSuratKeluar::with('emailLogs')
            ->where('user_id', auth()->id())
            ->select('id', 'nomor_surat', 'tanggal_surat', 'nama_penerima', 'perihal', 'tembusan', 'file_lampiran')
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
            $data['user_id'] = Auth::id();
            $fileLampiran = null;
            $ttdFile = null;
            $user = Auth::user();

            if ($request->hasFile('file_lampiran')) {
                $fileLampiran = $this->fileUploadService->upload($request->file('file_lampiran'), 'surat-keluar/lampiran', auth()->user()->email);
            }

            if ($request->hasFile('ttd')) {
                $ttdFile = $this->fileUploadService->upload($request->file('ttd'), 'surat-keluar/ttd', auth()->user()->email);
            }

            $surat = AgendaSuratKeluar::create([
                'user_id' => Auth::id(),
                'nomor_surat' => $request->nomor_surat,
                'lampiran' => $request->lampiran_text,
                'perihal' => $request->perihal,
                'tanggal_surat' => $request->tanggal_surat,

                // penerima
                'nama_penerima' => $request->nama_penerima,
                'jabatan_penerima' => $request->jabatan_penerima,
                'alamat_penerima' => $request->alamat_penerima,
                'email_penerima' => $request->email_penerima,

                // isi surat
                'paragraf_pembuka' => $request->paragraf_pembuka,
                'paragraf_isi' => $request->paragraf_isi,
                'paragraf_penutup' => $request->paragraf_penutup,

                // pengirim
                'nama_pengirim' => $request->nama_pengirim,
                'jabatan_pengirim' => $request->jabatan_pengirim,

                // tembusan
                'tembusan' => $request->tembusan,

                // file
                'ttd' => $ttdFile,
                'file_lampiran' => $fileLampiran,
            ]);

            // queue to send email bruh
            dispatch(new SendSuratKeluarJob($surat, $user));

            SuratKeluarEmailLog::create([
                'surat_keluar_id' => $surat->id,
                'email' => $data['email_penerima'],
                'status' => 'success',
            ]);

            DB::commit();

            return redirect()
                ->route('administrasi.surat-keluar.index')
                ->with('success', 'Surat keluar berhasil dikirim ke penerima.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Surat keluar error: '.$e->getMessage());

            return back()->with(
                'error',
                'Terjadi kesalahan saat menyimpan surat keluar.',
            );
        }
    }

    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {
            $surat = AgendaSuratKeluar::findOrFail($id);

            // Hapus file lampiran jika ada
            if (
                $surat->file_lampiran &&
                Storage::disk('public')->exists($surat->file_lampiran)
            ) {
                Storage::disk('public')->delete($surat->file_lampiran);
            }

            // Hapus file ttd jika ada
            if ($surat->ttd && Storage::disk('public')->exists($surat->ttd)) {
                Storage::disk('public')->delete($surat->ttd);
            }

            // Hapus record surat keluar
            $surat->delete();

            DB::commit();

            return redirect()
                ->route('administrasi.surat-keluar.index')
                ->with('success', 'Surat keluar berhasil dihapus.');
        } catch (Throwable $e) {
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
        $suratKeluar = AgendaSuratKeluar::find($id);
        if (! $suratKeluar) {
            abort(404, 'Data surat keluar tidak ditemukan.');
        }
        $fileName = 'surat-keluar-'.Str::slug($suratKeluar->nomor_surat ?? 'dokumen').'.pdf';
        $pdf = Pdf::loadView('emails.surat-keluar-pdf', [
            'surat' => $suratKeluar,
            'user' => auth()->user(),
        ]);

        return $pdf->download($fileName);
    }
}
