<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PendapatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Hanya bersihkan nilai yang benar-benar ada di request.
     */
    protected function prepareForValidation()
    {
        $clean = fn($val) => $val === null || $val === ""
            ? null
            : (int) round((float) str_replace(["Rp ", "."], "", $val));

        $this->merge([
            // Hanya merge bila ada di request (bisa null)
            "jumlah" => $this->filled("jumlah") ? $clean($this->jumlah) : null,
            "potongan_pembelian" => $this->filled("potongan_pembelian")
                ? $clean($this->potongan_pembelian)
                : null,
            // "biaya_lain" dihapus karena sudah dipindah ke halaman lain
            "bunga_bank" => $this->filled("bunga_bank")
                ? $clean($this->bunga_bank)
                : null,
            "jumlah_barang_dijual" => $this->filled("jumlah_barang_dijual")
                ? (int) $this->jumlah_barang_dijual
                : null,

            // Checkbox → boolean (hanya true bila ter-centang)
            "barang_terjual_check" => $this->has("barang_terjual_check"),
            "ada_debitur_check" => $this->has("ada_debitur_check"),
        ]);
    }

    public function rules(): array
    {
        $jenis = $this->input("jenis_pendapatan");

        return [
            // -------------------------------------------------
            // SELALU WAJIB
            // -------------------------------------------------
            "uraian_pendapatan" => ["required", "string", "max:255"],
            "tanggal" => ["required", "date"],
            "jenis_pendapatan" => [
                "required",
                Rule::in(["tunai", "piutang", "kredit"]), // "lain-lain" dihapus karena sudah dipindah halaman
            ],

            // -------------------------------------------------
            // JUMLAH (wajib untuk semua jenis yang tersisa)
            // -------------------------------------------------
            "jumlah" => [
                "required", // Sekarang wajib untuk semua jenis
                "integer",
                "min:1",
            ],

            // -------------------------------------------------
            // BIAYA TAMBAHAN LAINNYA
            // -------------------------------------------------
            "potongan_pembelian" => ["nullable", "integer", "min:0"],
            "bunga_bank" => ["nullable", "integer", "min:0"],

            // -------------------------------------------------
            // DEBITUR
            // -------------------------------------------------
            "ada_debitur_check" => ["nullable", "boolean"],

            "nama_pelanggan" => [
                Rule::requiredIf(fn() => $this->ada_debitur_check === true),
                "nullable",
                "integer",
                "exists:pelanggan,id",
            ],

            // -------------------------------------------------
            // PIUTANG / HUTANG
            // -------------------------------------------------
            "piutang_aktif" => [
                Rule::requiredIf(
                    fn() => $jenis === "piutang" &&
                        $this->ada_debitur_check === true &&
                        $this->filled("piutang_aktif"),
                ),
                "nullable",
                "string",
                Rule::exists("buku_besar_piutang", "kode"),
            ],

            "hutang_aktif" => [
                Rule::requiredIf(fn() => $jenis === "kredit"),
                Rule::prohibitedIf(fn() => $jenis !== "kredit"),
                "nullable",
                "string",
                Rule::exists("buku_besar_piutang", "kode"),
            ],

            // -------------------------------------------------
            // BARANG TERJUAL
            // -------------------------------------------------
            "barang_terjual" => [
                Rule::when(
                    fn() => $this->barang_terjual_check === false,
                    ["required", "integer", "exists:barang,id"],
                    ["nullable", "integer"],
                ),
            ],

            "jumlah_barang_dijual" => [
                Rule::when(
                    fn() => $this->barang_terjual_check === false,
                    ["required", "integer", "min:1"],
                    ["nullable", "integer"],
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // umum
            "uraian_pendapatan.required" => "Uraian pendapatan wajib diisi.",
            "tanggal.required" => "Tanggal transaksi wajib diisi.",
            "jenis_pendapatan.required" => "Jenis pendapatan wajib dipilih.",
            "jenis_pendapatan.in" => "Jenis pendapatan tidak valid.",

            // jumlah
            "jumlah.required" => "Jumlah penjualan wajib diisi.",
            "jumlah.min" => "Jumlah minimal Rp 1.",

            // debitur
            "nama_pelanggan.required_if" =>
                "Pilih debitur jika ada transaksi piutang/kredit.",
            "nama_pelanggan.exists" => "Debitur tidak ditemukan.",

            // piutang / hutang
            "piutang_aktif.required_if" =>
                "Pilih piutang aktif yang ingin ditambah.",
            "piutang_aktif.prohibited_if" =>
                "Piutang aktif hanya untuk jenis Piutang.",
            "piutang_aktif.exists" =>
                "Kode piutang tidak valid atau sudah lunas.",

            "hutang_aktif.required_if" =>
                "Pilih hutang aktif yang ingin dilunasi.",
            "hutang_aktif.prohibited_if" =>
                "Hutang aktif hanya untuk jenis Kredit.",
            "hutang_aktif.exists" =>
                "Kode hutang tidak valid atau sudah lunas.",

            // barang
            "barang_terjual.required" =>
                'Pilih barang atau centang "Tidak ada barang terjual".',
            "jumlah_barang_dijual.required" => "Jumlah barang wajib diisi.",
            "jumlah_barang_dijual.min" => "Jumlah minimal 1.",
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $jenis = $this->input("jenis_pendapatan");

            // ---- piutang aktif ----
            if ($jenis === "piutang" && $this->filled("piutang_aktif")) {
                $piutang = \App\Models\BukuBesarPiutang::where(
                    "kode",
                    $this->piutang_aktif,
                )->first();
                if (!$piutang || $piutang->saldo <= 0) {
                    $validator
                        ->errors()
                        ->add(
                            "piutang_aktif",
                            "Kode piutang tidak valid atau sudah lunas.",
                        );
                }
            }

            // ---- hutang aktif ----
            if ($jenis === "kredit" && $this->filled("hutang_aktif")) {
                $hutang = \App\Models\BukuBesarPiutang::where(
                    "kode",
                    $this->hutang_aktif,
                )->first();
                if (!$hutang || $hutang->saldo <= 0) {
                    $validator
                        ->errors()
                        ->add(
                            "hutang_aktif",
                            "Kode hutang tidak valid atau sudah lunas.",
                        );
                }
            }
        });
    }
}
