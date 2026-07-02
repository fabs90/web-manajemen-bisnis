<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgendaTelponRequest;
use App\Models\AgendaJanjiTemu;
use App\Models\AgendaPerjalanan;
use App\Models\AgendaTelpon;
use App\Services\AgendaJanjiTemuService;
use App\Services\AgendaSuratPerjalananService;
use App\Services\AgendaTelponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdministrasiSuratController extends Controller
{
    public function index()
    {
        return view('administrasi.surat.index');
    }

    public function indexAgendaTelpon()
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

    public function showAgendaTelpon($id)
    {
        $agenda = AgendaTelpon::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        return view('administrasi.surat.agenda-telpon.show', compact('agenda'));
    }

    public function indexAgendaPerjalanan()
    {
        $agenda = AgendaPerjalanan::where('user_id', auth()->id())->get();

        return view(
            'administrasi.surat.agenda-perjalanan.index',
            compact('agenda'),
        );
    }

    public function indexJanjiTemu()
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

    public function createAgendaTelpon()
    {
        return view('administrasi.surat.agenda-telpon.create');
    }

    public function createAgendaPerjalanan()
    {
        return view('administrasi.surat.agenda-perjalanan.create');
    }

    public function createJanjiTemu()
    {
        return view('administrasi.surat.janji-temu.create');
    }

    public function showJanjiTemu($id)
    {
        $agendaJanjiTemuService = app(AgendaJanjiTemuService::class);
        $agendaJanjiTemu = $agendaJanjiTemuService->show($id);

        return view(
            'administrasi.surat.janji-temu.show',
            compact('agendaJanjiTemu'),
        );
    }

    public function showAgendaPerjalanan($id)
    {
        $agendaPerjalanan = AgendaPerjalanan::with(
            'agendaPerjalananDetail',
            'agendaPerjalananAkomodasi',
            'agendaPerjalananKontak',
            'agendaPerjalananTransportasi',
        )
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        return view(
            'administrasi.surat.agenda-perjalanan.show',
            compact('agendaPerjalanan'),
        );
    }

    public function pdfJanjiTemu($id)
    {
        $agendaJanjiTemuService = app(AgendaJanjiTemuService::class);

        return $agendaJanjiTemuService->generatePdf($id);
    }

    public function pdfAgendaPerjalanan($id)
    {
        $agendaPerjalananService = app(AgendaSuratPerjalananService::class);

        return $agendaPerjalananService->generatePdf($id);
    }

    public function storeAgendaTelpon(AgendaTelponRequest $request)
    {
        $data = $request->validated();
        $agendaTelponService = app(AgendaTelponService::class);
        DB::beginTransaction();
        try {
            $agendaTelponService->store($data);
            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Agenda telepon berhasil disimpan.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(
                'Gagal menyimpan pada Agenda telepon. Error: '.
                $e->getMessage(),
            );

            return back()->with(
                'error',
                'Terjadi kesalahan saat menyimpan agenda telepon.',
            );
        }
    }

    public function storeAgendaPerjalanan(Request $request)
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

    public function storeJanjiTemu(Request $request)
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

    public function updateAgendaTelpon($id, Request $request)
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

    public function updateIsDone($id)
    {
        $agenda = AgendaTelpon::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();
        // Ubah status is_done ke true
        $agenda->is_done = ! $agenda->is_done;
        $agenda->save();

        return back()->with('success', 'Agenda berhasil ditandai!');
    }

    public function destroyAgendaTelpon($id)
    {
        DB::beginTransaction();
        try {
            $agendaTelponService = app(AgendaTelponService::class);
            $agenda = AgendaTelpon::where('user_id', auth()->id())->findOrFail(
                $id,
            );
            $agendaTelponService->delete($agenda);
            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Agenda telepon berhasil dihapus.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(
                'Gagal menghapus pada Agenda telepon. Error: '.
                $e->getMessage(),
            );

            return back()->with(
                'error',
                'Terjadi kesalahan saat menghapus agenda telepon.',
            );
        }
    }

    public function destroyAgendaPerjalanan($id)
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

    public function destroyJanjiTemu($id)
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
