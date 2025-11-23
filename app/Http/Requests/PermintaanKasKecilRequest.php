<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermintaanKasKecilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "nomor" => "required|string|max:50",
            "tanggal" => "required|date",
            "nama_pemohon" => "required|string|max:100",
            "departemen" => "required|string|max:100",

            "jenis" => "required",

            "keterangan" => "required|array|min:1",
            "keterangan.*" => "required|string|max:255",

            "kategori" => "required|array|min:1",
            "kategori.*" => "required|string",

            "jumlah" => "required|array|min:1",
            "jumlah.*" => "required|numeric|min:0",

            "total" => "required|numeric|min:0",

            "nama_atasan_langsung" => "required|string|max:100",
            "nama_bagian_keuangan" => "required|string|max:100",

            // === Optional signature files ===
            "ttd_nama_pemohon" => "nullable|file|mimes:jpg,jpeg,png|max:2048",
            "ttd_nama_atasan_langsung" =>
                "nullable|file|mimes:jpg,jpeg,png|max:2048",
            "ttd_nama_bagian_keuangan" =>
                "nullable|file|mimes:jpg,jpeg,png|max:2048",
        ];
    }

    public function messages(): array
    {
        return [
            "keterangan.required" =>
                "Minimal satu keterangan kebutuhan harus diisi.",
            "keterangan.*.required" => "Keterangan tidak boleh kosong.",

            "kategori.required" => "Kategori harus diisi.",
            "kategori.*.required" => "Setiap kategori harus dipilih.",

            "jumlah.required" => "Minimal satu nilai jumlah harus diisi.",
            "jumlah.*.required" => "Jumlah tidak boleh kosong.",
            "jumlah.*.numeric" => "Jumlah harus angka.",

            "total.numeric" => "Total harus angka.",
            "total.min" => "Total tidak boleh negatif.",

            "nama_atasan_langsung.required" =>
                "Nama atasan langsung harus diisi.",
            "nama_bagian_keuangan.required" =>
                "Nama bagian keuangan harus diisi.",

            // Pesan error file (opsional)
            "ttd_nama_pemohon.max" =>
                "File tanda tangan pemohon tidak boleh lebih dari 2MB.",
            "ttd_nama_atasan_langsung.max" =>
                "File tanda tangan atasan langsung tidak boleh lebih dari 2MB.",
            "ttd_nama_bagian_keuangan.max" =>
                "File tanda tangan bagian keuangan tidak boleh lebih dari 2MB.",
        ];
    }
}
