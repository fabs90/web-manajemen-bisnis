<?php

namespace App\Services;

use App\Models\AgendaPerjalanan;
use App\Models\AgendaPerjalananAkomodasi;
use App\Models\AgendaPerjalananDetail;
use App\Models\AgendaPerjalananKontak;
use App\Models\AgendaPerjalananTransportasi;
use App\Models\BukuBesarPengeluaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AgendaSuratPerjalananService
{
    /**
     * Store new data.
     */
    public function store(array $data)
    {
        // agenda surat perjalanan master
        $agendaSuratPerjalanan = AgendaPerjalanan::create([
            "user_id" => auth()->user()->id,
            "nama_pelaksana" => $data["nama_pelaksana"],
            "jabatan" => $data["jabatan"],
            "tujuan" => $data["tujuan"],
            "tanggal_mulai" => $data["tanggal_mulai"],
            "tanggal_selesai" => $data["tanggal_selesai"],
            "keperluan" => $data["keperluan"],
            "disiapkan_oleh" => $data["disiapkan_oleh"],
            "tanggal_disiapkan" => $data["tanggal_disiapkan"],
            "disetujui_oleh" => $data["disetujui_oleh"],
            "tanggal_disetujui" => $data["tanggal_disetujui"],
            "transport" => $data["transport"],
            "akomodasi" => $data["akomodasi"],
            "konsumsi" => $data["konsumsi"],
            "lain_lain" => $data["lain_lain"],
            "total_biaya" => $data["total_biaya"],
        ]);

        // masukin ke pengeluaran
        $bukuBesarPengeluaran = BukuBesarPengeluaran::create([
            "tanggal" => $data["tanggal_disetujui"],
            "uraian" =>
                "Pengeluaran untuk agenda surat perjalanan: " .
                $agendaSuratPerjalanan->id .
                " - " .
                $data["nama_pelaksana"],
            "potongan_pembelian" => 0,
            "jumlah_hutang" => 0,
            "jumlah_pembelian_tunai" => 0,
            "lain_lain" => $data["total_biaya"],
            "admin_bank" => 0,
            "jumlah_retur_pembelian" => 0,
            "jumlah_pengeluaran" => $data["total_biaya"],
            "user_id" => auth()->user()->id,
        ]);

        // insert into agenda_surat_perjalanan_detail
        foreach ($data["jadwal"] as $index => $jadwal) {
            $hariKe = $index + 1;
            foreach ($jadwal["items"] as $item) {
                AgendaPerjalananDetail::create([
                    "user_id" => auth()->user()->id,
                    "agenda_perjalanan_id" => $agendaSuratPerjalanan->id,
                    "hari" => $hariKe,
                    "tanggal" => $jadwal["tanggal"],
                    "waktu" => $item["waktu"],
                    "kegiatan" => $item["kegiatan"],
                    "lokasi" => $item["lokasi"],
                ]);
            }
        }

        // agenda transportasi
        $agendaPerjalananTransportasi = AgendaPerjalananTransportasi::create([
            "user_id" => auth()->user()->id,
            "agenda_perjalanan_id" => $agendaSuratPerjalanan->id,
            "penerbangan_pergi" => $data["transportasi_pergi"],
            "penerbangan_pulang" => $data["transportasi_pulang"],
            "kode_booking" => $data["kode_booking"],
            "transportasi_lokal" => $data["transportasi_lokal"],
        ]);

        // agenda akomodasi
        $agendaPerjalananAkomodasi = AgendaPerjalananAkomodasi::create([
            "user_id" => auth()->user()->id,
            "agenda_perjalanan_id" => $agendaSuratPerjalanan->id,
            "hotel" => $data["akomodasi_hotel"],
            "alamat" => $data["akomodasi_alamat"],
            "telepon" => $data["akomodasi_telpon"],
            "check_in" => $data["akomodasi_check_in"],
            "check_out" => $data["akomodasi_check_out"],
            "booking_number" => $data["akomodasi_booking_no"],
        ]);

        // agenda kontak
        foreach ($data["kontak"] as $item) {
            $index = 1;
            $agendaPerjalananKontak = AgendaPerjalananKontak::create([
                "user_id" => auth()->user()->id,
                "agenda_perjalanan_id" => $agendaSuratPerjalanan->id,
                "nama" => $item["nama"],
                "telepon" => $item["tel"],
                "jenis" => "Kontak-$index",
            ]);
            $index += 1;
        }

        return $agendaSuratPerjalanan;
    }

    public function delete($agenda)
    {
        $agendaSuratPerjalanan = AgendaPerjalanan::where(
            "user_id",
            auth()->id(),
        )->findOrFail($agenda->id);

        // hapus relasi
        $agendaSuratPerjalanan->agendaPerjalananDetail()->delete();
        $agendaSuratPerjalanan->agendaPerjalananAkomodasi()->delete();
        $agendaSuratPerjalanan->agendaPerjalananKontak()->delete();
        $agendaSuratPerjalanan->agendaPerjalananTransportasi()->delete();

        // hapus buku besar pengeluaran terkait
        BukuBesarPengeluaran::where("user_id", auth()->id())
            ->where(
                "uraian",
                "LIKE",
                "%Pengeluaran untuk agenda surat perjalanan: " .
                    $agendaSuratPerjalanan->id .
                    " - " .
                    $agendaSuratPerjalanan->nama_pelaksana .
                    "%",
            )
            ->delete();

        return $agendaSuratPerjalanan->delete();
    }

    public function generatePdf($id)
    {
        try {
            $agendaPerjalanan = AgendaPerjalanan::with([
                "agendaPerjalananDetail",
                "agendaPerjalananAkomodasi",
                "agendaPerjalananKontak",
                "agendaPerjalananTransportasi",
            ])
                ->where("user_id", auth()->id())
                ->findOrFail($id);
            $userProfile = Auth::user();
            $pdf = Pdf::loadView(
                "administrasi.surat.agenda-perjalanan.template-pdf",
                compact("agendaPerjalanan", "userProfile"),
            )->setPaper("A4", "portrait");

            $fileName =
                "Agenda-Surat-Perjalanan-" . $agendaPerjalanan->id . ".pdf";

            return $pdf->download($fileName); // Jika ingin download: ->download($fileName)
        } catch (\Exception $e) {
            Log::error(
                "Gagal membuat PDF Agenda Surat Perjalanan: " .
                    $e->getMessage(),
            );
            throw new \Exception("Gagal membuat PDF: " . $e->getMessage());
        }
    }
}
