<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class KasirRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Set to true because authorization is handled via middleware or policies
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('uang_bayar')) {
            $this->merge([
                'uang_bayar' => (float) str_replace(['Rp', '.', ' '], '', $this->uang_bayar),
            ]);
        }

        if ($this->has('uang_kembalian')) {
            $this->merge([
                'uang_kembalian' => (float) str_replace(['Rp', '.', ' '], '', $this->uang_kembalian),
            ]);
        }

        if ($this->has('grand_total')) {
            $this->merge([
                'grand_total' => (float) str_replace(['Rp', '.', ' '], '', $this->grand_total),
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
        return [
            'jenis_pembayaran_id' => ['required', 'exists:jenis_pembayaran,id'],
            'grand_total' => ['required', 'numeric', 'min:0'],
            'id_barang_terjual' => ['required', 'array', 'min:1'],
            'id_barang_terjual.*' => ['required', 'exists:barang,id'],
            'jumlah_barang_dijual' => ['required', 'array'],
            'jumlah_barang_dijual.*' => ['required', 'numeric', 'min:1'],
            'uang_bayar' => ['required', 'gte:grand_total'],
            'uang_kembalian' => ['required', 'min:0'],
            'diskon_total' => ['nullable', 'numeric', 'min:0'],
            'paket_diskon_id' => ['nullable', 'exists:paket_diskons,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'jenis_pembayaran_id.required' => 'Jenis pembayaran harus dipilih.',
            'jenis_pembayaran_id.exists' => 'Jenis pembayaran tidak valid.',
            'grand_total.required' => 'Grand total tidak boleh kosong.',
            'id_barang_terjual.required' => 'Daftar barang tidak boleh kosong.',
            'id_barang_terjual.min' => 'Pilih minimal satu barang.',
            'id_barang_terjual.*.exists' => 'Barang yang dipilih tidak valid.',
            'jumlah_barang_dijual.required' => 'Jumlah barang harus diisi.',
            'jumlah_barang_dijual.*.required' => 'Jumlah barang tidak boleh kosong.',
            'jumlah_barang_dijual.*.min' => 'Jumlah barang minimal 1.',
            'uang_bayar.required' => 'Uang bayar harus diisi.',
            'uang_bayar.gte' => 'Uang bayar tidak boleh kurang dari grand total.',
            'uang_kembalian.required' => 'Uang kembalian harus dihitung dengan benar.',
        ];
    }
}
