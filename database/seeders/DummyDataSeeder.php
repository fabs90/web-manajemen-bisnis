<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // === Pelanggan (Debitur) ===
        DB::table("pelanggan")->insert([
            [
                "nama_pelanggan" => "PT Sumber Makmur",
                "alamat" => "Jl. Melati No.12, Bandung",
                "no_telp" => "081234567890",
                "email" => "sumbermakmur@example.com",
                "jenis" => "debitur",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "nama_pelanggan" => "CV Sentosa Jaya",
                "alamat" => "Jl. Merdeka No.45, Surabaya",
                "no_telp" => "082233445566",
                "email" => "sentosajaya@example.com",
                "jenis" => "debitur",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "nama_pelanggan" => "UD Berkah Abadi",
                "alamat" => "Jl. Cempaka No.33, Semarang",
                "no_telp" => "087712345678",
                "email" => "berkahabadi@example.com",
                "jenis" => "debitur",
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);

        // === Supplier (Kreditur) ===
        DB::table("pelanggan")->insert([
            [
                "nama_supplier" => "PT Bumi Rezeki",
                "alamat" => "Jl. Sudirman No.88, Jakarta",
                "no_telp" => "081322334455",
                "email" => "bumirezeki@example.com",
                "jenis" => "kreditur",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "nama_supplier" => "CV Makmur Bersama",
                "alamat" => "Jl. Gatot Subroto No.21, Medan",
                "no_telp" => "081298765432",
                "email" => "makmurbersama@example.com",
                "jenis" => "kreditur",
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);

        // === Barang Dagangan ===
        DB::table("barang")->insert([
            [
                "nama_barang" => "Beras Premium 5Kg",
                "kode_barang" => "BRG-001",
                "harga_beli_per_unit" => 75000,
                "harga_jual_per_unit" => 95000,
                "stok" => 120,
                "satuan" => "sak",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "nama_barang" => "Minyak Goreng 1L",
                "kode_barang" => "BRG-002",
                "harga_beli_per_unit" => 18000,
                "harga_jual_per_unit" => 23000,
                "stok" => 250,
                "satuan" => "botol",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "nama_barang" => "Gula Pasir 1Kg",
                "kode_barang" => "BRG-003",
                "harga_beli_per_unit" => 14500,
                "harga_jual_per_unit" => 19000,
                "stok" => 300,
                "satuan" => "kg",
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);
    }
}
