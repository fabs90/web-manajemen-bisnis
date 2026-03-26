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
        $agendaRapat = AgendaRapat::where("user_id", auth()->id())->get();

        return view("administrasi.surat.notulen-rapat.index", compact("agendaRapat"));
    }

    public function indexHasilKeputusan()
    {
        $hasilKeputusan = AgendaRapat::with("hasilKeputusanRapat")
            ->where("user_id", Auth::id())
            ->get();

        return view("administrasi.surat.hasil-keputusan.index", compact("hasilKeputusan"));
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
            ->latest("tanggal")
            ->get();

        return view("administrasi.surat.hasil-keputusan.create", compact("agendaRapat"));
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

    public function generatePdfHasilKeputusan($rapatId)
    {
        return app(ManajemenRapatService::class)
            ->generatePdfHasilKeputusan($rapatId);
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

    public function store(Request $request)
    {
        $data = $request->all();
        $data["user_id"] = auth()->id();

        try {
            app(ManajemenRapatService::class)->store($data);

            return redirect()
                ->route("administrasi.rapat.index")
                ->with("success", "Agenda rapat berhasil ditambahkan.");

        } catch (\Throwable $th) {
            Log::error("Store rapat error", [
                'message' => $th->getMessage(),
                'payload' => $data
            ]);

            // 🎯 Filter error biar user friendly
            $message = "Gagal menambahkan agenda rapat.";

            if (str_contains($th->getMessage(), "Column 'nama' cannot be null")) {
                $message = "Nama peserta rapat wajib diisi.";
            }

            return back()
                ->withInput()
                ->with("error", $message);
        }
    }

    public function storeHasilKeputusan(Request $request)
    {
        try {
            app(ManajemenRapatService::class)
                ->storeHasilKeputusan($request->all());

            return redirect()
                ->route("administrasi.rapat.hasil-keputusan.index")
                ->with("success", "Hasil keputusan rapat berhasil ditambahkan.");

        } catch (\Throwable $th) {
            Log::error("Store hasil keputusan error", [
                'message' => $th->getMessage()
            ]);

            return back()
                ->withInput()
                ->with("error", "Gagal menambahkan hasil keputusan rapat.");
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

    public function destroyHasilKeputusan($id)
    {
        try {
            app(ManajemenRapatService::class)
                ->destroyHasilKeputusan($id);

            return redirect()
                ->route("administrasi.rapat.hasil-keputusan.index")
                ->with("success", "Hasil keputusan rapat berhasil dihapus.");

        } catch (\Throwable $th) {
            Log::error("Delete hasil keputusan error", [
                'message' => $th->getMessage(),
                'id' => $id
            ]);

            return back()->with("error", "Gagal menghapus hasil keputusan rapat.");
        }
    }
}