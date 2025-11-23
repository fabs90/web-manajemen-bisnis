<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuratKeluarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "nomor_surat" => ["required", "string", "max:255"],
            "lampiran_text" => ["nullable", "string", "max:255"],
            "perihal" => ["required", "string", "max:255"],
            "tanggal_surat" => ["required", "date"],

            // Penerima
            "nama_penerima" => ["required", "string", "max:255"],
            "jabatan_penerima" => ["nullable", "string", "max:255"],
            "alamat_penerima" => ["nullable", "string"],
            "email_penerima" => ["required", "email", "max:255"],

            // Isi surat
            "paragraf_pembuka" => ["nullable", "string"],
            "paragraf_isi" => ["required", "string"],
            "paragraf_penutup" => ["nullable", "string"],

            // Pengirim
            "nama_pengirim" => ["required", "string", "max:255"],
            "jabatan_pengirim" => ["nullable", "string", "max:255"],

            // Tembusan
            "tembusan" => ["nullable", "string"],

            // File upload
            "ttd" => [
                "nullable",
                "image",
                "mimes:jpeg,png,jpg,gif",
                "max:2048",
            ],
            "file_lampiran" => [
                "nullable",
                "file",
                "mimes:pdf,doc,docx,jpeg,png,jpg",
                "max:5120",
            ],
        ];
    }

    public function messages(): array
    {
        return [
            "nomor_surat.required" => "Nomor surat wajib diisi.",
            "perihal.required" => "Perihal surat wajib diisi.",
            "tanggal_surat.required" => "Tanggal surat wajib diisi.",
            "tanggal_surat.date" => "Tanggal surat tidak valid.",

            "nama_penerima.required" => "Nama penerima wajib diisi.",
            "paragraf_isi.required" => "Bagian isi surat wajib diisi.",

            "nama_pengirim.required" => "Nama pengirim wajib diisi.",

            "ttd.image" => "File tanda tangan harus berupa gambar.",
            "ttd.mimes" => "Tanda tangan hanya boleh jpg, jpeg, png, gif.",
            "ttd.max" => "Ukuran tanda tangan maksimal 2MB.",

            "file_lampiran.mimes" =>
                "Lampiran hanya boleh pdf, doc, docx, jpg, jpeg, png.",
            "file_lampiran.max" => "Ukuran lampiran maksimal 5MB.",
        ];
    }
}
