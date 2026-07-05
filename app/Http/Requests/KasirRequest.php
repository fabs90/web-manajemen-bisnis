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
            'uang_bayar' => ['required', 'numeric', 'gte:grand_total'],
            'uang_kembalian' => ['required', 'numeric', 'min:0'],
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
