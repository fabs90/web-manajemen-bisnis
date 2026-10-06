<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgendaTelponRequest;
use App\Models\AgendaTelpon;
use App\Services\AgendaTelponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AgendaTelponController extends Controller
{
    public function index(): View
    {
        $agendaBelum = AgendaTelpon::where('user_id', auth()->id())
            ->where('is_done', false)
            ->get();

        $agendaSelesai = AgendaTelpon::where('user_id', auth()->id())
            ->where('is_done', true)
            ->get();

        return view(
            'administrasi.surat.agenda-telpon.index',
            compact('agendaBelum', 'agendaSelesai'),
        );
    }

    public function create(): View
    {
        return view('administrasi.surat.agenda-telpon.create');
    }

    public function show(int|string $id): View
    {
        $agenda = AgendaTelpon::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        return view('administrasi.surat.agenda-telpon.show', compact('agenda'));
    }

    public function store(AgendaTelponRequest $request): RedirectResponse
    {
        $data = array_merge([
            'perusahaan' => null,
            'nomor_telpon' => null,
            'jadwal_tanggal' => null,
            'jadwal_waktu' => null,
            'jadwal_dengan' => null,
            'catatan_khusus' => null,
        ], $request->validated());

        $agendaTelponService = app(AgendaTelponService::class);

        DB::beginTransaction();
        try {
            $agendaTelponService->store($data);
            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Agenda telepon berhasil disimpan.');
        } catch (Throwable $e) {
            report($e);
            DB::rollBack();
            Log::error('Gagal menyimpan pada Agenda telepon. Error: '.$e->getMessage());

            return back()->with(
                'error',
                'Terjadi kesalahan saat menyimpan agenda telepon.',
            );
        }
    }

    public function update(int|string $id, Request $request): RedirectResponse
    {
        $agenda = AgendaTelpon::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $agenda->tgl_panggilan = $request->tgl_panggilan;
        $agenda->waktu_panggilan = $request->waktu_panggilan;
        $agenda->nama_penelpon = $request->nama_penelpon;
        $agenda->perusahaan = $request->perusahaan;
        $agenda->nomor_telpon = $request->nomor_telpon;
        $agenda->jadwal_tanggal = $request->jadwal_tanggal;
        $agenda->jadwal_waktu = $request->jadwal_waktu;
        $agenda->jadwal_dengan = $request->jadwal_dengan;
        $agenda->keperluan = $request->keperluan;
        $agenda->tingkat_status = $request->tingkat_status;
        $agenda->catatan_khusus = $request->catatan_khusus;
        $agenda->status = $request->status;
        $agenda->dicatat_oleh = $request->dicatat_oleh;
        $agenda->dicatat_tgl = $request->dicatat_tgl;

        $agenda->save();

        return redirect()
            ->route('administrasi.agenda-telpon.index')
            ->with('success', 'Agenda Telpon berhasil diperbarui!');
    }

    public function updateIsDone(int|string $id): RedirectResponse
    {
        $agenda = AgendaTelpon::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $agenda->is_done = ! $agenda->is_done;
        $agenda->save();

        return back()->with('success', 'Agenda berhasil ditandai!');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $agendaTelponService = app(AgendaTelponService::class);
            $agenda = AgendaTelpon::where('user_id', auth()->id())->findOrFail($id);
            $agendaTelponService->delete($agenda);
            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Agenda telepon berhasil dihapus.');
        } catch (Throwable $e) {
            report($e);
            DB::rollBack();
            Log::error('Gagal menghapus pada Agenda telepon. Error: '.$e->getMessage());

            return back()->with(
                'error',
                'Terjadi kesalahan saat menghapus agenda telepon.',
            );
        }
    }
}
