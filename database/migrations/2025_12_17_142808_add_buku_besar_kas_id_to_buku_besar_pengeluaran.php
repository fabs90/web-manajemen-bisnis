<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("buku_besar_pengeluaran_tunai", function (
            Blueprint $table,
        ) {
            $table
                ->foreignId("buku_besar_kas_id")
                ->nullable()
                ->after("id")
                ->constrained("buku_besar_kas")
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("buku_besar_pengeluaran", function (Blueprint $table) {
            //
        });
    }
};
