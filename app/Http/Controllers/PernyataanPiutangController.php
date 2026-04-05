<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Barryvdh\DomPDF\Facade\Pdf;

class PernyataanPiutangController extends Controller
{
    public function index()
    {
        $dataPiutang = \App\Models\BukuBesarPiutang::where(
            'user_id',
            auth()->id()
        )
            ->with('pelanggan')
            ->latest()
            ->get()
            ->unique('pelanggan_id')
            ->values();

        return view(
            'administrasi.surat.pernyataan-piutang.index',
            compact('dataPiutang')
        );
    }

    public function generatePdf($pelangganId)
    {
        $user = auth()->user();
        $pelanggan = Pelanggan::findOrFail($pelangganId);
        $items = \App\Models\BukuBesarPiutang::where("user_id", $user->id)
            ->where("pelanggan_id", $pelangganId)
            ->latest()->first();

        $totalPiutang = $items->saldo;
        if (empty($totalPiutang)) {
            $totalPiutang = 0;
        }
        $pdf = Pdf::setOptions([
            "isRemoteEnabled" => true,
        ])
            ->loadView("administrasi.surat.pernyataan-piutang.template-pdf", [
                "pelanggan" => $pelanggan,
                "totalPiutang" => $totalPiutang,
                "profileUser" => $user,
            ])
            ->setPaper("A4");

        return $pdf->download("Pernyataan-Piutang-{$pelanggan->nama}.pdf");
    }
}
