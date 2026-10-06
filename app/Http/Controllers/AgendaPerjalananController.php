<?php

namespace App\Http\Controllers;

use App\Models\AgendaPerjalanan;
use App\Services\AgendaSuratPerjalananService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AgendaPerjalananController extends Controller
{
    public function index(): View
    {
        $agenda = AgendaPerjalanan::where('user_id', auth()->id())->get();

        return view(
            'administrasi.surat.agenda-perjalanan.index',
            compact('agenda'),
        );
    }

    public function create(): View
    {
        return view('administrasi.surat.agenda-perjalanan.create');
    }

    public function show(int|string $id): View
    {
        $agendaPerjalanan = AgendaPerjalanan::with(
            'agendaPerjalananDetail',
            'agendaPerjalananAkomodasi',
            'agendaPerjalananKontak',
            'agendaPerjalananTransportasi',
        )
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        return view(
            'administrasi.surat.agenda-perjalanan.show',
            compact('agendaPerjalanan'),
        );
    }

    public function pdf(int|string $id): mixed
    {
        $agendaPerjalananService = app(AgendaSuratPerjalananService::class);

        return $agendaPerjalananService->generatePdf($id);
    }

    public function pdfSuratTugas(int|string $id): mixed
    {
        $agendaPerjalananService = app(AgendaSuratPerjalananService::class);

        return $agendaPerjalananService->generatePdfSuratTugas($id);
    }

    public function store(Request $request): RedirectResponse
    {
        $agendaPerjalananService = app(AgendaSuratPerjalananService::class);
        DB::beginTransaction();
        try {
            $agendaPerjalananService->store($request->all());
            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Agenda Perjalanan berhasil disimpan.');
        } catch (Throwable $e) {
            report($e);
            DB::rollBack();
            Log::error(
                'Gagal menyimpan pada Agenda Perjalanan. Error: '.
                $e->getMessage(),
            );

            return back()->with(
                'error',
                'Terjadi kesalahan saat menyimpan Agenda Perjalanan.',
            );
        }
    }

    public function destroy(int|string $id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $agendaPerjalananService = app(AgendaSuratPerjalananService::class);
            $agenda = AgendaPerjalanan::where(
                'user_id',
                auth()->id(),
            )->findOrFail($id);
            $agendaPerjalananService->delete($agenda);
            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Agenda perjalanan berhasil dihapus.');
        } catch (Throwable $e) {
            report($e);
            DB::rollBack();
            Log::error(
                'Gagal menghapus pada Agenda perjalanan. Error: '.
                $e->getMessage(),
            );

            return back()->with(
                'error',
                'Terjadi kesalahan saat menghapus agenda perjalanan.',
            );
        }
    }
}
