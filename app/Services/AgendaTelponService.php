<?php

namespace App\Services;

use App\Models\AgendaTelpon;

class AgendaTelponService
{
    /**
     * Get all agenda telpon dipisah berdasarkan status.
     */
    public function getAllSeparated()
    {
        return [
            "belum" => AgendaTelpon::where("is_done", "1")
                ->orderBy("jadwal_tanggal")
                ->get(),
            "selesai" => AgendaTelpon::where("is_done", "!=", "1")
                ->orderBy("jadwal_tanggal")
                ->get(),
        ];
    }

    /**
     * Store new data.
     */
    public function store(array $data)
    {
        return AgendaTelpon::create([
            "user_id" => auth()->user()->id,
            "tgl_panggilan" => $data["tgl_panggilan"],
            "waktu_panggilan" => $data["waktu_panggilan"],
            "nama_penelpon" => $data["nama_penelpon"],
            "perusahaan" => $data["perusahaan"],
            "nomor_telpon" => $data["nomor_telpon"],
            "jadwal_tanggal" => $data["jadwal_tanggal"],
            "jadwal_waktu" => $data["jadwal_waktu"],
            "jadwal_dengan" => $data["jadwal_dengan"],
            "keperluan" => $data["keperluan"],
            "tingkat_status" => $data["tingkat_status"],
            "catatan_khusus" => $data["catatan_khusus"],
            "status" => $data["status"],
            "dicatat_oleh" => $data["dicatat_oleh"],
            "dicatat_tgl" => $data["dicatat_tgl"],
            "is_done" => false,
        ]);
    }

    /**
     * Update existing.
     */
    public function update(AgendaTelpon $agenda, array $data)
    {
        $agenda->update($data);
        return $agenda;
    }

    /**
     * Delete.
     */
    public function delete(AgendaTelpon $agenda)
    {
        return $agenda->delete();
    }
}
