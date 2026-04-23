<?php

namespace App\Http\Controllers\Rapat;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log};
use App\Http\Controllers\Controller;
use App\Http\Requests\SuratUndanganRapatRequest;
use App\Models\SuratUndanganRapat;
use App\Services\SuratUndanganRapatService;
use Throwable;

class SuratUndanganRapatController extends Controller
{
    public function index()
    {
        $agendaSuratUndanganRapat = SuratUndanganRapat::where(
            'user_id',
            auth()->id(),
        )->get();

        return view(
            'administrasi.surat.surat-undangan-rapat.index',
            compact('agendaSuratUndanganRapat'),
        );
    }

    public function create()
    {
        $user = auth()->user();

        return view('administrasi.surat.surat-undangan-rapat.create', compact('user'));
    }

    public function store(SuratUndanganRapatRequest $request)
    {
        $data = $request->validated();
        $agendaService = app(SuratUndanganRapatService::class);
        $surat = $agendaService->store($data);
        if (!$surat) {
            return back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data')
                ->withInput();
        }

        return redirect()
            ->route('administrasi.surat-undangan-rapat.index')
            ->with('success', 'Surat undangan rapat berhasil disimpan.');
    }

    public function edit($id)
    {
        $suratUndanganRapat = SuratUndanganRapat::with('details')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        $user = auth()->user();

        return view(
            'administrasi.surat.surat-undangan-rapat.edit',
            compact('suratUndanganRapat', 'user'),
        );
    }

    public function update(SuratUndanganRapatRequest $request, $id)
    {
        $data = $request->validated();
        $agendaService = app(SuratUndanganRapatService::class);
        $surat = $agendaService->update($id, $data);
        
        if (!$surat) {
            return back()
                ->with('error', 'Terjadi kesalahan saat memperbarui data')
                ->withInput();
        }

        return redirect()
            ->route('administrasi.surat-undangan-rapat.index')
            ->with('success', 'Surat undangan rapat berhasil diperbarui.');
    }

    public function generatePdf($id)
    {
        $suratUndanganRapatService = app(SuratUndanganRapatService::class);

        return $suratUndanganRapatService->generatePdf($id);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $suratUndanganRapatService = app(SuratUndanganRapatService::class);
            $suratUndanganRapat = SuratUndanganRapat::where(
                'user_id',
                auth()->id(),
            )->findOrFail($id);
            $suratUndanganRapatService->delete($suratUndanganRapat->id);
            DB::commit();

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Agenda surat undangan rapat berhasil dihapus.',
                );
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(
                'Gagal menghapus pada surat undangan rapat. Error: ' .
                $e->getMessage(),
            );

            return back()->with(
                'error',
                'Terjadi kesalahan saat menghapus surat undangan rapat.',
            );
        }
    }
}
