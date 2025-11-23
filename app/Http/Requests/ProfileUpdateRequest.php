<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => ["required", "string", "max:255"],
            "email" => [
                "required",
                "string",
                "lowercase",
                "email",
                "max:255",
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            "password" => ["nullable", "string", "min:8", "confirmed"],
            "password_confirmation" => ["nullable", "string", "min:8"],
            "alamat" => ["nullable", "string", "max:255"],
            "nomor_telepon" => ["nullable", "string", "max:255"],
            "logo_perusahaan" => [
                "nullable",
                "image",
                "mimes:jpeg,png,jpg,gif",
                "max:2048",
            ],
        ];
    }

    public function messages(): array
    {
        return [
            "name.required" => "Nama wajib diisi.",
            "name.string" => "Nama harus berupa teks.",
            "name.max" => "Nama maksimal 255 karakter.",

            "email.required" => "Email wajib diisi.",
            "email.string" => "Email harus berupa teks.",
            "email.lowercase" => "Email harus dalam huruf kecil.",
            "email.email" => "Format email tidak valid.",
            "email.max" => "Email maksimal 255 karakter.",
            "email.unique" => "Email sudah digunakan oleh pengguna lain.",

            "password.string" => "Password harus berupa teks.",
            "password.min" => "Password minimal 8 karakter.",
            "password.confirmed" => "Konfirmasi password tidak cocok.",

            "password_confirmation.string" =>
                "Konfirmasi password harus berupa teks.",
            "password_confirmation.min" =>
                "Konfirmasi password minimal 8 karakter.",

            "alamat.string" => "Alamat harus berupa teks.",
            "alamat.max" => "Alamat maksimal 255 karakter.",

            "nomor_telepon.string" => "Nomor telepon harus berupa teks.",
            "nomor_telepon.max" => "Nomor telepon maksimal 255 karakter.",

            "logo_perusahaan.image" => "File logo harus berupa gambar.",
            "logo_perusahaan.mimes" =>
                "Logo harus berformat jpeg, png, jpg, atau gif.",
            "logo_perusahaan.max" => "Ukuran logo maksimal 2MB.",
        ];
    }
}
