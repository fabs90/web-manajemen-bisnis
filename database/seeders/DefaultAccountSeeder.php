<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultAccountSeeder extends Seeder
{
    /**
     * Seed akun standar untuk user tertentu.
     * Dapat dipanggil saat registrasi user baru.
     */
    public static function seedForUser(int $userId): void
    {
        $now = now();

        $accounts = [
            // ==========================================
            // 1. CURRENT ASSET (ASET LANCAR) - Kepala 11xx
            // ==========================================
            [
                'user_id' => $userId,
                'code' => '1101',
                'name' => 'Kas Utama',
                'category' => 'asset',
                'sub_category' => 'current_asset',
                'normal_balance' => 'debit',
                'requires_sub_ledger' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $userId,
                'code' => '1102',
                'name' => 'Kas Kecil',
                'category' => 'asset',
                'sub_category' => 'current_asset',
                'normal_balance' => 'debit',
                'requires_sub_ledger' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $userId,
                'code' => '1103',
                'name' => 'Bank Utama',
                'category' => 'asset',
                'sub_category' => 'current_asset',
                'normal_balance' => 'debit',
                'requires_sub_ledger' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $userId,
                'code' => '1104',
                'name' => 'Piutang Usaha',
                'category' => 'asset',
                'sub_category' => 'current_asset',
                'normal_balance' => 'debit',
                'requires_sub_ledger' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $userId,
                'code' => '1105',
                'name' => 'Persediaan Barang Dagang',
                'category' => 'asset',
                'sub_category' => 'current_asset',
                'normal_balance' => 'debit',
                'requires_sub_ledger' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==========================================
            // 2. FIXED ASSET (ASET TETAP) - Kepala 12xx
            // ==========================================
            [
                'user_id' => $userId,
                'code' => '1201',
                'name' => 'Peralatan & Meubel Kantor',
                'category' => 'asset',
                'sub_category' => 'fixed_asset',
                'normal_balance' => 'debit',
                'requires_sub_ledger' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $userId,
                'code' => '1202',
                'name' => 'Kendaraan',
                'category' => 'asset',
                'sub_category' => 'fixed_asset',
                'normal_balance' => 'debit',
                'requires_sub_ledger' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $userId,
                'code' => '1203',
                'name' => 'Tanah & Bangunan',
                'category' => 'asset',
                'sub_category' => 'fixed_asset',
                'normal_balance' => 'debit',
                'requires_sub_ledger' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==========================================
            // 3. CURRENT LIABILITY (KEWAJIBAN LANCAR) - Kepala 21xx
            // ==========================================
            [
                'user_id' => $userId,
                'code' => '2101',
                'name' => 'Utang Usaha',
                'category' => 'liability',
                'sub_category' => 'current_liability',
                'normal_balance' => 'credit',
                'requires_sub_ledger' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==========================================
            // 4. LONG TERM LIABILITY (UTANG JANGKA PANJANG) - Kepala 22xx
            // ==========================================
            [
                'user_id' => $userId,
                'code' => '2201',
                'name' => 'Utang Bank Jangka Panjang',
                'category' => 'liability',
                'sub_category' => 'long_term_liability',
                'normal_balance' => 'credit',
                'requires_sub_ledger' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==========================================
            // 5. EQUITY (MODAL) - Kepala 3xxx
            // ==========================================
            [
                'user_id' => $userId,
                'code' => '3100',
                'name' => 'Modal Pemilik',
                'category' => 'equity',
                'sub_category' => 'equity',
                'normal_balance' => 'credit',
                'requires_sub_ledger' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],


            // ==========================================
            // 6. REVENUE & EXPENSE
            // ==========================================
            [
                'user_id' => $userId,
                'code' => '4101',
                'name' => 'Pendapatan Penjualan',
                'category' => 'revenue',
                'sub_category' => null,
                'normal_balance' => 'credit',
                'requires_sub_ledger' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $userId,
                'code' => '5101',
                'name' => 'Harga Pokok Penjualan (HPP)',
                'category' => 'expense',
                'sub_category' => null,
                'normal_balance' => 'debit',
                'requires_sub_ledger' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $userId,
                'code' => '5201',
                'name' => 'Beban Gaji Karyawan',
                'category' => 'expense',
                'sub_category' => null,
                'normal_balance' => 'debit',
                'requires_sub_ledger' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $userId,
                'code' => '5202',
                'name' => 'Beban Operasional Bulanan (Listrik/Air/Internet)',
                'category' => 'expense',
                'sub_category' => null,
                'normal_balance' => 'debit',
                'requires_sub_ledger' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('accounts')->insert($accounts);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        if ($user) {
            self::seedForUser($user->id);
        }
    }
}
