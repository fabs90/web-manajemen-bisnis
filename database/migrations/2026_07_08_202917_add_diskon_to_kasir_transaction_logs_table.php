<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kasir_transaction_logs', function (Blueprint $table) {
            $table->foreignId('paket_diskon_id')->nullable()->constrained('paket_diskons')->nullOnDelete();
            $table->decimal('diskon', 15, 2)->default(0);
        });

        // Pastikan setiap user punya akun Potongan Penjualan (4102)
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            // Update or create to ensure category and normal_balance are correct even if it exists
            \App\Models\Account::updateOrCreate([
                'user_id' => $user->id,
                'code' => '4102'
            ], [
                'name' => 'Potongan Penjualan',
                'category' => 'revenue',
                'normal_balance' => 'debit'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kasir_transaction_logs', function (Blueprint $table) {
            $table->dropForeign(['paket_diskon_id']);
            $table->dropColumn(['paket_diskon_id', 'diskon']);
        });
    }
};
