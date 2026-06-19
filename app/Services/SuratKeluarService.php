<?php

namespace App\Services;

use App\Jobs\SendSuratKeluarJob;
use App\Models\AgendaSuratKeluar;
use App\Models\SuratKeluarEmailLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SuratKeluarService
{
    public function __construct(protected FileUploadService $fileUploadService)
    {
    }

    public function store(array $data, User $user, ?UploadedFile $fileLampiran, ?UploadedFile $ttdFile)
    {
        $lampiranPath = null;
        $ttdPath = null;

        // Validate email
        if (!$this->validateEmail((string) $data['email_penerima'])) {
            throw new \Exception('Maaf, Email yang dimasukan tidak valid. Coba ulangi dengan email yang valid.');
        }

        if ($fileLampiran) {
            $lampiranPath = $this->fileUploadService->upload($fileLampiran, 'surat-keluar/lampiran', $user->email);
        }

        if ($ttdFile) {
            $ttdPath = $this->fileUploadService->upload($ttdFile, 'surat-keluar/ttd', $user->email);
        }

        $surat = AgendaSuratKeluar::create([
            'user_id' => $user->id,
            'nomor_surat' => $data['nomor_surat'],
            'lampiran' => $data['lampiran_text'] ?? null,
            'perihal' => $data['perihal'],
            'tanggal_surat' => $data['tanggal_surat'],

            // penerima
            'nama_penerima' => $data['nama_penerima'],
            'jabatan_penerima' => $data['jabatan_penerima'],
            'alamat_penerima' => $data['alamat_penerima'],
            'email_penerima' => $data['email_penerima'],

            // isi surat
            'paragraf_pembuka' => $data['paragraf_pembuka'],
            'paragraf_isi' => $data['paragraf_isi'],
            'paragraf_penutup' => $data['paragraf_penutup'],

            // pengirim
            'nama_pengirim' => $data['nama_pengirim'],
            'jabatan_pengirim' => $data['jabatan_pengirim'],

            // tembusan
            'tembusan' => $data['tembusan'] ?? null,

            // file
            'ttd' => $ttdPath,
            'file_lampiran' => $lampiranPath,
        ]);

        dispatch(new SendSuratKeluarJob($surat, $user))->afterCommit();

        SuratKeluarEmailLog::create([
            'surat_keluar_id' => $surat->id,
            'email' => $data['email_penerima'],
            'status' => 'success',
        ]);

        return $surat;
    }

    public function delete(AgendaSuratKeluar $surat)
    {
        if (
            $surat->file_lampiran &&
            Storage::disk('public')->exists($surat->file_lampiran)
        ) {
            Storage::disk('public')->delete($surat->file_lampiran);
        }

        if ($surat->ttd && Storage::disk('public')->exists($surat->ttd)) {
            Storage::disk('public')->delete($surat->ttd);
        }

        return $surat->delete();
    }

    public function generatePdf(int $id)
    {
        $suratKeluar = AgendaSuratKeluar::findOrFail($id);

        $fileName = 'surat-keluar-' . Str::slug($suratKeluar->nomor_surat ?? 'dokumen') . '.pdf';

        $pdf = Pdf::loadView('administrasi.surat.surat-keluar.template.surat-keluar-pdf', [
            'surat' => $suratKeluar,
            'user' => auth()->user(),
        ]);

        return $pdf->download($fileName);
    }

    private function validateEmail(string $email): bool
    {
        $response = Http::get("https://rapid-email-verifier.fly.dev/api/validate?email={$email}");
        if ($response->json()['status'] !== 'VALID') {
            return false;
        }
        return true;
    }
}
