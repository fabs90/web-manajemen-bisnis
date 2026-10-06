<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreKartuGudangRequest extends FormRequest
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
            'tanggal' => ['required', 'date'],
            'uraian' => ['required', 'string'],
            'diterima' => ['nullable', 'integer', 'min:0'],
            'dikeluarkan' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',

            'uraian.required' => 'Uraian wajib diisi.',
            'uraian.string' => 'Uraian harus berupa teks.',

            'diterima.integer' => 'Jumlah diterima harus berupa bilangan bulat.',
            'diterima.min' => 'Jumlah diterima tidak boleh kurang dari 0.',

            'dikeluarkan.integer' => 'Jumlah dikeluarkan harus berupa bilangan bulat.',
            'dikeluarkan.min' => 'Jumlah dikeluarkan tidak boleh kurang dari 0.',
        ];
    }
}
