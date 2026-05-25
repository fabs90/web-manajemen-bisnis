<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuratPesananPenjualanRequest extends FormRequest
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
            'pelanggan_id' => [
                'required',
                Rule::exists('pelanggan', 'id')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                }),
            ],
            'alamat_pelanggan' => ['required'],
            'nomor_pesanan_pembelian' => ['required', 'string', 'max:255'],
            'tanggal_pesanan_pembelian' => ['required', 'date'],
            'tanggal_kirim_pesanan_pembelian' => ['required', 'date'],
            'nama_pelanggan' => ['required', 'string', 'max:255'],
            'ttd_pelanggan' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'detail' => 'required|array|min:1',
            'detail.*.nama_barang' => 'required|string|max:255',
            'detail.*.kuantitas' => 'required|string',
            'detail.*.harga' => 'required|string',
            'detail.*.total' => 'required|string',
        ];
    }
}
