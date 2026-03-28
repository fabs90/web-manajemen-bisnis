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
        $agendaRapat = AgendaRapat::where("user_id", auth()->id())->get();

        return view("administrasi.surat.notulen-rapat.index", compact("agendaRapat"));
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
        return app(ManajemenRapatService::class)->generatePdf($rapatId);
    }

    public function update($rapatId, Request $request)
    {
        try {
            app(ManajemenRapatService::class)
                ->update($rapatId, $request->all());

            return redirect()
                ->route("administrasi.rapat.edit", $rapatId)
                ->with("success", "Agenda rapat berhasil diperbarui.");

        } catch (\Throwable $th) {
            Log::error("Update rapat error", [
                'message' => $th->getMessage(),
                'id' => $rapatId
            ]);

            return back()
                ->withInput()
                ->with("error", "Gagal memperbarui agenda rapat.");
        }
    }

    public function store(AgendaRapatRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData["user_id"] = auth()->user()->id;

        try {
            app(ManajemenRapatService::class)->store($validatedData);

            return redirect()
                ->route("administrasi.rapat.index")
                ->with("success", "Agenda rapat berhasil ditambahkan.");

        } catch (\Throwable $th) {
            Log::error("Store rapat error", [
                'message' => $th->getMessage(),
                'payload' => $validatedData
            ]);

            $message = "Gagal menambahkan agenda rapat.";

            if (str_contains($th->getMessage(), "Column 'nama' cannot be null")) {
                $message = "Nama peserta rapat wajib diisi.";
            }

            return back()
                ->withInput()
                ->with("error", $message);
        }
    }

    public function destroy($id)
    {
        try {
            app(ManajemenRapatService::class)->destroy($id);

            return redirect()
                ->route("administrasi.rapat.index")
                ->with("success", "Agenda rapat berhasil dihapus.");

        } catch (\Throwable $th) {
            Log::error("Delete rapat error", [
                'message' => $th->getMessage(),
                'id' => $id
            ]);

            return back()->with("error", "Gagal menghapus agenda rapat.");
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