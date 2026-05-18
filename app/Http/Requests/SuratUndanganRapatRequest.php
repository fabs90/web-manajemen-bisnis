<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SuratUndanganRapatRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nomor_surat' => ['required', 'string', 'max:255'],
            'lampiran' => ['nullable', 'string', 'max:255'],
            'file_lampiran' => ['nullable', 'file', 'max:5120'],
            'perihal' => ['required', 'string', 'max:255'],
            'nama_penerima' => ['required', 'string', 'max:255'],
            'email_penerima' => ['required', 'email', 'max:255'],
            'jabatan_penerima' => ['nullable', 'string', 'max:255'],
            'kota_penerima' => ['nullable', 'string', 'max:255'],
            'judul_rapat' => ['required', 'string', 'max:255'],
            'hari' => ['nullable', 'string', 'max:255'],
            'tanggal_rapat' => ['required', 'date'],
            'tempat' => ['nullable', 'string', 'max:255'],
            'waktu_mulai' => ['nullable'],
            'waktu_selesai' => ['nullable'],
            'agenda' => ['nullable', 'array'],
            'agenda.*' => ['nullable', 'string'],
            'tembusan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_surat.required' => 'Nomor surat wajib diisi.',
            'perihal.required' => 'Perihal wajib diisi.',
            'nama_penerima.required' => 'Nama penerima wajib diisi.',
            'judul_rapat.required' => 'Judul rapat wajib diisi.',
            'tanggal_rapat.required' => 'Tanggal rapat wajib diisi.',
            'tanggal_rapat.date' => 'Format tanggal rapat tidak valid.',
        ];
    }
}
