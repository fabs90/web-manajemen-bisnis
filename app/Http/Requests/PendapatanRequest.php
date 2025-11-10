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
     * Bersihkan nilai untuk array barang dan lainnya.
     */
    protected function prepareForValidation()
    {
        $clean = fn($val) => $val === null || $val === ""
            ? null
            : (int) round((float) str_replace(["Rp ", "."], "", $val));

        // Bersihkan array barang
        $barangTerjual = [];
        $jumlahBarangDijual = [];
        $potonganPembelian = [];

        if (
            $this->filled("barang_terjual") &&
            is_array($this->barang_terjual)
        ) {
            foreach ($this->barang_terjual as $index => $barang) {
                $barangTerjual[$index] = $barang ? (int) $barang : null;
                $jumlahBarangDijual[$index] = $this->filled(
                    "jumlah_barang_dijual.{$index}",
                )
                    ? (int) $this->input("jumlah_barang_dijual.{$index}")
                    : null;
                $potonganPembelian[$index] = $this->filled(
                    "potongan_pembelian.{$index}",
                )
                    ? $clean($this->input("potongan_pembelian.{$index}"))
                    : null;
            }
        }

        $this->merge([
            "jumlah" => $this->filled("jumlah") ? $clean($this->jumlah) : null,
            "bunga_bank" => $this->filled("bunga_bank")
                ? $clean($this->bunga_bank)
                : null,
            "biaya_lain" => $this->filled("biaya_lain")
                ? $clean($this->biaya_lain)
                : null,

            // Array barang
            "barang_terjual" => $barangTerjual,
            "jumlah_barang_dijual" => $jumlahBarangDijual,
            "potongan_pembelian" => $potonganPembelian,

            // Checkbox → boolean
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
                Rule::in(["tunai", "piutang", "kredit"]),
            ],

            // -------------------------------------------------
            // JUMLAH (wajib untuk semua jenis)
            // -------------------------------------------------
            "jumlah" => ["required", "integer", "gte:0"],
            "jumlah_piutang" => "nullable|numeric|min:0",
            "jumlah_kredit" => "nullable|numeric|min:0",

            // -------------------------------------------------
            // BIAYA TAMBAHAN LAINNYA
            // -------------------------------------------------
            "bunga_bank" => ["nullable", "integer", "min:0"],
            "biaya_lain" => ["nullable", "integer", "min:0"],

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
                Rule::requiredIf(
                    fn() => $jenis === "kredit" &&
                        $this->filled("hutang_aktif"),
                ),
                Rule::prohibitedIf(fn() => $jenis !== "kredit"),
                "nullable",
                "string",
                Rule::exists("buku_besar_piutang", "kode"),
            ],

            // -------------------------------------------------
            // BARANG TERJUAL (array)
            // -------------------------------------------------
            "barang_terjual" => [
                Rule::when(
                    fn() => $this->barang_terjual_check === false,
                    ["required", "array", "min:1"],
                    ["nullable", "array"],
                ),
            ],

            "barang_terjual.*" => [
                Rule::when(
                    fn() => $this->barang_terjual_check === false,
                    ["required", "integer", "exists:barang,id"],
                    ["nullable", "integer"],
                ),
            ],

            "jumlah_barang_dijual" => [
                Rule::when(
                    fn() => $this->barang_terjual_check === false,
                    ["required", "array", "min:1"],
                    ["nullable", "array"],
                ),
            ],

            "jumlah_barang_dijual.*" => [
                Rule::when(
                    fn() => $this->barang_terjual_check === false,
                    ["required", "integer", "min:1"],
                    ["nullable", "integer"],
                ),
            ],

            "potongan_pembelian" => [
                Rule::when(
                    fn() => $this->barang_terjual_check === false,
                    ["nullable", "array"],
                    ["nullable", "array"],
                ),
            ],

            "potongan_pembelian.*" => ["nullable", "integer", "min:0"],
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
            "barang_terjual.array" => "Barang terjual harus berupa array.",
            "barang_terjual.min" => "Minimal satu barang harus dipilih.",
            "barang_terjual.*.required" => "Barang wajib dipilih.",
            "barang_terjual.*.exists" => "Barang tidak ditemukan.",

            "jumlah_barang_dijual.required" => "Jumlah barang wajib diisi.",
            "jumlah_barang_dijual.array" => "Jumlah barang harus berupa array.",
            "jumlah_barang_dijual.min" =>
                "Minimal satu jumlah barang harus diisi.",
            "jumlah_barang_dijual.*.required" => "Jumlah barang wajib diisi.",
            "jumlah_barang_dijual.*.min" => "Jumlah minimal 1.",

            "potongan_pembelian.*.min" => "Potongan minimal 0.",
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

            // ---- Validasi jumlah total dari subtotal barang ----
            if (
                $this->barang_terjual_check === false &&
                $this->filled("barang_terjual")
            ) {
                $totalCalculated = 0;
                foreach ($this->barang_terjual as $index => $barangId) {
                    if ($barangId) {
                        $barang = \App\Models\Barang::find($barangId);
                        if ($barang) {
                            $harga = $barang->harga_jual_per_unit;
                            $qty =
                                $this->input("jumlah_barang_dijual.{$index}") ??
                                0;
                            $potongan =
                                $this->input("potongan_pembelian.{$index}") ??
                                0;
                            $subtotal = max($harga * $qty - $potongan, 0);
                            $totalCalculated += $subtotal;
                        }
                    }
                }
                if ($totalCalculated != $this->jumlah) {
                    $validator
                        ->errors()
                        ->add(
                            "jumlah",
                            "Jumlah total tidak sesuai dengan kalkulasi barang terjual.",
                        );
                }
            }
        });
    }
}
