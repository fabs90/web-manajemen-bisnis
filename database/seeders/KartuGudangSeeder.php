<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barang;
use App\Models\KartuGudang;

class KartuGudangSeeder extends Seeder
{
    public function run()
    {
        $barangList = Barang::all();

        foreach ($barangList as $barang) {
            // stok awal otomatis berdasarkan kategori harga
            $harga = $barang->harga_beli_per_unit;

            if ($harga < 5000) {
                $stok = 500;
            } elseif ($harga < 50000) {
                $stok = 200;
            } elseif ($harga < 500000) {
                $stok = 50;
            } elseif ($harga < 2000000) {
                $stok = 20;
            } else {
                $stok = 5; // barang mahal banget
            }

            KartuGudang::create([
                "barang_id" => $barang->id,
                "tanggal" => now(),
                "diterima" => $stok,
                "dikeluarkan" => 0,
                "saldo_persatuan" => $stok,
                "saldo_perkemasan" => ceil(
                    $stok / max($barang->jumlah_unit_per_kemasan, 1),
                ),
                "uraian" => "Stok Awal Barang",
                "user_id" => $barang->user_id,
            ]);
        }
    }
}
