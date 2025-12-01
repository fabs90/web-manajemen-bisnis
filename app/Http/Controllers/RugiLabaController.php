<?php

namespace App\Http\Controllers;

use App\Models\BukuBesarHutang;
use App\Models\BukuBesarPendapatan;
use App\Models\BukuBesarPengeluaran;
use App\Models\BukuBesarPiutang;
use App\Models\KartuGudang;
use App\Models\NeracaAwal;
use App\Services\KeuanganService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class RugiLabaController extends Controller
{
    public function index(Request $request, KeuanganService $keuangan)
    {
        $startDate = $request->input(
            "start_date",
            now()->startOfYear()->format("Y-m-d"),
        );
        $endDate = $request->input(
            "end_date",
            now()->endOfYear()->format("Y-m-d"),
        );

        $data = $keuangan->hitungLabaRugi($startDate, $endDate);

        return view(
            "keuangan.rugi-laba.list",
            array_merge($data, [
                "startDate" => $startDate,
                "endDate" => $endDate,
            ]),
        );
    }

    public function exportToPdf(Request $request, KeuanganService $keuangan)
    {
        $startDate = $request->input(
            "start_date",
            now()->startOfYear()->toDateString(),
        );
        $endDate = $request->input(
            "end_date",
            now()->endOfYear()->toDateString(),
        );
        $userId = Auth::id();

        // === Gunakan Service yg sama seperti index() untuk konsistensi ===
        $data = $keuangan->hitungLabaRugi($startDate, $endDate);

        // === Tambahkan tanggal ke array data ===
        $data["startDate"] = $startDate;
        $data["endDate"] = $endDate;
        $userProfile = Auth::user();
        // === Generate PDF ===
        $pdf = Pdf::loadView(
            "keuangan.rugi-laba.pdf",
            array_merge($data, [
                "startDate" => $startDate,
                "endDate" => $endDate,
                "userProfile" => $userProfile, // wajib
            ]),
        )->setPaper("a4", "portrait");

        return $pdf->download(
            "laporan-rugi-laba-" . now()->format("Y-m-d") . ".pdf",
        );
    }
}
