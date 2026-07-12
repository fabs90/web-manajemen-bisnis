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
            $table->double('bayar')->default(0);
            $table->double('kembalian')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kasir_transaction_logs', function (Blueprint $table) {
            $table->dropColumn('bayar');
            $table->dropColumn('kembalian');
        });
    }
};
