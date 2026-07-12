<?php

namespace App\Services;

use App\Jobs\SendSuratUndanganRapatJob;
use App\Jobs\SendUpdateSuratUndanganRapatJob;
use App\Models\SuratUndanganRapat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Str;
use Throwable;

class SuratUndanganRapatService
{
    public function __construct(protected FileUploadService $fileUploadService) {}

    /**
     * Create a new class instance.
     */
    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $data['user_id'] = Auth::id();

            if (! empty($data['file_lampiran'])) {
                $data['file_lampiran'] = $this->fileUploadService->upload($data['file_lampiran'], 'surat-undangan-rapat/lampiran', auth()->user()->email);
            }

            $suratUndanganRapat = SuratUndanganRapat::create([
                'user_id' => auth()->id(),
                'nomor_surat' => $data['nomor_surat'],
                'lampiran' => $data['lampiran'] ?? null,
                'file_lampiran' => $data['file_lampiran'] ?? null,
                'perihal' => $data['perihal'],
                'nama_penerima' => $data['nama_penerima'],
                'email_penerima' => $data['email_penerima'],
                'jabatan_penerima' => $data['jabatan_penerima'] ?? null,
                'kota_penerima' => $data['kota_penerima'] ?? null,
                'judul_rapat' => $data['judul_rapat'],
                'tanggal_rapat' => $data['tanggal_rapat'],
                'hari' => $data['hari'] ?? null,
                'waktu_mulai' => $data['waktu_mulai'] ?? null,
                'waktu_selesai' => $data['waktu_selesai'] ?? null,
                'tempat' => $data['tempat'] ?? null,
                'nama_penandatangan' => null,
                'jabatan_penandatangan' => null,
                'tembusan' => $data['tembusan'] ?? null,
                'ttd' => null,
            ]);

            if (! empty($data['agenda']) && is_array($data['agenda'])) {
                foreach ($data['agenda'] as $agenda) {
                    if (! empty($agenda)) {
                        $suratUndanganRapat->details()->create([
                            'user_id' => Auth::id(),
                            'agenda' => $agenda,
                        ]);
                    }
                }
            }

            DB::commit();

            // Dispatch job to send email
            SendSuratUndanganRapatJob::dispatch($suratUndanganRapat, auth()->user());

            return $suratUndanganRapat;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Surat Undangan Rapat Store Error: '.$e->getMessage());

            return false;
        }
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();
        try {
            $suratUndanganRapat = SuratUndanganRapat::findOrFail($id);

            if (! empty($data['file_lampiran'])) {
                $fileLampiran = $this->fileUploadService->upload($data['file_lampiran'], 'surat-undangan-rapat/lampiran', auth()->user()->email);
                $data['file_lampiran'] = $fileLampiran;
                $this->fileUploadService->delete($suratUndanganRapat->file_lampiran);
            } else {
                $data['file_lampiran'] = $suratUndanganRapat->file_lampiran;
            }

            $suratUndanganRapat->update([
                'nomor_surat' => $data['nomor_surat'],
                'lampiran' => $data['lampiran'] ?? null,
                'file_lampiran' => $data['file_lampiran'],
                'perihal' => $data['perihal'],
                'nama_penerima' => $data['nama_penerima'],
                'email_penerima' => $data['email_penerima'],
                'jabatan_penerima' => $data['jabatan_penerima'] ?? null,
                'kota_penerima' => $data['kota_penerima'] ?? null,
                'judul_rapat' => $data['judul_rapat'],
                'tanggal_rapat' => $data['tanggal_rapat'],
                'hari' => $data['hari'] ?? null,
                'waktu_mulai' => $data['waktu_mulai'] ?? null,
                'waktu_selesai' => $data['waktu_selesai'] ?? null,
                'tempat' => $data['tempat'] ?? null,
                'nama_penandatangan' => null,
                'jabatan_penandatangan' => null,
                'tembusan' => $data['tembusan'] ?? null,
                'ttd' => null,
            ]);

            // Sync agenda details
            $suratUndanganRapat->details()->delete();
            if (! empty($data['agenda']) && is_array($data['agenda'])) {
                foreach ($data['agenda'] as $agenda) {
                    if (! empty($agenda)) {
                        $suratUndanganRapat->details()->create([
                            'user_id' => Auth::id(),
                            'agenda' => $agenda,
                        ]);
                    }
                }
            }

            DB::commit();

            // dispatch updated email
            SendUpdateSuratUndanganRapatJob::dispatch($suratUndanganRapat, auth()->user());

            return $suratUndanganRapat;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Surat Undangan Rapat Update Error: '.$e->getMessage());

            return false;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $suratUndanganRapat = SuratUndanganRapat::findOrFail($id);
            $suratUndanganRapat->details()->delete();
            $suratUndanganRapat->delete();

            DB::commit();

            return true;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Surat Undangan Rapat Delete Error: '.$e->getMessage());

            return false;
        }
    }

    public function generatePdf($id)
    {
        $suratUndanganRapat = SuratUndanganRapat::findOrFail($id);
        $pdf = $suratUndanganRapat->generatePdf();

        return $pdf->download(
            Str::slug('surat-undangan-rapat-'.$suratUndanganRapat->perihal).'.pdf',
        );
    }
}
