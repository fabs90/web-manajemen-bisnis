<?php

namespace App\Http\Controllers\Rapat;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log};
use App\Http\Requests\AgendaRapatRequest;
use App\Models\Rapat\{AgendaRapat, HasilKeputusanRapat};
use App\Http\Controllers\Controller;
use App\Services\ManajemenRapatService;

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

    public function create()
    {
        return view("administrasi.surat.notulen-rapat.create");
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

    public function store(AgendaRapatRequest $request)
    {
        $validatedData = $request->validated();
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


    public function generatePdfHasilKeputusan($rapatId)
    {
        $generateHasilKeputusanService = app(ManajemenRapatService::class);
        return $generateHasilKeputusanService->generatePdfHasilKeputusan(
            $rapatId,
        );
    }

}
