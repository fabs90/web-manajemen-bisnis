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
        Schema::create("buku_besar_modal", function (Blueprint $table) {
            $table->id();
            $table->uuid("kode")->unique();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table
                ->foreignId("neraca_awal_id")
                ->nullable()
                ->constrained("neraca_awal")
                ->onDelete("cascade");
            $table
                ->foreignId("rugi_laba_id")
                ->nullable()
                ->constrained("rugi_laba")
                ->onDelete("cascade");
            $table->date("tanggal");
            $table->string("uraian")->nullable();

            $table->decimal("debit", 15, 2)->default(0); // pengambilan modal (berkurang)
            $table->decimal("kredit", 15, 2)->default(0); // penambahan modal
            $table->decimal("saldo", 15, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("buku_besar_modal");
    }
};
