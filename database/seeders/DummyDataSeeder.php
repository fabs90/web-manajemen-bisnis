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
                "user_id" => 2,
                "nama" => "PT Sumber Makmur",
                "alamat" => "Jl. Melati No.12, Bandung",
                "kontak" => "081234567890",
                "email" => "sumbermakmur@example.com",
                "jenis" => "debitur",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "user_id" => 2,
                "nama" => "CV Sentosa Jaya",
                "alamat" => "Jl. Merdeka No.45, Surabaya",
                "kontak" => "082233445566",
                "email" => "sentosajaya@example.com",
                "jenis" => "debitur",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "user_id" => 2,
                "nama" => "UD Berkah Abadi",
                "alamat" => "Jl. Cempaka No.33, Semarang",
                "kontak" => "087712345678",
                "email" => "berkahabadi@example.com",
                "jenis" => "debitur",
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);

        // === Supplier (Kreditur) ===
        DB::table("pelanggan")->insert([
            [
                "user_id" => 2,
                "nama" => "PT Bumi Rezeki",
                "alamat" => "Jl. Sudirman No.88, Jakarta",
                "kontak" => "081322334455",
                "email" => "bumirezeki@example.com",
                "jenis" => "kreditur",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "user_id" => 2,
                "nama" => "CV Makmur Bersama",
                "alamat" => "Jl. Gatot Subroto No.21, Medan",
                "kontak" => "081298765432",
                "email" => "makmurbersama@example.com",
                "jenis" => "kreditur",
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);
    }
}
