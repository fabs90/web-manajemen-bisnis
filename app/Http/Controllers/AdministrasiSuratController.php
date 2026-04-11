<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgendaTelponRequest;
use App\Http\Requests\PermintaanKasKecilRequest;
use App\Http\Requests\SuratKeluarRequest;
use App\Mail\SuratKeluarMail;
use App\Models\AgendaJanjiTemu;
use App\Models\AgendaPerjalanan;
use App\Models\AgendaSuratKeluar;
use App\Models\AgendaTelpon;
use App\Models\KasKecil;
use App\Models\KasKecilDetail;
use App\Models\KasKecilFormulir;
use App\Models\SuratKeluarEmailLog;
use App\Models\SuratUndanganRapat;
use App\Services\AgendaJanjiTemuService;
use App\Services\AgendaSuratPerjalananService;
use App\Services\AgendaTelponService;
use App\Services\FileUploadService;
use App\Services\PermintaanKasKecilService;
use App\Services\SuratUndanganRapatService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AdministrasiSuratController extends Controller
{
    public function __construct(protected FileUploadService $fileUploadService) {}

    private function cleanRupiah($value)
    {
        return (int) preg_replace("/\D/", '', $value);
    }

    public function index()
    {
        return view('administrasi.surat.index');
    }

    public function indexSuratKeluar()
    {
        $suratKeluar = AgendaSuratKeluar::where('user_id', auth()->id())
            ->select('id', 'nomor_surat', 'tanggal_surat', 'nama_penerima', 'perihal', 'tembusan')
            ->get();

        return view(
            'administrasi.surat.surat-keluar.index',
            compact('suratKeluar'),
        );
    }

    public function indexKasKecil()
    {
        $kasKecil = KasKecil::where('user_id', auth()->id())->get();
        $saldoAkhir = KasKecil::where('user_id', auth()->id())
            ->latest()
            ->value('saldo_akhir');

        return view(
            'administrasi.surat.kas-kecil.index',
            compact('kasKecil', 'saldoAkhir'),
        );
    }

    public function indexAgendaTelpon()
    {
        $agendaBelum = AgendaTelpon::where('user_id', auth()->id())
            ->where('is_done', false)
            ->orderBy('tgl_panggilan', 'desc')
            ->get();

        $agendaSelesai = AgendaTelpon::where('user_id', auth()->id())
            ->where('is_done', true)
            ->orderBy('tgl_panggilan', 'desc')
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

    public function indexSuratUndanganRapat()
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

    public function createSuratKeluar()
    {
        return view('administrasi.surat.surat-keluar.create');
    }

    public function createKasKecil()
    {
        return view('administrasi.surat.kas-kecil.create');
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

    public function createSuratUndanganRapat()
    {
        $user = auth()->user();

        return view('administrasi.surat.surat-undangan-rapat.create', compact('user'));
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

    public function pdfPermintaanKasKecil($id)
    {
        $data = KasKecil::with(
            'kasKecilDetail',
            'kasKecilFormulir',
        )->findOrFail($id);
        $userProfile = Auth::user();

        $pdf = Pdf::loadView('administrasi.surat.kas-kecil.template-pdf', [
            'data' => $data,
            'userProfile' => $userProfile,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('permintaan-kas-kecil-'.$data->id.'.pdf');
    }

    public function pdfJanjiTemu($id)
    {
        $agendaJanjiTemuService = app(AgendaJanjiTemuService::class);

        return $agendaJanjiTemuService->generatePdf($id);
    }

    public function pdfSuratUndanganRapat($id)
    {
        $suratUndanganRapatService = app(SuratUndanganRapatService::class);

        return $suratUndanganRapatService->generatePdf($id);
    }

    public function pdfAgendaPerjalanan($id)
    {
        $agendaPerjalananService = app(AgendaSuratPerjalananService::class);

        return $agendaPerjalananService->generatePdf($id);
    }

    public function storeSuratKeluar(SuratKeluarRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['user_id'] = Auth::id();
            $fileLampiran = null;
            $ttdFile = null;
            $user = Auth::user();

            if ($request->hasFile('file_lampiran')) {
                $fileLampiran = $this->fileUploadService->upload($request->file('file_lampiran'), 'surat-keluar/lampiran', auth()->user()->email);
            }

            if ($request->hasFile('ttd')) {
                $ttdFile = $this->fileUploadService->upload($request->file('ttd'), 'surat-keluar/ttd', auth()->user()->email);
            }

            $surat = AgendaSuratKeluar::create([
                'user_id' => Auth::id(),
                'nomor_surat' => $request->nomor_surat,
                'lampiran' => $request->lampiran_text,
                'perihal' => $request->perihal,
                'tanggal_surat' => $request->tanggal_surat,

                // penerima
                'nama_penerima' => $request->nama_penerima,
                'jabatan_penerima' => $request->jabatan_penerima,
                'alamat_penerima' => $request->alamat_penerima,
                'email_penerima' => $request->email_penerima,

                // isi surat
                'paragraf_pembuka' => $request->paragraf_pembuka,
                'paragraf_isi' => $request->paragraf_isi,
                'paragraf_penutup' => $request->paragraf_penutup,

                // pengirim
                'nama_pengirim' => $request->nama_pengirim,
                'jabatan_pengirim' => $request->jabatan_pengirim,

                // tembusan
                'tembusan' => $request->tembusan,

                // file
                'ttd' => $ttdFile,
                'file_lampiran' => $fileLampiran,
            ]);
            Mail::to($data['email_penerima'])->send(
                new SuratKeluarMail($surat, $user),
            );

            SuratKeluarEmailLog::create([
                'surat_keluar_id' => $surat->id,
                'email' => $data['email_penerima'],
                'status' => 'success',
            ]);

            DB::commit();

            return redirect()
                ->route('administrasi.surat-keluar.index')
                ->with('success', 'Surat keluar berhasil dikirim ke penerima.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Surat keluar error: '.$e->getMessage());

            return back()->with(
                'error',
                'Terjadi kesalahan saat menyimpan surat keluar.',
            );
        }
    }

    public function storeKasKecil(
        PermintaanKasKecilRequest $request,
        PermintaanKasKecilService $service,
    ) {
        try {
            $service->store($request);

            return back()->with(
                'success',
                'Permintaan kas kecil berhasil disimpan.',
            );
        } catch (\Exception $e) {
            Log::error('Gagal simpan kas kecil: '.$e->getMessage(), [
                'user_id' => auth()->id(),
                'request' => $request->except(['_token', 'ttd_*']),
            ]);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage() ?:
                    'Terjadi kesalahan saat menyimpan data.',
                );
        }
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

            return back()->with(
                'error',
                'Terjadi kesalahan saat menyimpan Agenda Janji Temu.',
            );
        }
    }

    public function storeSuratUndanganRapat(Request $request)
    {
        $agendaService = app(SuratUndanganRapatService::class);

        $surat = $agendaService->store($request->all());

        if (! $surat) {
            return back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data')
                ->withInput();
        }

        return redirect()
            ->route('administrasi.surat-undangan-rapat.index')
            ->with('success', 'Surat undangan rapat berhasil disimpan.');
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

    public function destroyAgendaSuratKeluar($id)
    {
        DB::beginTransaction();
        try {
            $surat = AgendaSuratKeluar::findOrFail($id);

            // Hapus file lampiran jika ada
            if (
                $surat->file_lampiran &&
                Storage::disk('public')->exists($surat->file_lampiran)
            ) {
                Storage::disk('public')->delete($surat->file_lampiran);
            }

            // Hapus file ttd jika ada
            if ($surat->ttd && Storage::disk('public')->exists($surat->ttd)) {
                Storage::disk('public')->delete($surat->ttd);
            }

            // Hapus record surat keluar
            $surat->delete();

            DB::commit();

            return redirect()
                ->route('administrasi.surat-keluar.index')
                ->with('success', 'Surat keluar berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal menghapus Surat Keluar: '.$e->getMessage());

            return back()->with(
                'error',
                'Terjadi kesalahan saat menghapus surat.',
            );
        }
    }

    public function destroyKasKecil($kasId)
    {
        DB::beginTransaction();
        try {
            // Ambil kas kecil
            $kas = KasKecil::where('id', $kasId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            // Hapus detail kas kecil
            KasKecilDetail::where('kas_kecil_id', $kasId)->delete();

            // Ambil formulirnya
            $formulir = KasKecilFormulir::where(
                'kas_kecil_id',
                $kasId,
            )->first();

            // Hapus file ttd jika ada
            if ($formulir) {
                if ($formulir->ttd_nama_pemohon) {
                    Storage::disk('public')->delete(
                        $formulir->ttd_nama_pemohon,
                    );
                }
                if ($formulir->ttd_atasan_langsung) {
                    Storage::disk('public')->delete(
                        $formulir->ttd_atasan_langsung,
                    );
                }
                if ($formulir->ttd_bagian_keuangan) {
                    Storage::disk('public')->delete(
                        $formulir->ttd_bagian_keuangan,
                    );
                }

                // Hapus record formulir
                $formulir->delete();
            }

            // Terakhir, hapus kas kecil header
            $kas->delete();

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Data kas kecil berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Delete kas kecil error: '.$e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus data kas kecil.');
        }
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

    public function destroySuratUndanganRapat($id)
    {
        DB::beginTransaction();
        try {
            $suratUndanganRapatService = app(suratUndanganRapatService::class);
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
                'Gagal menghapus pada surat undangan rapat. Error: '.
                $e->getMessage(),
            );

            return back()->with(
                'error',
                'Terjadi kesalahan saat menghapus surat undangan rapat.',
            );
        }
    }
}
