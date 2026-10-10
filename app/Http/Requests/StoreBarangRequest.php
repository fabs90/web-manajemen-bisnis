<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBarangRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare inputs before validation.
     */
    protected function prepareForValidation(): void
    {
        if (auth()->user()?->role === 'nelayan') {
            $this->merge([
                'jumlah_max' => $this->jumlah_max ?? 0,
                'jumlah_min' => $this->jumlah_min ?? 0,
                'jumlah_unit_per_kemasan' => $this->jumlah_unit_per_kemasan ?? 1,
                'harga_beli_per_kemas' => $this->harga_beli_per_kemas ?? 0,
                'harga_beli_per_unit' => $this->harga_beli_per_unit ?? 0,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isNelayan = auth()->user()?->role === 'nelayan';

        return [
            'kode_barang' => ['required', 'string', 'max:100'],
            'nama' => ['required', 'string', 'max:255'],
            'jumlah_max' => [$isNelayan ? 'nullable' : 'required', 'integer', 'min:0'],
            'jumlah_min' => [$isNelayan ? 'nullable' : 'required', 'integer', 'min:0'],
            'jumlah_unit_per_kemasan' => [$isNelayan ? 'nullable' : 'required', 'integer', 'min:0'],
            'harga_beli_per_kemas' => [$isNelayan ? 'nullable' : 'required', 'numeric', 'min:0'],
            'harga_beli_per_unit' => [$isNelayan ? 'nullable' : 'required', 'numeric', 'min:0'],
            'harga_jual_per_unit' => ['required', 'numeric', 'min:0'],
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
            'kode_barang.required' => 'Kode barang wajib diisi.',
            'kode_barang.string' => 'Kode barang harus berupa teks.',
            'kode_barang.max' => 'Kode barang tidak boleh lebih dari 100 karakter.',

            'nama.required' => 'Nama barang wajib diisi.',
            'nama.string' => 'Nama barang harus berupa teks.',
            'nama.max' => 'Nama barang tidak boleh lebih dari 255 karakter.',

            'jumlah_max.required' => 'Jumlah maksimum wajib diisi.',
            'jumlah_max.integer' => 'Jumlah maksimum harus berupa bilangan bulat.',
            'jumlah_max.min' => 'Jumlah maksimum tidak boleh kurang dari 0.',

            'jumlah_min.required' => 'Jumlah minimum wajib diisi.',
            'jumlah_min.integer' => 'Jumlah minimum harus berupa bilangan bulat.',
            'jumlah_min.min' => 'Jumlah minimum tidak boleh kurang dari 0.',

            'jumlah_unit_per_kemasan.required' => 'Jumlah unit per kemasan wajib diisi.',
            'jumlah_unit_per_kemasan.integer' => 'Jumlah unit per kemasan harus berupa bilangan bulat.',
            'jumlah_unit_per_kemasan.min' => 'Jumlah unit per kemasan tidak boleh kurang dari 0.',

            'harga_beli_per_kemas.required' => 'Harga beli per kemasan wajib diisi.',
            'harga_beli_per_kemas.numeric' => 'Harga beli per kemasan harus berupa angka.',
            'harga_beli_per_kemas.min' => 'Harga beli per kemasan tidak boleh kurang dari 0.',

            'harga_beli_per_unit.required' => 'Harga beli per unit wajib diisi.',
            'harga_beli_per_unit.numeric' => 'Harga beli per unit harus berupa angka.',
            'harga_beli_per_unit.min' => 'Harga beli per unit tidak boleh kurang dari 0.',

            'harga_jual_per_unit.required' => 'Harga jual per unit wajib diisi.',
            'harga_jual_per_unit.numeric' => 'Harga jual per unit harus berupa angka.',
            'harga_jual_per_unit.min' => 'Harga jual per unit tidak boleh kurang dari 0.',
        ];
    }
}
