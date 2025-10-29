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
        Schema::create("neraca_akhir", function (Blueprint $table) {
            $table->id();
            $table->decimal("total_kas", 15, 2)->default(0);
            $table->decimal("total_piutang", 15, 2)->default(0);
            $table->decimal("total_hutang", 15, 2)->default(0);
            $table->decimal("total_persediaan", 15, 2)->default(0);
            $table->decimal("modal", 15, 2)->default(0);
            $table->decimal("laba", 15, 2)->default(0);
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("neraca_akhir");
    }
};
