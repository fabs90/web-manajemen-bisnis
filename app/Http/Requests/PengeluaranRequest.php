<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PengeluaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            "potongan_pembelian" => $this->potongan_pembelian ?? 0,
            "biaya_lain" => $this->biaya_lain ?? 0,
            "admin_bank" => $this->admin_bank ?? 0,
            "jumlah" => $this->jumlah ?? 0,
        ]);
    }

    public function rules(): array
    {
        return [
            "uraian_pengeluaran" => "required|string|max:255",
            "jumlah" => "required|numeric|min:0",
            "jumlah_hutang" => "nullable|numeric|min:0",
            "jumlah_manual" =>
                "required_if:jenis_keperluan,lain_lain,membayar_hutang|numeric|min:0",

            "tanggal" => "required|date",
            "jenis_pengeluaran" => "required|in:tunai,kredit",

            "nama_kreditur" =>
                "required_if:jenis_pengeluaran,kredit|integer|exists:pelanggan,id",

            "jenis_keperluan" =>
                "required|in:membeli_barang,lain_lain,membayar_hutang",

            // ✅ Membeli Barang
            "barang_dibeli.*" =>
                "required_if:jenis_keperluan,membeli_barang|exists:barang,id",
            "jumlah_barang_dibeli.*" =>
                "required_if:jenis_keperluan,membeli_barang|integer|min:1",

            // ✅ Membayar Hutang
            "hutang_id" =>
                "required_if:jenis_keperluan,membayar_hutang|nullable|exists:buku_besar_hutang,id",

            "potongan_pembelian" => "nullable|numeric|min:0",
            "biaya_lain" => "nullable|numeric|min:0",
            "admin_bank" => "nullable|numeric|min:0",
        ];
    }

    public function messages(): array
    {
        return [
            "uraian_pengeluaran.required" => "Uraian pengeluaran wajib diisi.",
            "jumlah.required" => "Jumlah pengeluaran wajib diisi.",
            "jumlah.numeric" => "Jumlah pengeluaran harus berupa angka.",
            "tanggal.required" => "Tanggal transaksi pengeluaran wajib diisi.",
            "jumlah_manual.required_if" =>
                "Jumlah pengeluaran wajib diisi jika keperluan adalah membayar hutang atau lain-lain.",
            "jumlah_manual.numeric" => "Jumlah pengeluaran harus berupa angka.",
            "jumlah_manual.min" =>
                "Jumlah pengeluaran tidak boleh kurang dari 0.",

            "jenis_pengeluaran.required" => "Jenis pengeluaran wajib dipilih.",
            "nama_kreditur.required_if" =>
                "Nama kreditur wajib diisi jika jenis pengeluaran adalah kredit.",
            "nama_kreditur.exists" => "Kreditur yang dipilih tidak valid.",

            "jenis_keperluan.required" => "Jenis keperluan wajib dipilih.",

            // Membeli Barang
            "barang_dibeli.required_if" =>
                "Barang dibeli wajib diisi jika keperluan adalah membeli barang.",
            "barang_dibeli.exists" => "Barang yang dipilih tidak valid.",
            "jumlah_barang_dibeli.required_if" =>
                "Jumlah barang dibeli wajib diisi jika keperluan adalah membeli barang.",
            "jumlah_barang_dibeli.integer" =>
                "Jumlah barang dibeli harus berupa angka bulat.",
            "jumlah_barang_dibeli.min" =>
                "Jumlah barang dibeli tidak boleh kurang dari 1.",

            // Membayar Hutang
            "hutang_id.required_if" =>
                "Hutang wajib dipilih jika keperluan adalah membayar hutang.",
            "hutang_id.exists" => "Data hutang yang dipilih tidak valid.",

            "potongan_pembelian.numeric" =>
                "Potongan pembelian harus berupa angka.",
            "biaya_lain.numeric" => "Biaya lain harus berupa angka.",
            "admin_bank.numeric" => "Admin bank harus berupa angka.",
            "admin_bank.min" => "Admin bank tidak boleh kurang dari 0.",
        ];
    }
}
