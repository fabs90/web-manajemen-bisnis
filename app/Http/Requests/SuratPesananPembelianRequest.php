<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuratPesananPembelianRequest extends FormRequest
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
            'supplier_id' => [
                'required',
                Rule::exists('pelanggan', 'id')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                }),
            ],
            'alamat_supplier' => 'required|string',
            'email_supplier' => 'required|email|max:255',
            'nomor_pesanan_pembelian' => 'required|string|max:255',
            'tanggal_pesanan_pembelian' => 'required|date',
            'tanggal_kirim_pesanan_pembelian' => 'required|date',
            'nama_pimpinan_supplier' => 'required|string|max:255',
            'ttd_pimpinan_supplier' => 'nullable|file|mimes:jpg,png,jpeg|max:2048',
            'detail' => 'required|array|min:1',
            'detail.*.barang_id' => [
                'required',
                Rule::exists('barang', 'id')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                }),
            ],
            'detail.*.nama_barang' => 'required|string|max:255',
            'detail.*.kuantitas' => 'required',
            'detail.*.harga' => 'required',
            'detail.*.total' => 'required',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'supplier_id' => 'supplier',
            'alamat_supplier' => 'alamat supplier',
            'email_supplier' => 'email supplier',
            'nomor_pesanan_pembelian' => 'nomor pesanan pembelian',
            'tanggal_pesanan_pembelian' => 'tanggal pesanan pembelian',
            'tanggal_kirim_pesanan_pembelian' => 'tanggal kirim pesanan',
            'nama_bagian_supplier' => 'nama pimpinan supplier',
            'ttd_pimpinan_supplier' => 'tanda tangan pimpinan supplier',
            'detail' => 'detail barang',
            'detail.*.barang_id' => 'barang',
            'detail.*.nama_barang' => 'nama barang',
            'detail.*.kuantitas' => 'kuantitas',
            'detail.*.harga' => 'harga',
            'detail.*.total' => 'total',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'exists' => ':attribute tidak valid atau tidak ditemukan.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'array' => ':attribute harus berupa array.',
            'min' => ':attribute minimal memiliki :min item.',
        ];
    }
}
