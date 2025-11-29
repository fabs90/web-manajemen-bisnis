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
        Schema::create("kasir_transaction_logs", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("pendapatan_id")
                ->constrained("buku_besar_pendapatan_tunai")
                ->cascadeOnDelete();
            $table
                ->foreignId("buku_besar_kas_id")
                ->constrained("buku_besar_kas")
                ->cascadeOnDelete();
            $table->string("uraian");
            $table->date("tanggal_transaksi");
            $table->bigInteger("jumlah");
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("kasir_transaction_logs");
    }
};
