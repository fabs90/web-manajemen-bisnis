<?php

namespace App\Http\Controllers\Rapat;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgendaRapatRequest;
use App\Models\Rapat\AgendaRapat;
use App\Services\ManajemenRapatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotulenRapatController extends Controller
{
    public function __construct(protected ManajemenRapatService $service) {}

    public function index()
    {
        $agendaRapat = AgendaRapat::where('user_id', auth()->id())->get();

        return view(
            'administrasi.surat.notulen-rapat.index',
            compact('agendaRapat'),
        );
    }

    public function create()
    {
        return view('administrasi.surat.notulen-rapat.create');
    }

    public function edit($rapatId)
    {
        $rapat = AgendaRapat::with([
            'rapatDetails',
            'pesertaRapat',
            'tindakLanjutRapat',
        ])->findOrFail($rapatId);

        return view('administrasi.surat.notulen-rapat.edit', compact('rapat'));
    }

    public function generatePdf($rapatId)
    {
        return $this->service->generatePdf($rapatId);
    }

    public function update($rapatId, Request $request)
    {
        try {
            $this->service->update(
                $rapatId,
                $request->all(),
            );

            return redirect()
                ->route('administrasi.rapat.edit', $rapatId)
                ->with('success', 'Agenda rapat berhasil diperbarui.');
        } catch (\Throwable $th) {
            Log::error('Update rapat error', [
                'message' => $th->getMessage(),
                'id' => $rapatId,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui agenda rapat.');
        }
    }

    public function store(AgendaRapatRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['user_id'] = auth()->user()->id;
        try {
            $this->service->store($validatedData);

            return redirect()
                ->route('administrasi.rapat.index')
                ->with('success', 'Notulen rapat dan hasil keputusan rapat berhasil ditambahkan.');
        } catch (\Throwable $th) {
            Log::error('Store rapat error', [
                'message' => $th->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menambahkan agenda rapat: '.$th->getMessage(),
                );
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->destroy($id);

            return redirect()
                ->route('administrasi.rapat.index')
                ->with('success', 'Agenda rapat berhasil dihapus.');
        } catch (\Throwable $th) {
            Log::error('Delete rapat error', [
                'message' => $th->getMessage(),
                'id' => $id,
            ]);

            return back()->with('error', 'Gagal menghapus agenda rapat.');
        }
    }

    public function indexHasilKeputusan()
    {
        $hasilKeputusan = AgendaRapat::with('hasilKeputusanRapat')
            ->where('user_id', Auth::id())
            ->get();

        return view(
            'administrasi.surat.hasil-keputusan.index',
            compact('hasilKeputusan'),
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
