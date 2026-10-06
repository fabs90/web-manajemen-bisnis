<?php

namespace App\Http\Controllers;

use App\Models\AgendaJanjiTemu;
use App\Services\AgendaJanjiTemuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AgendaJanjiTemuController extends Controller
{
    public function index(): View
    {
        $agendaJanjiTemu = AgendaJanjiTemu::where(
            'user_id',
            auth()->id(),
        )->get();

        return view(
            'administrasi.surat.janji-temu.index',
            compact('agendaJanjiTemu'),
        );
    }

    public function create(): View
    {
        return view('administrasi.surat.janji-temu.create');
    }

    public function show(int|string $id): View
    {
        $agendaJanjiTemuService = app(AgendaJanjiTemuService::class);
        $agendaJanjiTemu = $agendaJanjiTemuService->show($id);

        return view(
            'administrasi.surat.janji-temu.show',
            compact('agendaJanjiTemu'),
        );
    }

    public function pdf(int|string $id): mixed
    {
        $agendaJanjiTemuService = app(AgendaJanjiTemuService::class);

        return $agendaJanjiTemuService->generatePdf($id);
    }

    public function store(Request $request): RedirectResponse
    {
        $agendaJanjiTemuService = app(AgendaJanjiTemuService::class);
        DB::beginTransaction();
        $data = $request->all();
        $data['user_id'] = auth()->id();
        try {
            $agendaJanjiTemuService->store($data);
            DB::commit();

            return redirect()
                ->route('administrasi.janji-temu.index')
                ->with('success', 'Agenda Janji Temu berhasil disimpan.');
        } catch (Throwable $e) {
            report($e);
            DB::rollBack();
            Log::error(
                'Gagal menyimpan pada Agenda Janji Temu. Error: '.
                $e->getMessage(),
            );

            return back()
                ->with(
                    'error',
                    'Terjadi kesalahan saat menyimpan Agenda Janji Temu.',
                );
        }
    }

    public function destroy(int|string $id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $agendaJanjiTemuService = app(AgendaJanjiTemuService::class);
            $agenda = AgendaJanjiTemu::where(
                'user_id',
                auth()->id(),
            )->findOrFail($id);
            $agendaJanjiTemuService->delete($agenda->id);
            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Agenda janji temu berhasil dihapus.');
        } catch (Throwable $e) {
            report($e);
            DB::rollBack();
            Log::error(
                'Gagal menghapus pada Agenda janji temu. Error: '.
                $e->getMessage(),
            );

            return back()->with(
                'error',
                'Terjadi kesalahan saat menghapus agenda janji temu.',
            );
        }
    }
}
