<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PernyataanPiutangController extends Controller
{
    public function index()
    {
        $dataPiutang = \App\Models\BukuBesarPiutang::where(
            "user_id",
            auth()->user()->id,
        )
            ->with("pelanggan")
            ->get()
            ->groupBy("pelanggan_id");
        return view(
            "administrasi.surat.pernyataan-piutang.index",
            compact("dataPiutang"),
        );
    }

    public function generatePdf($pelangganId)
    {
        $user = auth()->user();
        $pelanggan = Pelanggan::findOrFail($pelangganId);
        $items = \App\Models\BukuBesarPiutang::where("user_id", $user->id)
            ->where("pelanggan_id", $pelangganId)
            ->get();
        $totalPiutang = $items->sum("debit") - $items->sum("kredit");
        if ($totalPiutang < 0) {
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
