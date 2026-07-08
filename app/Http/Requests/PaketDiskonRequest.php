<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaketDiskonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_paket' => ['required', 'string', 'max:255'],
            'jenis_diskon' => ['required', 'in:persentase,nominal'],
            'nilai_diskon' => ['required', 'numeric', 'min:0'],
            'minimal_pembelian' => ['nullable', 'numeric', 'min:0'],
            'barang_id' => ['nullable', 'exists:barang,id'],
            'is_active' => ['boolean'],
        ];
    }
    
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->has('is_active'),
            'minimal_pembelian' => $this->minimal_pembelian ? (int) str_replace(['Rp', '.', ' '], '', $this->minimal_pembelian) : 0,
            'nilai_diskon' => $this->jenis_diskon === 'nominal' && $this->nilai_diskon ? (float) str_replace(['Rp', '.', ' '], '', $this->nilai_diskon) : (float) $this->nilai_diskon,
        ]);
    }
}
