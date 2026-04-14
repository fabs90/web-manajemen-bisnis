<?php

namespace App\Services;

use Illuminate\Support\Facades\{Auth, DB, Log, Storage};
use App\Models\Rapat\{AgendaRapat, HasilKeputusanRapat, PesertaRapat, RapatDetail, TindakLanjutRapat};
use Exception;
use Throwable;
use Barryvdh\DomPDF\Facade\Pdf;

class ManajemenRapatService
{
    public function __construct(protected FileUploadService $fileUploadService)
    {

    }
    public function store($data)
    {
        DB::beginTransaction();

        try {
            // MASTER TABLE
            $rapat = AgendaRapat::create([
                "user_id" => auth()->user()->id,
                "nomor_surat" => $data['nomor_surat_rapat'],
                "judul_rapat" => $data["judul_rapat"],
                "tempat" => $data["tempat"],
                "tanggal" => $data["tanggal"],
                "waktu" => $data["waktu"],
                "pemimpin_rapat" => $data["pemimpin_rapat"],
                "keputusan_rapat" => $data["keputusan_rapat"],
                "nama_kota" => $data["nama_kota"],
                "nama_notulis" => $data["nama_notulis"],
                "agenda_rapat" => $data['agenda_rapat'],
                "tanggal_rapat_berikutnya" => $data["tanggal_rapat_berikutnya"],
                "agenda_rapat_berikutnya" => $data["agenda_rapat_berikutnya"],
                "waktu_rapat_berikutnya" => $data["waktu_rapat_berikutnya"],
            ]);

            // detail rapat
            if (isset($data["pembahasan_pembicara"])) {
                foreach ($data["pembahasan_pembicara"] as $i => $pembicara) {
                    RapatDetail::create([
                        "agenda_rapat_id" => $rapat->id,
                        "judul_agenda" => $data["pembahasan_agenda"][$i],
                        "pembicara" => $pembicara,
                        "pembahasan" => $data["pembahasan_isi"][$i],
                    ]);
                }
            }

            // peserta rapat
            if (isset($data["peserta_nama"])) {
                foreach ($data["peserta_nama"] as $i => $namaPeserta) {
                    // Upload tanda tangan jika ada
                    $ttdPath = null;
                    if (
                        isset($data["peserta_ttd"][$i]) &&
                        $data["peserta_ttd"][$i] !== null
                    ) {
                        $ttdPath = $this->fileUploadService->upload($data['peserta_ttd'][$i], 'rapat/ttd', auth()->user()->email);
                    }

                    PesertaRapat::create([
                        "agenda_rapat_id" => $rapat->id,
                        "nama" => $namaPeserta,
                        "jabatan" => $data["peserta_jabatan"][$i],
                        "tanda_tangan" => $ttdPath,
                    ]);
                }
            }

            if (isset($data["tindak_tindakan"])) {
                foreach ($data["tindak_tindakan"] as $i => $tindakan) {
                    TindakLanjutRapat::create([
                        "agenda_rapat_id" => $rapat->id,
                        "tindakan" => $tindakan,
                        "pelaksana" => $data["tindak_pelaksana"][$i],
                        "target_selesai" => $data["tindak_target"][$i],
                        "status" => $data["tindak_status"][$i],
                    ]);
                }
            }
            DB::commit();
            return $rapat;
        } catch (Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function storeHasilKeputusan($data)
    {
        DB::beginTransaction();
        try {
            $hasil = HasilKeputusanRapat::create([
                "agenda_rapat_id" => $data["agenda_rapat_id"],
                "nomor_surat" => $data["nomor_surat"],
                "keputusan_rapat" => $data["keputusan"],
                "kota_tujuan" => $data["kota_tujuan"],
                "tanggal_tujuan" => $data["tanggal_tujuan"],
                "jabatan_penanggung_jawab" => $data["jabatan_penanggung_jawab"],
                "nama_penanggung_jawab" => $data["nama_penanggung_jawab"],
                "user_id" => auth()->id(),
            ]);

            DB::commit();

            return $hasil;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $rapat = AgendaRapat::findOrFail($id);

            // Hapus detail rapat
            RapatDetail::where("agenda_rapat_id", $rapat->id)->delete();

            // Hapus peserta + file tanda tangan
            $peserta = PesertaRapat::where(
                "agenda_rapat_id",
                $rapat->id,
            )->get();
            foreach ($peserta as $p) {
                if (
                    $p->tanda_tangan &&
                    Storage::disk("public")->exists($p->tanda_tangan)
                ) {
                    Storage::disk("public")->delete($p->tanda_tangan);
                }
                $p->delete();
            }

            // Hapus tindak lanjut
            TindakLanjutRapat::where("agenda_rapat_id", $rapat->id)->delete();

            // Hapus master rapat
            $rapat->delete();

            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroyHasilKeputusan($id)
    {
        DB::beginTransaction();
        try {
            $hasil = HasilKeputusanRapat::find($id);
            if (!$hasil) {
                return redirect()
                    ->back()
                    ->with("error", "Data Hasil Keputusan tidak ditemukan!");
            }

            $hasil->delete();

            DB::commit();
            return redirect()
                ->back()
                ->with("success", "Data Hasil Keputusan berhasil dihapus!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with("error", "Terjadi kesalahan saat menghapus data!");
        }
    }

    public function update($id, $data)
    {
        DB::beginTransaction();

        try {
            // Ambil data rapat
            $rapat = AgendaRapat::findOrFail($id);

            // Update master
            $rapat->update([
                "judul_rapat" => $data["judul_rapat"],
                "tempat" => $data["tempat"],
                "tanggal" => $data["tanggal"],
                "waktu" => $data["waktu"],
                "pemimpin_rapat" => $data["pemimpin_rapat"],
                "keputusan_rapat" => $data["keputusan_rapat"],
                "nama_kota" => $data["nama_kota"],
                "nama_notulis" => $data["nama_notulis"],
                "tanggal_rapat_berikutnya" => $data["tanggal_rapat_berikutnya"],
                "agenda_rapat_berikutnya" => $data["agenda_rapat_berikutnya"],
            ]);

            // ===========================
            // DELETE DETAIL PEMBAHASAN LAMA
            // ===========================
            RapatDetail::where("agenda_rapat_id", $rapat->id)->delete();

            if (isset($data["pembahasan_pembicara"])) {
                foreach ($data["pembahasan_pembicara"] as $i => $pembicara) {
                    RapatDetail::create([
                        "agenda_rapat_id" => $rapat->id,
                        "judul_agenda" => $data["pembahasan_agenda"][$i],
                        "pembicara" => $pembicara,
                        "pembahasan" => $data["pembahasan_isi"][$i],
                    ]);
                }
            }

            // ===========================
            // DELETE PESERTA LAMA + FILE
            // ===========================
            $pesertaLama = PesertaRapat::where(
                "agenda_rapat_id",
                $rapat->id,
            )->get();
            foreach ($pesertaLama as $p) {
                if (
                    $p->tanda_tangan &&
                    Storage::disk("public")->exists($p->tanda_tangan)
                ) {
                    Storage::disk("public")->delete($p->tanda_tangan);
                }
                $p->delete();
            }

            if (isset($data["peserta_nama"])) {
                foreach ($data["peserta_nama"] as $i => $namaPeserta) {
                    $ttdPath = null;

                    if (
                        isset($data["peserta_ttd"][$i]) &&
                        $data["peserta_ttd"][$i] !== null
                    ) {
                        $ttdPath = $data["peserta_ttd"][$i]->store(
                            "/rapat/ttd",
                            "public",
                        );
                    }
                    PesertaRapat::create([
                        "agenda_rapat_id" => $rapat->id,
                        "nama" => $namaPeserta,
                        "jabatan" => $data["peserta_jabatan"][$i],
                        "tanda_tangan" => $ttdPath,
                    ]);
                }
            }

            // ===========================
            // DELETE TINDAK LANJUT LAMA
            // ===========================
            TindakLanjutRapat::where("agenda_rapat_id", $rapat->id)->delete();
            if (isset($data["tindak_tindakan"])) {
                foreach ($data["tindak_tindakan"] as $i => $tindakan) {
                    TindakLanjutRapat::create([
                        "agenda_rapat_id" => $rapat->id,
                        "tindakan" => $tindakan,
                        "pelaksana" => $data["tindak_pelaksana"][$i],
                        "target_selesai" => $data["tindak_target"][$i],
                        "status" => $data["tindak_status"][$i],
                    ]);
                }
            }
            DB::commit();
            return $rapat;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function generatePdf($id)
    {
        try {
            $rapat = AgendaRapat::with([
                "rapatDetails",
                "pesertaRapat",
                "tindakLanjutRapat",
            ])->findOrFail($id);

            $profileUser = Auth::user();
            $pdf = Pdf::loadView(
                "administrasi.surat.notulen-rapat.template-pdf",
                [
                    "agendaJanjiTemu" => $rapat,
                    "profileUser" => $profileUser,
                ],
            )->setPaper("a4", "portrait");

            return $pdf->download(
                "Notulen Rapat - " . $rapat->tanggal . ".pdf",
            );
        } catch (Exception $e) {
            // Simpan error ke log Laravel
            Log::error("PDF Generation Error: " . $e->getMessage());

            // Redirect dengan pesan error ke halaman sebelumnya
            return redirect()
                ->back()
                ->with(
                    "error",
                    "Gagal generate PDF! Silahkan cek kembali data.",
                );
        }
    }

    public function generatePdfHasilKeputusan($rapatId)
    {
        $result = AgendaRapat::where("id", $rapatId)->where("user_id", Auth::id())->latest()->first();
        $user = Auth::user();
        $pdf = Pdf::setOptions([
            "isRemoteEnabled" => true,
        ])
            ->loadView("administrasi.surat.hasil-keputusan.template-pdf", [
                "result" => $result,
                "user" => $user,
            ])
            ->setPaper("A4");

        return $pdf->download("Surat Keputusan Rapat-" . $result->nomor_surat . ".pdf");
    }
}
