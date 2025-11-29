<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barang;

class BarangSeeder extends Seeder
{
    public function run()
    {
        Barang::insert([
            [
                "kode_barang" => "BRG-001",
                "nama" => "Beras Premium 5kg",
                "user_id" => 2,
                "jumlah_max" => 200,
                "jumlah_min" => 10,
                "jumlah_unit_per_kemasan" => 1,
                "harga_beli_per_unit" => 65000,
                "harga_beli_per_kemas" => 65000,
                "harga_jual_per_unit" => 75000,
            ],
            [
                "kode_barang" => "BRG-002",
                "nama" => "Gula Pasir 1kg",
                "user_id" => 2,
                "jumlah_max" => 400,
                "jumlah_min" => 20,
                "jumlah_unit_per_kemasan" => 1,
                "harga_beli_per_unit" => 14000,
                "harga_beli_per_kemas" => 14000,
                "harga_jual_per_unit" => 17000,
            ],
            [
                "kode_barang" => "BRG-003",
                "nama" => "Minyak Goreng 1L",
                "user_id" => 2,
                "jumlah_max" => 500,
                "jumlah_min" => 25,
                "jumlah_unit_per_kemasan" => 1,
                "harga_beli_per_unit" => 13000,
                "harga_beli_per_kemas" => 13000,
                "harga_jual_per_unit" => 16000,
            ],
            [
                "kode_barang" => "BRG-004",
                "nama" => "Kopi Bubuk Sachet (Pack 10 pcs)",
                "user_id" => 2,
                "jumlah_max" => 150,
                "jumlah_min" => 10,
                "jumlah_unit_per_kemasan" => 10,
                "harga_beli_per_unit" => 1000, // per sachet
                "harga_beli_per_kemas" => 10000, // 1 pack
                "harga_jual_per_unit" => 1500,
            ],
            [
                "kode_barang" => "BRG-005",
                "nama" => "Sabun Cuci Piring 750ml",
                "user_id" => 2,
                "jumlah_max" => 250,
                "jumlah_min" => 20,
                "jumlah_unit_per_kemasan" => 1,
                "harga_beli_per_unit" => 10000,
                "harga_beli_per_kemas" => 10000,
                "harga_jual_per_unit" => 14000,
            ],
            [
                "kode_barang" => "BRG-006",
                "nama" => "Air Mineral Cup (Dus 48 pcs)",
                "user_id" => 2,
                "jumlah_max" => 120,
                "jumlah_min" => 10,
                "jumlah_unit_per_kemasan" => 48,
                "harga_beli_per_unit" => 250, // per cup
                "harga_beli_per_kemas" => 12000, // per duduk 48
                "harga_jual_per_unit" => 500,
            ],
            [
                "kode_barang" => "BRG-007",
                "nama" => "Mie Instan (Dus 40 pcs)",
                "user_id" => 2,
                "jumlah_max" => 300,
                "jumlah_min" => 30,
                "jumlah_unit_per_kemasan" => 40,
                "harga_beli_per_unit" => 2500,
                "harga_beli_per_kemas" => 100000,
                "harga_jual_per_unit" => 3500,
            ],
            [
                "kode_barang" => "BRG-008",
                "nama" => "Rokok Premium (Pack 12 bungkus)",
                "user_id" => 2,
                "jumlah_max" => 60,
                "jumlah_min" => 5,
                "jumlah_unit_per_kemasan" => 12,
                "harga_beli_per_unit" => 24000, // per bungkus
                "harga_beli_per_kemas" => 288000,
                "harga_jual_per_unit" => 30000,
            ],
            [
                "kode_barang" => "BRG-009",
                "nama" => "Laptop Kantor",
                "user_id" => 2,
                "jumlah_max" => 20,
                "jumlah_min" => 2,
                "jumlah_unit_per_kemasan" => 1,
                "harga_beli_per_unit" => 6500000,
                "harga_beli_per_kemas" => 6500000,
                "harga_jual_per_unit" => 8200000,
            ],
            [
                "kode_barang" => "BRG-010",
                "nama" => "Printer LaserJet",
                "user_id" => 2,
                "jumlah_max" => 15,
                "jumlah_min" => 2,
                "jumlah_unit_per_kemasan" => 1,
                "harga_beli_per_unit" => 2800000,
                "harga_beli_per_kemas" => 2800000,
                "harga_jual_per_unit" => 3500000,
            ],
            [
                "kode_barang" => "BRG-011",
                "nama" => "TV LED 50 Inch",
                "user_id" => 2,
                "jumlah_max" => 10,
                "jumlah_min" => 1,
                "jumlah_unit_per_kemasan" => 1,
                "harga_beli_per_unit" => 4500000,
                "harga_beli_per_kemas" => 4500000,
                "harga_jual_per_unit" => 5500000,
            ],
            [
                "kode_barang" => "BRG-012",
                "nama" => "Mesin Cuci 9kg",
                "user_id" => 2,
                "jumlah_max" => 8,
                "jumlah_min" => 1,
                "jumlah_unit_per_kemasan" => 1,
                "harga_beli_per_unit" => 3600000,
                "harga_beli_per_kemas" => 3600000,
                "harga_jual_per_unit" => 4300000,
            ],
        ]);
    }
}
