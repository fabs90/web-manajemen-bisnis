<?php

namespace App\Services;

use Illuminate\Support\Facades\{Auth, DB, Log};
use App\Models\SuratUndanganRapat;
use Str;
use Throwable;
use Barryvdh\DomPDF\Facade\Pdf;

class SuratUndanganRapatService
{

    public function __construct(protected FileUploadService $fileUploadService)
    {
    }
    /**
     * Create a new class instance.
     */
    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $data['user_id'] = Auth::id();
            if (!empty($data['ttd'])) {
                $ttdFile = $this->fileUploadService->upload($data['ttd'], 'surat-undangan-rapat/ttd', auth()->user()->email);
            }
            $suratUndanganRapat = SuratUndanganRapat::create([
                "user_id" => $data['user_id'],
                'nomor_surat' => $data['nomor_surat'],
                'lampiran' => $data['lampiran'],
                'perihal' => $data['perihal'],
                'nama_penerima' => $data['nama_penerima'],
                'jabatan_penerima' => $data['jabatan_penerima'],
                'kota_penerima' => $data['kota_penerima'],
                'judul_rapat' => $data['judul_rapat'],
                'tanggal_rapat' => $data['tanggal_rapat'],
                'hari' => $data['hari'],
                'waktu_mulai' => $data['waktu_mulai'],
                'waktu_selesai' => $data['waktu_selesai'],
                'tempat' => $data['tempat'],
                'nama_penandatangan' => $data['nama_penandatangan'],
                'jabatan_penandatangan' => $data['jabatan_penandatangan'],
                'tembusan' => isset($data['tembusan']) ? $data['tembusan'] : null,
                'ttd' => isset($ttdFile) ? $ttdFile : null,

            ]);
            if (!empty($data['agenda']) && is_array($data['agenda'])) {
                foreach ($data['agenda'] as $agenda) {
                    $suratUndanganRapat->details()->create([
                        'user_id' => Auth::id(),
                        'agenda' => $agenda,
                    ]);
                }
            }

            DB::commit();
            return $suratUndanganRapat;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Surat Undangan Rapat Store Error: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();
        try {
            $suratUndanganRapat = SuratUndanganRapat::findOrFail($id);

            if (!empty($data['ttd'])) {
                $ttdFile = $this->fileUploadService->upload($data['ttd'], 'surat-undangan-rapat/ttd', auth()->user()->email);
                $data['ttd'] = $ttdFile;
                $this->fileUploadService->delete($suratUndanganRapat->ttd);
            } else {
                unset($data['ttd']);
            }

            $suratUndanganRapat->update([
                'nomor_surat' => $data['nomor_surat'],
                'lampiran' => $data['lampiran'],
                'perihal' => $data['perihal'],
                'nama_penerima' => $data['nama_penerima'],
                'jabatan_penerima' => $data['jabatan_penerima'],
                'kota_penerima' => $data['kota_penerima'],
                'judul_rapat' => $data['judul_rapat'],
                'tanggal_rapat' => $data['tanggal_rapat'],
                'hari' => $data['hari'],
                'waktu_mulai' => $data['waktu_mulai'],
                'waktu_selesai' => $data['waktu_selesai'],
                'tempat' => $data['tempat'],
                'nama_penandatangan' => $data['nama_penandatangan'],
                'jabatan_penandatangan' => $data['jabatan_penandatangan'],
                'tembusan' => isset($data['tembusan']) ? $data['tembusan'] : null,
                'ttd' => isset($data['ttd']) ? $data['ttd'] : $suratUndanganRapat->ttd,
            ]);

            // Sync agenda details
            $suratUndanganRapat->details()->delete();
            if (!empty($data['agenda']) && is_array($data['agenda'])) {
                foreach ($data['agenda'] as $agenda) {
                    $suratUndanganRapat->details()->create([
                        'user_id' => Auth::id(),
                        'agenda' => $agenda,
                    ]);
                }
            }

            DB::commit();
            return $suratUndanganRapat;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Surat Undangan Rapat Update Error: " . $e->getMessage());
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
            Log::error("Surat Undangan Rapat Delete Error: " . $e->getMessage());
            return false;
        }
    }

    public function generatePdf($id)
    {
        $suratUndanganRapat = SuratUndanganRapat::findOrFail($id);
        $suratUndanganRapat->load('details');
        $profileUser = Auth::user();
        $pdf = Pdf::loadView("administrasi.surat.surat-undangan-rapat.template-pdf", [
            "agendaJanjiTemu" => $suratUndanganRapat,
            "profileUser" => $profileUser
        ])->setPaper("a4", "portrait");

        return $pdf->download(
            Str::slug("surat-undangan-rapat-" . $suratUndanganRapat->perihal) . ".pdf",
        );
    }

}
