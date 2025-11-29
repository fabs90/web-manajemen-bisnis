<?php

namespace App\Http\Controllers\Rapat;

use App\Http\Controllers\Controller;
use App\Models\Rapat\AgendaRapat;
use Illuminate\Http\Request;
use App\Services\ManajemenRapatService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ManajemenRapatController extends Controller
{
    public function index()
    {
        $agendaRapat = AgendaRapat::where("user_id", auth()->user()->id)->get();
        return view(
            "administrasi.surat.notulen-rapat.index",
            compact("agendaRapat"),
        );
    }

    public function indexHasilKeputusan()
    {
        $hasilKeputusan = AgendaRapat::with("hasilKeputusanRapat")
            ->where("user_id", Auth::id())
            ->get();
        return view(
            "administrasi.surat.hasil-keputusan.index",
            compact("hasilKeputusan"),
        );
    }

    public function create()
    {
        return view("administrasi.surat.notulen-rapat.create");
    }

    public function createHasilKeputusan()
    {
        $agendaRapat = AgendaRapat::with("hasilKeputusanRapat")
            ->where("user_id", Auth::id())
            ->whereDoesntHave("hasilKeputusanRapat")
            ->orderBy("tanggal", "desc")
            ->get();
        return view(
            "administrasi.surat.hasil-keputusan.create",
            compact("agendaRapat"),
        );
    }

    public function edit($rapatId)
    {
        $rapat = AgendaRapat::with([
            "rapatDetails",
            "pesertaRapat",
            "tindakLanjutRapat",
        ])->findOrFail($rapatId);
        return view("administrasi.surat.notulen-rapat.edit", compact("rapat"));
    }

    public function generatePdf($rapatId)
    {
        $agendaJanjiTemuService = app(ManajemenRapatService::class);
        return $agendaJanjiTemuService->generatePdf($rapatId);
    }

    public function generatePdfHasilKeputusan($rapatId)
    {
        $generateHasilKeputusanService = app(ManajemenRapatService::class);
        return $generateHasilKeputusanService->generatePdfHasilKeputusan(
            $rapatId,
        );
    }

    public function update($rapatId, Request $request)
    {
        $validatedData = $request->all();
        try {
            $manajemenRapatServices = app(ManajemenRapatService::class);
            $manajemenRapatServices->update($rapatId, $validatedData);

            return redirect()
                ->route("administrasi.rapat.edit", $rapatId)
                ->with("success", "Agenda rapat berhasil diperbarui.");
        } catch (\Throwable $th) {
            Log::error("Gagal memperbarui agenda rapat: " . $th->getMessage());
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Gagal memperbarui agenda rapat: " . $th->getMessage(),
                );
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->all();
        $validatedData["user_id"] = auth()->user()->id;

        try {
            $manajemenRapatServices = app(ManajemenRapatService::class);
            $manajemenRapatServices->store($validatedData);

            return redirect()
                ->route("administrasi.rapat.index")
                ->with("success", "Agenda rapat berhasil ditambahkan.");
        } catch (\Throwable $th) {
            Log::error("Gagal menambahkan agenda rapat: " . $th->getMessage());
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Gagal menambahkan agenda rapat: " . $th->getMessage(),
                );
        }
    }

    public function storeHasilKeputusan(Request $request)
    {
        try {
            $manajemenRapatServices = app(ManajemenRapatService::class);
            $manajemenRapatServices->storeHasilKeputusan($request->all());

            return redirect()
                ->route("administrasi.rapat.hasil-keputusan.index")
                ->with("success", "Agenda rapat berhasil ditambahkan.");
        } catch (\Throwable $th) {
            Log::error(
                "Gagal menambahkan hasil keputusan rapat: " . $th->getMessage(),
            );
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Gagal menambahkan agenda rapat: " . $th->getMessage(),
                );
        }
    }

    public function destroy($id)
    {
        try {
            $manajemenRapatServices = app(ManajemenRapatService::class);
            $manajemenRapatServices->destroy($id);

            return redirect()
                ->route("administrasi.rapat.index")
                ->with("success", "Agenda rapat berhasil dihapus.");
        } catch (\Throwable $th) {
            Log::error("Gagal menghapus agenda rapat: " . $th->getMessage());
            return back()->with(
                "error",
                "Gagal menghapus agenda rapat: " . $th->getMessage(),
            );
        }
    }

    public function destroyHasilKeputusan($id)
    {
        try {
            $manajemenRapatServices = app(ManajemenRapatService::class);
            $manajemenRapatServices->destroyHasilKeputusan($id);

            return redirect()
                ->route("administrasi.rapat.hasil-keputusan.index")
                ->with("success", "Hasil keputusan rapat berhasil dihapus.");
        } catch (\Throwable $th) {
            Log::error(
                "Gagal menghapus hasil keputusan rapat: " . $th->getMessage(),
            );
            return back()->with(
                "error",
                "Gagal menghapus hasil keputusan rapat: " . $th->getMessage(),
            );
        }
    }
}
