<?php

namespace Database\Seeders;

use App\Models\JenisPembayaran;
use Illuminate\Database\Seeder;

class JenisPembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisPembayaran = [
            ['nama' => 'tunai', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'transfer_bank', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'qris', 'created_at' => now(), 'updated_at' => now()],
        ];

        JenisPembayaran::insert($jenisPembayaran);
    }
}
