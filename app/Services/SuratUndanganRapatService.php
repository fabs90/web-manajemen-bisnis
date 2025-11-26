<?php

namespace App\Services;

use App\Models\SuratUndanganRapat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SuratUndanganRapatService
{
    /**
     * Create a new class instance.
     */
    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $data['user_id'] = Auth::id();

            $suratUndanganRapat = SuratUndanganRapat::create($data);

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
            "surat-undangan-rapat-" . $suratUndanganRapat->id . ".pdf",
        );
    }

}
