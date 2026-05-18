<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Services\PernyataanPiutangService;
use Barryvdh\DomPDF\Facade\Pdf;

class PernyataanPiutangController extends Controller
{
    public function __construct(protected PernyataanPiutangService $service)
    {
    }

    public function index()
    {
        $dataPiutang = $this->service->getDaftarPiutang();

        return view(
            'administrasi.surat.pernyataan-piutang.index',
            compact('dataPiutang')
        );
    }

    public function generatePdf($pelangganId)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->back()->with('error', 'Silakan login terlebih dahulu.');
        }

        $pelanggan = Pelanggan::findOrFail($pelangganId);
        $totalPiutang = $this->service->getTotalPiutangPelanggan($pelangganId);

        $pdf = Pdf::setOptions([
            'isRemoteEnabled' => true, // Diaktifkan agar bisa memuat gambar jika perlu
            'isHtml5ParserEnabled' => true,
        ])
            ->loadView('administrasi.surat.pernyataan-piutang.template-pdf', [
                'pelanggan' => $pelanggan,
                'totalPiutang' => $totalPiutang,
                'profileUser' => $user,
            ])
            ->setPaper('A4');

        return $pdf->download("Pernyataan-Piutang-{$pelanggan->nama}.pdf");
    }
}
