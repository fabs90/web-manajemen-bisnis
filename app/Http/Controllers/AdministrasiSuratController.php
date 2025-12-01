<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgendaTelponRequest;
use App\Http\Requests\PermintaanKasKecilRequest;
use App\Http\Requests\SuratKeluarRequest;
use App\Http\Requests\SuratMasukRequest;
use App\Mail\SuratKeluarMail;
use App\Models\AgendaJanjiTemu;
use App\Models\AgendaPerjalanan;
use App\Models\AgendaSuratKeluar;
use App\Models\AgendaSuratMasuk;
use App\Models\AgendaTelpon;
use App\Models\KasKecil;
use App\Models\KasKecilDetail;
use App\Models\KasKecilFormulir;
use App\Models\SuratKeluarEmailLog;
use App\Models\SuratUndanganRapat;
use App\Services\AgendaJanjiTemuService;
use App\Services\AgendaSuratPerjalananService;
use App\Services\AgendaTelponService;
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
    private function cleanRupiah($value)
    {
        return (int) preg_replace("/\D/", "", $value);
    }

    public function index()
    {
        return view("administrasi.surat.index");
    }

    public function indexSuratKeluar()
    {
        $suratKeluar = AgendaSuratKeluar::where("user_id", auth()->id())->get();
        return view(
            "administrasi.surat.surat-keluar.index",
            compact("suratKeluar"),
        );
    }

    public function indexKasKecil()
    {
        $kasKecil = KasKecil::where("user_id", auth()->id())->get();
        return view("administrasi.surat.kas-kecil.index", compact("kasKecil"));
    }

    public function indexAgendaTelpon()
    {
        $agendaBelum = AgendaTelpon::where("user_id", auth()->id())
            ->where("is_done", false)
            ->orderBy("tgl_panggilan", "desc")
            ->get();

        $agendaSelesai = AgendaTelpon::where("user_id", auth()->id())
            ->where("is_done", true)
            ->orderBy("tgl_panggilan", "desc")
            ->get();

        return view(
            "administrasi.surat.agenda-telpon.index",
            compact("agendaBelum", "agendaSelesai"),
        );
    }

    public function showAgendaTelpon($id)
    {
        $agenda = AgendaTelpon::where("user_id", auth()->id())
            ->where("id", $id)
            ->first();
        return view("administrasi.surat.agenda-telpon.show", compact("agenda"));
    }

    public function indexAgendaPerjalanan()
    {
        $agenda = AgendaPerjalanan::where("user_id", auth()->id())->get();
        return view(
            "administrasi.surat.agenda-perjalanan.index",
            compact("agenda"),
        );
    }

    public function indexJanjiTemu()
    {
        $agendaJanjiTemu = AgendaJanjiTemu::where(
            "user_id",
            auth()->id(),
        )->get();
        return view(
            "administrasi.surat.janji-temu.index",
            compact("agendaJanjiTemu"),
        );
    }

    public function indexSuratUndanganRapat()
    {
        $agendaSuratUndanganRapat = SuratUndanganRapat::where(
            "user_id",
            auth()->id(),
        )->get();
        return view(
            "administrasi.surat.surat-undangan-rapat.index",
            compact("agendaSuratUndanganRapat"),
        );
    }

    public function create()
    {
        $agendaSuratMasuk = AgendaSuratMasuk::where(
            "user_id",
            auth()->id(),
        )->get();
        return view(
            "administrasi.surat.surat-masuk.create",
            compact("agendaSuratMasuk"),
        );
    }

    public function createSuratKeluar()
    {
        return view("administrasi.surat.surat-keluar.create");
    }

    public function createKasKecil()
    {
        return view("administrasi.surat.kas-kecil.create");
    }

    public function createAgendaTelpon()
    {
        return view("administrasi.surat.agenda-telpon.create");
    }

    public function createAgendaPerjalanan()
    {
        return view("administrasi.surat.agenda-perjalanan.create");
    }

    public function createJanjiTemu()
    {
        return view("administrasi.surat.janji-temu.create");
    }

    public function createSuratUndanganRapat()
    {
        return view("administrasi.surat.surat-undangan-rapat.create");
    }

    public function showDisposisi($id)
    {
        $surat = AgendaSuratMasuk::where("user_id", auth()->id())
            ->where("id", $id)
            ->first();
        return view(
            "administrasi.surat.surat-masuk.create-disposisi",
            compact("surat"),
        );
    }

    public function showJanjiTemu($id)
    {
        $agendaJanjiTemuService = app(AgendaJanjiTemuService::class);
        $agendaJanjiTemu = $agendaJanjiTemuService->show($id);
        return view(
            "administrasi.surat.janji-temu.show",
            compact("agendaJanjiTemu"),
        );
    }

    public function showAgendaPerjalanan($id)
    {
        $agendaPerjalanan = AgendaPerjalanan::with(
            "agendaPerjalananDetail",
            "agendaPerjalananAkomodasi",
            "agendaPerjalananKontak",
            "agendaPerjalananTransportasi",
        )
            ->where("user_id", auth()->id())
            ->where("id", $id)
            ->first();
        return view(
            "administrasi.surat.agenda-perjalanan.show",
            compact("agendaPerjalanan"),
        );
    }

    public function pdfPermintaanKasKecil($id)
    {
        $data = KasKecil::with(
            "kasKecilDetail",
            "kasKecilFormulir",
        )->findOrFail($id);
        $userProfile = Auth::user();

        $pdf = Pdf::loadView("administrasi.surat.kas-kecil.template-pdf", [
            "data" => $data,
            "userProfile" => $userProfile,
        ])->setPaper("a4", "portrait");

        return $pdf->download("permintaan-kas-kecil-" . $data->id . ".pdf");
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

    public function store(SuratMasukRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $data["user_id"] = auth()->id();

            if ($request->hasFile("file_surat")) {
                $data["file_surat"] = $request
                    ->file("file_surat")
                    ->store("surat-masuk", "public");
            }

            AgendaSuratMasuk::create($data);

            DB::commit();

            return redirect()
                ->route("administrasi.surat-masuk.create")
                ->with("success", "Surat masuk berhasil ditambahkan.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors([
                    "error" =>
                        "Gagal menyimpan surat masuk: " . $e->getMessage(),
                ])
                ->withInput();
        }
    }

    public function storeDisposisi(Request $request, $id)
    {
        $request->validate([
            "disposisi" => "required|array",
            "tujuan" => "required|array",
            "catatan" => "nullable|string",
            "tanggal_disposisi" => "required|date",
            "ttd_pimpinan" => "nullable|mimes:jpg,jpeg,png,pdf|max:2048",
        ]);

        DB::beginTransaction();

        try {
            $surat = AgendaSuratMasuk::where(
                "user_id",
                auth()->id(),
            )->findOrFail($id);

            // ============================
            // Upload TTD pimpinan (optional)
            // ============================
            $ttdPath = $surat->ttd_pimpinan;

            if ($request->hasFile("ttd_pimpinan")) {
                $file = $request->file("ttd_pimpinan");

                // delete old file
                if ($ttdPath && Storage::disk("public")->exists($ttdPath)) {
                    Storage::disk("public")->delete($ttdPath);
                }

                $ttdPath = Storage::disk("public")->put(
                    "surat-masuk/ttd",
                    $file,
                );
            }
            // ============================
            // Map Checkbox → Boolean Columns
            // ============================

            // Disposisi checkbox mapping
            $disposisiMap = [
                "Segera" => "disp_segera",
                "Teliti dan beri pendapat" => "disp_teliti",
                "Edarkan" => "disp_edarkan",
                "Untuk diketahui" => "disp_diketahui",
                "Koordinasikan" => "disp_koordinasikan",
                "Proses lebih lanjut" => "disp_proses_lanjut",
                "Arsipkan" => "disp_arsipkan",
                "Mohon dijawab" => "disp_mohon_dijawab",
            ];

            $disposisi = [];
            foreach ($disposisiMap as $label => $column) {
                $disposisi[$column] = in_array(
                    $label,
                    $request->disposisi ?? [],
                );
            }

            // Tujuan mapping
            $tujuanMap = [
                "Keuangan" => "tujuan_keuangan",
                "Kepala Bagian Gudang" => "tujuan_gudang",
                "Karyawan" => "tujuan_karyawan",
                "Lainnya" => "tujuan_lainnya",
            ];

            $tujuan = [];
            foreach ($tujuanMap as $label => $column) {
                $tujuan[$column] = in_array($label, $request->tujuan ?? []);
            }

            // ============================
            // Update database
            // ============================
            $surat->update(
                array_merge(
                    [
                        "catatan" => $request->catatan,
                        "tanggal_disposisi" => $request->tanggal_disposisi,
                        "ttd_pimpinan" => $ttdPath,
                        "status_disposisi" => "selesai",
                    ],
                    $disposisi,
                    $tujuan,
                ),
            );
            DB::commit();
            return redirect()
                ->route("administrasi.surat-masuk.create")
                ->with("success", "Disposisi berhasil disimpan.");
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Disposisi store error: " . $e->getMessage());

            return back()->with(
                "error",
                "Terjadi kesalahan saat menyimpan disposisi.",
            );
        }
    }

    public function storeSuratKeluar(SuratKeluarRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data["user_id"] = Auth::id();

            // default null
            $fileLampiran = null;
            $ttdFile = null;

            if ($request->hasFile("file_lampiran")) {
                $fileLampiran = $request
                    ->file("file_lampiran")
                    ->store("surat-keluar/lampiran", "public");
            }

            if ($request->hasFile("ttd")) {
                $ttdFile = $request
                    ->file("ttd")
                    ->store("surat-keluar/ttd", "public");
            }

            $surat = AgendaSuratKeluar::create([
                "user_id" => Auth::id(),
                "nomor_surat" => $request->nomor_surat,
                "lampiran" => $request->lampiran_text,
                "perihal" => $request->perihal,
                "tanggal_surat" => $request->tanggal_surat,

                // penerima
                "nama_penerima" => $request->nama_penerima,
                "jabatan_penerima" => $request->jabatan_penerima,
                "alamat_penerima" => $request->alamat_penerima,
                "email_penerima" => $request->email_penerima,

                // isi surat
                "paragraf_pembuka" => $request->paragraf_pembuka,
                "paragraf_isi" => $request->paragraf_isi,
                "paragraf_penutup" => $request->paragraf_penutup,

                // pengirim
                "nama_pengirim" => $request->nama_pengirim,
                "jabatan_pengirim" => $request->jabatan_pengirim,

                // tembusan
                "tembusan" => $request->tembusan,

                // file
                "ttd" => $ttdFile,
                "file_lampiran" => $fileLampiran,
            ]);
            $user = Auth::user();
            Mail::to($data["email_penerima"])->send(
                new SuratKeluarMail($surat, $user),
            );

            SuratKeluarEmailLog::create([
                "surat_keluar_id" => $surat->id,
                "email" => $data["email_penerima"],
                "status" => "success",
            ]);

            DB::commit();
            return redirect()
                ->route("administrasi.surat-keluar.index")
                ->with("success", "Surat keluar berhasil dikirim ke penerima.");
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Surat keluar error: " . $e->getMessage());

            return back()->with(
                "error",
                "Terjadi kesalahan saat menyimpan surat keluar.",
            );
        }
    }

    public function storeKasKecil(PermintaanKasKecilRequest $request, PermintaanKasKecilService $service)
    {
        try {
            $service->store($request);
            return back()->with('success', 'Permintaan kas kecil berhasil disimpan.');
        } catch (\Exception $e) {
            Log::error('Gagal simpan kas kecil: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request' => $request->except(['_token', 'ttd_*']),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Terjadi kesalahan saat menyimpan data.');
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
                ->with("success", "Agenda telepon berhasil disimpan.");
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(
                "Gagal menyimpan pada Agenda telepon. Error: " .
                $e->getMessage(),
            );
            return back()->with(
                "error",
                "Terjadi kesalahan saat menyimpan agenda telepon.",
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
                ->with("success", "Agenda Perjalanan berhasil disimpan.");
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(
                "Gagal menyimpan pada Agenda Perjalanan. Error: " .
                $e->getMessage(),
            );
            return back()->with(
                "error",
                "Terjadi kesalahan saat menyimpan Agenda Perjalanan.",
            );
        }
    }

    public function storeJanjiTemu(Request $request)
    {
        $agendaJanjiTemuService = app(AgendaJanjiTemuService::class);
        DB::beginTransaction();
        $data = $request->all();
        $data["user_id"] = auth()->id();
        try {
            $agendaJanjiTemuService->store($data);
            DB::commit();
            return redirect()
                ->route("administrasi.janji-temu.index")
                ->with("success", "Agenda Janji Temu berhasil disimpan.");
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(
                "Gagal menyimpan pada Agenda Janji Temu. Error: " .
                $e->getMessage(),
            );
            return back()->with(
                "error",
                "Terjadi kesalahan saat menyimpan Agenda Janji Temu.",
            );
        }
    }

    public function storeSuratUndanganRapat(Request $request)
    {
        $agendaService = app(SuratUndanganRapatService::class);

        $surat = $agendaService->store($request->all());

        if (!$surat) {
            return back()
                ->with("error", "Terjadi kesalahan saat menyimpan data")
                ->withInput();
        }

        return redirect()
            ->route("administrasi.surat-undangan-rapat.index")
            ->with("success", "Surat undangan rapat berhasil disimpan.");
    }

    public function destroyAgendaMasuk(string $id)
    {
        DB::beginTransaction();
        try {
            $agendaSuratMasuk = AgendaSuratMasuk::where(
                "user_id",
                auth()->id(),
            )->findOrFail($id);

            if ($agendaSuratMasuk->file_surat) {
                $storagePath = storage_path(
                    "app/public/" . $agendaSuratMasuk->file_surat,
                );
                $publicPath = public_path($agendaSuratMasuk->file_surat);

                // delete using Storage (recommended)
                if (
                    Storage::disk("public")->exists(
                        $agendaSuratMasuk->file_surat,
                    )
                ) {
                    Storage::disk("public")->delete(
                        $agendaSuratMasuk->file_surat,
                    );
                } elseif (file_exists($publicPath)) {
                    unlink($publicPath);
                }
            }

            $agendaSuratMasuk->delete();

            DB::commit();

            return redirect()
                ->route("administrasi.surat.index")
                ->with("success", "Agenda surat masuk berhasil dihapus");
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(
                "Error deleting agenda surat masuk ID $id: " . $e->getMessage(),
            );

            return redirect()
                ->route("administrasi.surat.index")
                ->with("error", "Agenda surat masuk gagal dihapus");
        }
    }

    public function updateAgendaTelpon($id, Request $request)
    {
        $agenda = AgendaTelpon::where("user_id", auth()->id())
            ->where("id", $id)
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
            ->route("administrasi.agenda-telpon.index")
            ->with("success", "Agenda Telpon berhasil diperbarui!");
    }

    public function updateIsDone($id)
    {
        $agenda = AgendaTelpon::where("user_id", auth()->id())
            ->where("id", $id)
            ->first();
        // Ubah status is_done ke true
        $agenda->is_done = !$agenda->is_done;
        $agenda->save();

        return back()->with("success", "Agenda berhasil ditandai!");
    }

    public function destroyAgendaSuratKeluar($id)
    {
        DB::beginTransaction();
        try {
            $surat = AgendaSuratKeluar::findOrFail($id);

            // Hapus file lampiran jika ada
            if (
                $surat->file_lampiran &&
                Storage::disk("public")->exists($surat->file_lampiran)
            ) {
                Storage::disk("public")->delete($surat->file_lampiran);
            }

            // Hapus file ttd jika ada
            if ($surat->ttd && Storage::disk("public")->exists($surat->ttd)) {
                Storage::disk("public")->delete($surat->ttd);
            }

            // Hapus record surat keluar
            $surat->delete();

            DB::commit();

            return redirect()
                ->route("administrasi.surat-keluar.index")
                ->with("success", "Surat keluar berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal menghapus Surat Keluar: " . $e->getMessage());

            return back()->with(
                "error",
                "Terjadi kesalahan saat menghapus surat.",
            );
        }
    }

    public function destroyKasKecil($kasId)
    {
        DB::beginTransaction();
        try {
            // Ambil kas kecil
            $kas = KasKecil::where("id", $kasId)
                ->where("user_id", auth()->id())
                ->firstOrFail();

            // Hapus detail kas kecil
            KasKecilDetail::where("kas_kecil_id", $kasId)->delete();

            // Ambil formulirnya
            $formulir = KasKecilFormulir::where(
                "kas_kecil_id",
                $kasId,
            )->first();

            // Hapus file ttd jika ada
            if ($formulir) {
                if ($formulir->ttd_nama_pemohon) {
                    Storage::disk("public")->delete(
                        $formulir->ttd_nama_pemohon,
                    );
                }
                if ($formulir->ttd_atasan_langsung) {
                    Storage::disk("public")->delete(
                        $formulir->ttd_atasan_langsung,
                    );
                }
                if ($formulir->ttd_bagian_keuangan) {
                    Storage::disk("public")->delete(
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
                ->with("success", "Data kas kecil berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error("Delete kas kecil error: " . $e->getMessage());

            return redirect()
                ->back()
                ->with("error", "Gagal menghapus data kas kecil.");
        }
    }

    public function destroyAgendaTelpon($id)
    {
        DB::beginTransaction();
        try {
            $agendaTelponService = app(AgendaTelponService::class);
            $agenda = AgendaTelpon::where("user_id", auth()->id())->findOrFail(
                $id,
            );
            $agendaTelponService->delete($agenda);
            DB::commit();
            return redirect()
                ->back()
                ->with("success", "Agenda telepon berhasil dihapus.");
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(
                "Gagal menghapus pada Agenda telepon. Error: " .
                $e->getMessage(),
            );
            return back()->with(
                "error",
                "Terjadi kesalahan saat menghapus agenda telepon.",
            );
        }
    }

    public function destroyAgendaPerjalanan($id)
    {
        DB::beginTransaction();
        try {
            $agendaPerjalananService = app(AgendaSuratPerjalananService::class);
            $agenda = AgendaPerjalanan::where(
                "user_id",
                auth()->id(),
            )->findOrFail($id);
            $agendaPerjalananService->delete($agenda);
            DB::commit();
            return redirect()
                ->back()
                ->with("success", "Agenda perjalanan berhasil dihapus.");
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(
                "Gagal menghapus pada Agenda perjalanan. Error: " .
                $e->getMessage(),
            );
            return back()->with(
                "error",
                "Terjadi kesalahan saat menghapus agenda perjalanan.",
            );
        }
    }

    public function destroyJanjiTemu($id)
    {
        DB::beginTransaction();
        try {
            $agendaJanjiTemuService = app(AgendaJanjiTemuService::class);
            $agenda = AgendaJanjiTemu::where(
                "user_id",
                auth()->id(),
            )->findOrFail($id);
            $agendaJanjiTemuService->delete($agenda->id);
            DB::commit();
            return redirect()
                ->back()
                ->with("success", "Agenda janji temu berhasil dihapus.");
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(
                "Gagal menghapus pada Agenda janji temu. Error: " .
                $e->getMessage(),
            );
            return back()->with(
                "error",
                "Terjadi kesalahan saat menghapus agenda janji temu.",
            );
        }
    }

    public function destroySuratUndanganRapat($id)
    {
        DB::beginTransaction();
        try {
            $suratUndanganRapatService = app(suratUndanganRapatService::class);
            $suratUndanganRapat = SuratUndanganRapat::where(
                "user_id",
                auth()->id(),
            )->findOrFail($id);
            $suratUndanganRapatService->delete($suratUndanganRapat->id);
            DB::commit();
            return redirect()
                ->back()
                ->with(
                    "success",
                    "Agenda surat undangan rapat berhasil dihapus.",
                );
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(
                "Gagal menghapus pada surat undangan rapat. Error: " .
                $e->getMessage(),
            );
            return back()->with(
                "error",
                "Terjadi kesalahan saat menghapus surat undangan rapat.",
            );
        }
    }
}
