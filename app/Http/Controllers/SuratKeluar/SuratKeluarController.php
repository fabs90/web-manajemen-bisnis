<?php

namespace App\Http\Controllers\SuratKeluar;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class SuratKeluarController extends Controller
{
    public function downloadPdf(int $id)
    {
        $suratKeluar = DB::table('agenda_surat_keluar')->find($id);
        if (!$suratKeluar) {
            abort(404, 'Data surat keluar tidak ditemukan.');
        }
        $fileName = 'surat-keluar-' . Str::slug($suratKeluar->nomor_surat ?? 'dokumen') . '.pdf';
        $pdf = Pdf::loadView('emails.surat-keluar-pdf', [
            'surat' => $suratKeluar,
            'user' => auth()->user(),
        ]);
        return $pdf->download($fileName);
    }
}
