<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgendaTelponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "tgl_panggilan" => ["required", "date"],
            "waktu_panggilan" => ["required"],
            "nama_penelpon" => ["required", "string", "max:255"],
            "perusahaan" => ["nullable", "string", "max:255"],
            "nomor_telpon" => ["nullable", "string", "max:50"],
            "jadwal_tanggal" => ["nullable", "date"],
            "jadwal_waktu" => ["nullable"],
            "jadwal_dengan" => ["nullable", "string", "max:255"],
            "keperluan" => ["required", "string"],
            "tingkat_status" => ["required", "in:urgent,penting,normal,dijadwalkan"],
            "catatan_khusus" => ["nullable", "string"],
            "status" => ["required", "in:terkonfirmasi,reschedule,dibatalkan,belum"],
            "dicatat_oleh" => ["required", "string", "max:255"],
            "dicatat_tgl" => ["required", "date"],
        ];
    }

    public function messages(): array
    {
        return [
            "tgl_panggilan.required" => "Tanggal panggilan wajib diisi.",
            "waktu_panggilan.required" => "Waktu panggilan wajib diisi.",
            "nama_penelpon.required" => "Nama penelpon wajib diisi.",
            "keperluan.required" => "Keperluan wajib diisi.",
            "tingkat_status.required" => "Tingkat status wajib dipilih.",
            "status.required" => "Status wajib dipilih.",
            "dicatat_oleh.required" => "Nama pencatat wajib diisi.",
            "dicatat_tgl.required" => "Tanggal pencatatan wajib diisi.",
        ];
    }
}