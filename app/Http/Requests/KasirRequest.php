<?php

namespace App\Http\Requests;

use App\Models\JenisPembayaran;
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
        $grandTotal = $this->has('grand_total')
            ? (float) str_replace(['Rp', '.', ' '], '', (string) $this->grand_total)
            : 0.0;

        $jenisPembayaran = $this->filled('jenis_pembayaran_id')
            ? JenisPembayaran::find($this->jenis_pembayaran_id)
            : null;
        $namaMetode = $jenisPembayaran ? strtolower(str_replace([' ', '-'], '_', $jenisPembayaran->nama)) : '';
        $isNonTunai = in_array($namaMetode, ['transfer_bank', 'qris']);

        if ($isNonTunai) {
            $uangBayar = $this->filled('uang_bayar')
                ? (float) str_replace(['Rp', '.', ' '], '', (string) $this->uang_bayar)
                : $grandTotal;

            $this->merge([
                'grand_total' => $grandTotal,
                'uang_bayar' => $uangBayar,
                'uang_kembalian' => 0.0,
            ]);
        } else {
            $this->merge([
                'grand_total' => $grandTotal,
                'uang_bayar' => $this->filled('uang_bayar')
                    ? (float) str_replace(['Rp', '.', ' '], '', (string) $this->uang_bayar)
                    : null,
                'uang_kembalian' => $this->filled('uang_kembalian')
                    ? (float) str_replace(['Rp', '.', ' '], '', (string) $this->uang_kembalian)
                    : null,
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
        $jenisPembayaran = $this->filled('jenis_pembayaran_id')
            ? JenisPembayaran::find($this->jenis_pembayaran_id)
            : null;
        $namaMetode = $jenisPembayaran ? strtolower(str_replace([' ', '-'], '_', $jenisPembayaran->nama)) : '';
        $isNonTunai = in_array($namaMetode, ['transfer_bank', 'qris']);

        return [
            'jenis_pembayaran_id' => ['required', 'exists:jenis_pembayaran,id'],
            'grand_total' => ['required', 'numeric', 'min:0'],
            'id_barang_terjual' => ['required', 'array', 'min:1'],
            'id_barang_terjual.*' => ['required', 'exists:barang,id'],
            'jumlah_barang_dijual' => ['required', 'array'],
            'jumlah_barang_dijual.*' => ['required', 'numeric', 'min:1'],
            'uang_bayar' => $isNonTunai ? ['nullable', 'numeric'] : ['required', 'numeric', 'gte:grand_total'],
            'uang_kembalian' => $isNonTunai ? ['nullable', 'numeric'] : ['required', 'numeric', 'min:0'],
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
