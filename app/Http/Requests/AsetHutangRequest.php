<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AsetHutangRequest extends FormRequest
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
            // Aset Lancar
            "kas" => ["required", "numeric", "min:0"],
            "uraian_kas" => ["required", "string", "max:255"],

            // Piutang (bisa dikosongkan jika checkbox tidak_ada_piutang dicentang)
            "tidak_ada_piutang" => ["nullable", "boolean"],
            "piutang" => ["exclude_if:tidak_ada_piutang,1", "array", "min:1"],
            "piutang.*.nama" => [
                "exclude_if:tidak_ada_piutang,1",
                "required",
                Rule::exists("pelanggan", "id")->where(
                    fn($q) => $q->where("jenis", "debitur"),
                ),
            ],
            "piutang.*.jumlah" => [
                "exclude_if:tidak_ada_piutang,1",
                "required",
                "numeric",
                "min:0",
            ],
            "piutang.*.uraian" => [
                "exclude_if:tidak_ada_piutang,1",
                "required",
                "string",
                "max:255",
            ],
            "piutang.*.jatuh_tempo_piutang" => [
                "exclude_if:tidak_ada_piutang,1",
                "required",
                "date",
            ],

            // Hutang (bisa dikosongkan jika checkbox tidak_ada_hutang dicentang)
            "tidak_ada_hutang" => ["nullable", "boolean"],
            "hutang" => ["exclude_if:tidak_ada_hutang,1", "array", "min:1"],
            "hutang.*.nama" => [
                "exclude_if:tidak_ada_hutang,1",
                "required",
                Rule::exists("pelanggan", "id")->where(
                    fn($q) => $q->where("jenis", "kreditur"),
                ),
            ],
            "hutang.*.jumlah" => [
                "exclude_if:tidak_ada_hutang,1",
                "required",
                "numeric",
                "min:0",
            ],
            "hutang.*.uraian" => [
                "exclude_if:tidak_ada_hutang,1",
                "required",
                "string",
                "max:255",
            ],
            "hutang.*.jatuh_tempo_hutang" => [
                "exclude_if:tidak_ada_hutang,1",
                "required",
                "date",
            ],

            // Barang
            "barang_ids" => ["required", "array"],
            "barang_ids.*" => ["exists:barang,id"],

            // Persediaan
            "total_persediaan" => ["required", "numeric", "min:0"],

            // Aset Tetap
            "tanah_bangunan" => ["required", "numeric", "min:0"],
            "kendaraan" => ["required", "numeric", "min:0"],
            "meubel_peralatan" => ["required", "numeric", "min:0"],
        ];
    }

    public function messages(): array
    {
        return [
            // Aset Lancar
            "kas.required" => "Field kas/bank wajib diisi.",
            "kas.numeric" => "Nilai kas/bank harus berupa angka.",
            "uraian_kas.required" => "Uraian kas wajib diisi.",
            "uraian_kas.max" =>
                "Uraian kas tidak boleh lebih dari 255 karakter.",

            // Piutang
            "piutang.required" => "Data piutang wajib diisi minimal satu.",
            "piutang.array" => "Format piutang tidak valid.",
            "piutang.*.nama.required" =>
                "Nama debitur pada piutang wajib diisi.",
            "piutang.*.nama.exists" => "Debitur tidak ditemukan dalam daftar.",
            "piutang.*.jumlah.required" => "Jumlah piutang wajib diisi.",
            "piutang.*.jumlah.numeric" => "Jumlah piutang harus berupa angka.",
            "piutang.*.uraian.required" => "Uraian piutang wajib diisi.",
            "piutang.*.jatuh_tempo_piutang.required" =>
                "Tanggal jatuh tempo piutang wajib diisi.",
            "piutang.*.jatuh_tempo_piutang.date" =>
                "Format tanggal jatuh tempo piutang tidak valid.",

            // Hutang
            "hutang.required" => "Data hutang wajib diisi minimal satu.",
            "hutang.array" => "Format hutang tidak valid.",
            "hutang.*.nama.required" =>
                "Nama kreditur pada hutang wajib diisi.",
            "hutang.*.nama.exists" => "Kreditur tidak ditemukan dalam daftar.",
            "hutang.*.jumlah.required" => "Jumlah hutang wajib diisi.",
            "hutang.*.jumlah.numeric" => "Jumlah hutang harus berupa angka.",
            "hutang.*.uraian.required" => "Uraian hutang wajib diisi.",
            "hutang.*.jatuh_tempo_hutang.required" =>
                "Tanggal jatuh tempo hutang wajib diisi.",
            "hutang.*.jatuh_tempo_hutang.date" =>
                "Format tanggal jatuh tempo hutang tidak valid.",

            // Barang (Persediaan Barang Dagangan)
            "barang_ids.required" =>
                "Minimal satu barang dagangan harus dipilih.",
            "barang_ids.array" => "Format data barang tidak valid.",
            "barang_ids.*.exists" =>
                "Barang yang dipilih tidak ditemukan dalam daftar.",

            // Persediaan
            "total_persediaan.required" =>
                "Nilai total persediaan wajib diisi.",
            "total_persediaan.numeric" =>
                "Total persediaan harus berupa angka.",

            // Aset Tetap
            "tanah_bangunan.required" =>
                "Nilai tanah dan bangunan wajib diisi.",
            "kendaraan.required" => "Nilai kendaraan wajib diisi.",
            "meubel_peralatan.required" =>
                "Nilai meubel & peralatan wajib diisi.",
        ];
    }
}
