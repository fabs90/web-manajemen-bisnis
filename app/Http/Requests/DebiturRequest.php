<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DebiturRequest extends FormRequest
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
            "nama" => "required|string|max:200",
            "kontak" => "nullable|string|max:255",
            "alamat" => "nullable|string|max:255",
            "email" => "nullable|email|max:255|unique:pelanggan,email",
            "jenis" => "required",
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            "nama.required" => "Nama debitur wajib diisi.",
            "nama.string" => "Nama debitur harus berupa teks.",
            "nama.max" => "Nama debitur tidak boleh lebih dari 200 karakter.",

            "kontak.string" => "Kontak harus berupa teks atau angka.",
            "kontak.max" => "Kontak tidak boleh lebih dari 255 karakter.",

            "alamat.string" => "Alamat harus berupa teks.",
            "alamat.max" => "Alamat tidak boleh lebih dari 255 karakter.",

            "email.email" => "Format email tidak valid.",
            "email.max" => "Email tidak boleh lebih dari 255 karakter.",
            "email.unique" =>
                "Email ini sudah terdaftar sebagai debitur/kreditur lain.",

            "jenis.required" => "Jenis debitur/kreditur wajib diisi.",
        ];
    }
}
