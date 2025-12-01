<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barang;
use App\Models\KartuGudang;
use App\Models\NeracaAwal;
use Illuminate\Support\Facades\DB;

class SetupAwalKeuanganSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            // 2. Buat barang sesuai daftar kamu
            $barangs = [
                ["BRG-001", "Beras Premium 5kg", 1, 65000, 75000, 50],
                ["BRG-002", "Gula Pasir 1kg", 1, 14000, 17000, 200],
                ["BRG-003", "Minyak Goreng 1L", 1, 13000, 16000, 200],
                ["BRG-004", "Kopi Bubuk Sachet (Pack 10 pcs)", 10, 1000, 1500, 500], // 50 pack = 500 sachet
                ["BRG-005", "Sabun Cuci Piring 750ml", 1, 10000, 14000, 200],
                ["BRG-006", "Air Mineral Cup (Dus 48 pcs)", 48, 250, 500, 528], // 11 dus = 528 cup
                ["BRG-007", "Mie Instan (Dus 40 pcs)", 40, 2500, 3500, 520], // 13 dus = 520 pcs
                ["BRG-008", "Rokok Premium (Pack 12 bungkus)", 12, 24000, 30000, 204], // 17 pack = 204 bungkus
                ["BRG-009", "Laptop Kantor", 1, 6500000, 8200000, 5],
                ["BRG-010", "Printer LaserJet", 1, 2800000, 3500000, 5],
                ["BRG-011", "TV LED 50 Inch", 1, 4500000, 5500000, 5],
                ["BRG-012", "Mesin Cuci 9kg", 1, 3600000, 4300000, 5],
            ];

            $totalPersediaan = 0;

            foreach ($barangs as $b) {
                [$kode, $nama, $unitPerKemasan, $beli, $jual, $stokAwal] = $b;

                $barang = Barang::create([
                    'kode_barang' => $kode,
                    'nama' => $nama,
                    'user_id' => 1,
                    'jumlah_max' => 999,
                    'jumlah_min' => 1,
                    'jumlah_unit_per_kemasan' => $unitPerKemasan,
                    'harga_beli_per_unit' => $beli,
                    'harga_beli_per_kemas' => $beli * $unitPerKemasan,
                    'harga_jual_per_unit' => $jual,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Buat kartu gudang stok awal
                KartuGudang::create([
                    'barang_id' => $barang->id,
                    'user_id' => 1,
                    'tanggal' => '2025-01-01',
                    'uraian' => 'Stok Awal Periode',
                    'diterima' => $stokAwal,
                    'dikeluarkan' => 0,
                    'saldo_persatuan' => $stokAwal,
                    'saldo_perkemasan' => ceil($stokAwal / $unitPerKemasan),
                ]);

                $totalPersediaan += $stokAwal * $beli;
            }

            // 3. Buat Neraca Awal yang 100% sesuai dengan kartu gudang
            NeracaAwal::create([
                'user_id' => 1,
                'kas_awal' => 100_000_000,
                'total_piutang' => 0,
                'total_hutang' => 0,
                'total_persediaan' => $totalPersediaan,     // PASTI SAMA dengan kartu gudang
                'modal_awal' => 400_000_000,
                'tanah_bangunan' => 150_000_000,
                'kendaraan' => 50_000_000,
                'meubel_peralatan' => 50_000_000,
                'total_debit' => 400_000_000,
                'total_kredit' => 400_000_000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Buat entri kas awal di buku besar kas
            \App\Models\BukuBesarKas::create([
                'kode' => \Illuminate\Support\Str::uuid(),
                'user_id' => 1,
                'tanggal' => '2025-01-01',
                'uraian' => 'Modal awal disetor',
                'debit' => 100_000_000,
                'kredit' => 0,
                'saldo' => 100_000_000,
                'neraca_awal_id' => 1,
            ]);

            \App\Models\BukuBesarModal::create([
                'kode' => \Illuminate\Support\Str::uuid(),
                'user_id' => 1,
                'tanggal' => '2025-01-01',
                'uraian' => 'Modal awal disetor',
                'debit' => 0,
                'kredit' => 400_000_000,
                'saldo' => 400_000_000,
                'neraca_awal_id' => 1,
            ]);
        });
    }
}