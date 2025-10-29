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
        Schema::create("rugi_laba", function (Blueprint $table) {
            $table->id();
            $table->string("kode");
            $table->string("uraian")->nullable();
            $table->decimal("total_penjualan", 15, 2)->default(0);
            $table->decimal("hpp", 15, 2)->default(0);
            $table->decimal("biaya_operasional", 15, 2)->default(0);
            $table->decimal("laba_bersih", 15, 2)->default(0);
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("rugi_laba");
    }
};
