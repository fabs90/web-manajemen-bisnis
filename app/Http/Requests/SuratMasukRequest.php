<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuratMasukRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            "nomor_agenda" => "required",
            "tanggal_terima" => "required|date",
            "nomor_surat" => "required",
            "tanggal_surat" => "required|date",
            "pengirim" => "required",
            "perihal" => "required",
            "file_surat" => "required|mimes:pdf|max:20480", // 20MB
        ];
    }

    /**
     * Custom validation messages in Indonesian.
     */
    public function messages(): array
    {
        return [
            "nomor_agenda.required" => "Nomor agenda wajib diisi.",
            "tanggal_terima.required" => "Tanggal terima wajib diisi.",
            "tanggal_terima.date" =>
                "Tanggal terima harus berupa tanggal yang valid.",

            "nomor_surat.required" => "Nomor surat wajib diisi.",
            "tanggal_surat.required" => "Tanggal surat wajib diisi.",
            "tanggal_surat.date" =>
                "Tanggal surat harus berupa tanggal yang valid.",

            "pengirim.required" => "Pengirim surat wajib diisi.",
            "perihal.required" => "Perihal surat wajib diisi.",

            "file_surat.required" => "File surat wajib diunggah.",
            "file_surat.mimes" => "File surat harus berformat PDF.",
            "file_surat.max" => "Ukuran file surat maksimal 20MB.",
        ];
    }
}
