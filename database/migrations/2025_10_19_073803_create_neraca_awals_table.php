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
        Schema::create("neraca_awal", function (Blueprint $table) {
            $table->id();
            $table->decimal("kas_awal", 15, 2)->default(0);
            $table->decimal("total_piutang", 15, 2)->default(0);
            $table->decimal("total_hutang", 15, 2)->default(0);
            $table->decimal("total_persediaan", 15, 2)->default(0);
            $table->decimal("modal_awal", 15, 2)->default(0);
            $table->decimal("tanah_bangunan", 15, 2)->required();
            $table->decimal("kendaraan", 15, 2)->required();
            $table->decimal("meubel_peralatan", 15, 2)->required();
            $table->decimal("total_debit", 15, 2)->required();
            $table->decimal("total_kredit", 15, 2)->required();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("neraca_awal");
    }
};
