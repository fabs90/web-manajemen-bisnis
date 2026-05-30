<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\KartuGudang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SetupAwalKeuanganSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            // 2. Buat barang sesuai daftar kamu
            $barangs = [
                ['BRG-001', 'Beras Premium 5kg', 1, 65000, 75000, 50],
                ['BRG-002', 'Gula Pasir 1kg', 1, 14000, 17000, 200],
                ['BRG-003', 'Minyak Goreng 1L', 1, 13000, 16000, 200],
                [
                    'BRG-004',
                    'Kopi Bubuk Sachet (Pack 10 pcs)',
                    10,
                    1000,
                    1500,
                    500,
                ], // 50 pack = 500 sachet
                ['BRG-005', 'Sabun Cuci Piring 750ml', 1, 10000, 14000, 200],
                ['BRG-006', 'Air Mineral Cup (Dus 48 pcs)', 48, 250, 500, 528], // 11 dus = 528 cup
                ['BRG-007', 'Mie Instan (Dus 40 pcs)', 40, 2500, 3500, 520], // 13 dus = 520 pcs
                [
                    'BRG-008',
                    'Rokok Premium (Pack 12 bungkus)',
                    12,
                    24000,
                    30000,
                    204,
                ], // 17 pack = 204 bungkus
                ['BRG-009', 'Laptop Kantor', 1, 6500000, 8200000, 5],
                ['BRG-010', 'Printer LaserJet', 1, 2800000, 3500000, 5],
                ['BRG-011', 'TV LED 50 Inch', 1, 4500000, 5500000, 5],
                ['BRG-012', 'Mesin Cuci 9kg', 1, 3600000, 4300000, 5],
            ];

            $totalPersediaan = 0;

            foreach ($barangs as $b) {
                [$kode, $nama, $unitPerKemasan, $beli, $jual, $stokAwal] = $b;

                $barang = Barang::create([
                    'kode_barang' => $kode,
                    'nama' => $nama,
                    'user_id' => 3,
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
                    'user_id' => 3,
                    'tanggal' => '2025-01-01',
                    'uraian' => 'Stok Awal Periode',
                    'diterima' => $stokAwal,
                    'dikeluarkan' => 0,
                    'saldo_persatuan' => $stokAwal,
                    'saldo_perkemasan' => ceil($stokAwal / $unitPerKemasan),
                ]);

                $totalPersediaan += $stokAwal * $beli;
            }

            // 3. Pastikan Akun tersedia (panggil DefaultAccountSeeder jika kosong)
            $accounts = \App\Models\Account::where('user_id', 3)->get()->keyBy('code');
            if ($accounts->isEmpty()) {
                \Database\Seeders\DefaultAccountSeeder::seedForUser(3);
                $accounts = \App\Models\Account::where('user_id', 3)->get()->keyBy('code');
            }

            // pelanggan: Debitur PT Sumber Makmur
            $pelangganDebitur = \App\Models\Pelanggan::where('jenis', 'debitur')->first();
            // supplier: Kreditur PT Bumi Rezeki
            $pelangganKreditur = \App\Models\Pelanggan::where('jenis', 'kreditur')->first();

            $kasAwal = 100_000_000;
            $piutangAwal = 25_000_000;
            $tanahBangunan = 150_000_000;
            $kendaraan = 50_000_000;
            $peralatan = 50_000_000;

            $hutangAwal = 15_000_000;

            $totalDebit = $kasAwal + $piutangAwal + $totalPersediaan + $tanahBangunan + $kendaraan + $peralatan;
            $modalAwal = $totalDebit - $hutangAwal; // Agar balance

            // Buat Jurnal Entry untuk Setup Awal (Saldo Awal)
            $entry = \App\Models\JournalEntry::create([
                'user_id' => 3,
                'reference_number' => 'SA-20250101-'.\Illuminate\Support\Str::random(4),
                'date' => '2025-01-01',
                'description' => 'Setup Saldo Awal',
                'transaction_type' => 'neraca_awal',
            ]);

            // Debits
            $entry->items()->create([
                'user_id' => 3, 'account_id' => $accounts['1101']->id, 'debit' => $kasAwal, 'credit' => 0,
            ]);
            if ($pelangganDebitur) {
                $entry->items()->create([
                    'user_id' => 3, 'account_id' => $accounts['1104']->id,
                    'sub_ledger_type' => \App\Models\Pelanggan::class, 'sub_ledger_id' => $pelangganDebitur->id,
                    'debit' => $piutangAwal, 'credit' => 0,
                ]);
            }
            $entry->items()->create([
                'user_id' => 3, 'account_id' => $accounts['1105']->id, 'debit' => $totalPersediaan, 'credit' => 0,
            ]);
            $entry->items()->create([
                'user_id' => 3, 'account_id' => $accounts['1201']->id, 'debit' => $peralatan, 'credit' => 0,
            ]);
            $entry->items()->create([
                'user_id' => 3, 'account_id' => $accounts['1202']->id, 'debit' => $kendaraan, 'credit' => 0,
            ]);
            $entry->items()->create([
                'user_id' => 3, 'account_id' => $accounts['1203']->id, 'debit' => $tanahBangunan, 'credit' => 0,
            ]);

            // Credits
            if ($pelangganKreditur) {
                $entry->items()->create([
                    'user_id' => 3, 'account_id' => $accounts['2101']->id,
                    'sub_ledger_type' => \App\Models\Pelanggan::class, 'sub_ledger_id' => $pelangganKreditur->id,
                    'debit' => 0, 'credit' => $hutangAwal,
                ]);
            }
            $entry->items()->create([
                'user_id' => 3, 'account_id' => $accounts['3100']->id, 'debit' => 0, 'credit' => $modalAwal,
            ]);

            // Update opening_balance di master accounts
            $accounts['1101']->update(['opening_balance' => $kasAwal]);
            $accounts['1104']->update(['opening_balance' => $piutangAwal]);
            $accounts['1105']->update(['opening_balance' => $totalPersediaan]);
            $accounts['1201']->update(['opening_balance' => $peralatan]);
            $accounts['1202']->update(['opening_balance' => $kendaraan]);
            $accounts['1203']->update(['opening_balance' => $tanahBangunan]);
            $accounts['2101']->update(['opening_balance' => $hutangAwal]);
            $accounts['3100']->update(['opening_balance' => $modalAwal]);
        });
    }
}
