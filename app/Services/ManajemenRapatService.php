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
            // 1. Upload TTD Pimpinan jika ada
            $ttdPath = null;
            if (request()->hasFile('ttd_pemimpin')) {
                $ttdPath = $this->fileUploadService->upload(request()->file('ttd_pemimpin'), 'rapat/ttd', auth()->user()->email);
            }

            // 2. Simpan Master Rapat
            $rapat = AgendaRapat::create([
                "user_id" => auth()->user()->id,
                "nomor_surat" => $data['nomor_surat_rapat'],
                "judul_rapat" => $data["judul_rapat"],
                "tempat" => $data["tempat"],
                "tanggal" => $data["tanggal"],
                "ttd_pemimpin" => $ttdPath, 
                "waktu" => $data["waktu"],
                "pemimpin_rapat" => $data["pemimpin_rapat"],
                "keputusan_rapat" => $data["keputusan_rapat"],
                "nama_kota" => $data["nama_kota"],
                "nama_notulis" => $data["nama_notulis"],
                "agenda_rapat" => $data['agenda_rapat'],
                "tanggal_rapat_berikutnya" => $data["tanggal_rapat_berikutnya"] ?? null,
                "agenda_rapat_berikutnya" => $data["agenda_rapat_berikutnya"] ?? null,
                "waktu_rapat_berikutnya" => $data["waktu_rapat_berikutnya"] ?? null,
            ]);

            // 3. Detail Rapat
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

            // 4. Peserta Rapat
            if (isset($data["peserta_nama"])) {
                foreach ($data["peserta_nama"] as $i => $namaPeserta) {
                    $ttdPesertaPath = null;
                    if (isset($data["peserta_ttd"][$i]) && $data["peserta_ttd"][$i] !== null) {
                        $ttdPesertaPath = $this->fileUploadService->upload($data['peserta_ttd'][$i], 'rapat/ttd', auth()->user()->email);
                    }

                    PesertaRapat::create([
                        "agenda_rapat_id" => $rapat->id,
                        "nama" => $namaPeserta,
                        "jabatan" => $data["peserta_jabatan"][$i] ?? null,
                        "tanda_tangan" => $ttdPesertaPath,
                    ]);
                }
            }

            // 5. Tindak Lanjut
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
            // 1. Tangani Upload TTD Pemimpin untuk Surat Hasil Keputusan
            $ttdPath = null;
            if (request()->hasFile('ttd_pemimpin')) {
                // Menggunakan FileUploadService yang sama
                $ttdPath = $this->fileUploadService->upload(
                    request()->file('ttd_pemimpin'), 
                    'rapat/ttd_hasil', 
                    auth()->user()->email
                );
            }

            // 2. Simpan ke tabel HasilKeputusanRapat
            $hasil = HasilKeputusanRapat::create([
                "agenda_rapat_id" => $data["agenda_rapat_id"],
                "nomor_surat" => $data["nomor_surat"],
                "keputusan_rapat" => $data["keputusan"],
                "kota_tujuan" => $data["kota_tujuan"],
                "tanggal_tujuan" => $data["tanggal_tujuan"],
                "ttd_pemimpin" => $ttdPath, // Path file tersimpan di sini
                "jabatan_penanggung_jawab" => $data["jabatan_penanggung_jawab"],
                "nama_penanggung_jawab" => $data["nama_penanggung_jawab"],
                "user_id" => auth()->id(),
            ]);

            DB::commit();

            return $hasil;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Gagal menyimpan hasil keputusan: " . $e->getMessage());
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $rapat = AgendaRapat::findOrFail($id);
            RapatDetail::where("agenda_rapat_id", $rapat->id)->delete();
            
            $peserta = PesertaRapat::where("agenda_rapat_id", $rapat->id)->get();
            foreach ($peserta as $p) {
                if ($p->tanda_tangan && Storage::disk("public")->exists($p->tanda_tangan)) {
                    Storage::disk("public")->delete($p->tanda_tangan);
                }
                $p->delete();
            }

            TindakLanjutRapat::where("agenda_rapat_id", $rapat->id)->delete();
            $rapat->delete();

            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update($id, $data)
    {
        DB::beginTransaction();
        try {
            $rapat = AgendaRapat::findOrFail($id);

            // Update Master
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

            // Re-sync detail, peserta, dan tindak lanjut (Hapus lama, buat baru)
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
