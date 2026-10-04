<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdministrasiFakturRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'spb_id' => [
                'required',
                'integer',
                Rule::exists('surat_pengiriman_barang', 'id')->where(function ($query): void {
                    $query->where('user_id', auth()->id());
                }),
                Rule::unique('faktur_penjualan', 'spb_id'),
            ],
            'kode_faktur' => [
                'required',
                'string',
                'max:100',
                Rule::unique('faktur_penjualan', 'kode_faktur')->where(function ($query): void {
                    $query->where('user_id', auth()->id());
                }),
            ],
            'tanggal_faktur' => [
                'required',
                'date',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'spb_id.required' => 'Surat Pengiriman Barang (SPB) wajib dipilih.',
            'spb_id.integer' => 'ID Surat Pengiriman Barang harus berupa angka.',
            'spb_id.exists' => 'Surat Pengiriman Barang tidak valid atau bukan milik Anda.',
            'spb_id.unique' => 'Surat Pengiriman Barang ini sudah pernah dibuatkan faktur penjualan.',

            'kode_faktur.required' => 'Kode faktur wajib diisi.',
            'kode_faktur.string' => 'Kode faktur harus berupa teks.',
            'kode_faktur.max' => 'Kode faktur maksimal 100 karakter.',
            'kode_faktur.unique' => 'Kode faktur sudah digunakan.',

            'tanggal_faktur.required' => 'Tanggal faktur wajib diisi.',
            'tanggal_faktur.date' => 'Format tanggal faktur tidak valid.',
        ];
    }
}
