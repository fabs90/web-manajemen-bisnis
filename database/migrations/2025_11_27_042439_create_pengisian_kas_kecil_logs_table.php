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
        Schema::create("pengisian_kas_kecil_logs", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("buku_besar_kas_id")
                ->constrained("buku_besar_kas")
                ->cascadeOnDelete();
            $table
                ->foreignId("kas_kecil_id")
                ->constrained("kas_kecil")
                ->cascadeOnDelete();
            $table->string("uraian");
            $table->bigInteger("jumlah");
            $table->date("tanggal_transaksi");
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("pengisian_kas_kecil_logs");
    }
};
