<?php

namespace App\Http\Controllers\SuratKeluar;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

final class SuratKeluarController extends Controller
{
    public function downloadPdf($id)
    {
        $suratKeluar = DB::table('agenda_surat_keluar')->where('id', $id)->first();
        $pdf = Pdf::loadView('emails.surat-keluar-pdf', [
            'surat' => $suratKeluar,
            'user' => auth()->user(),
        ]);
        return $pdf->stream('surat-keluar-' . $suratKeluar->id . '.pdf');
    }
}
