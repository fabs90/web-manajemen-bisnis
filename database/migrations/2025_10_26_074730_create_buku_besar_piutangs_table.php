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
        Schema::create("buku_besar_piutang", function (Blueprint $table) {
            $table->id();
            $table->string("kode");
            $table
                ->foreignId("pelanggan_id")
                ->constrained("pelanggan")
                ->onDelete("cascade");
            $table->string("uraian");
            $table->date("tanggal");
            $table->decimal("debit", 15, 2)->default(0);
            $table->decimal("kredit", 15, 2)->default(0);
            $table->decimal("saldo", 15, 2)->default(0);
            $table
                ->foreignId("buku_besar_pendapatan_id")
                ->nullable()
                ->constrained("buku_besar_pendapatan_tunai")
                ->onDelete("set null");

            $table
                ->foreignId("buku_besar_pengeluaran_id")
                ->nullable()
                ->constrained("buku_besar_pengeluaran_tunai")
                ->onDelete("set null");
            $table
                ->foreignId("neraca_awal_id")
                ->nullable()
                ->constrained("neraca_awal")
                ->onDelete("set null");
            $table
                ->foreignId("neraca_akhir_id")
                ->nullable()
                ->constrained("neraca_akhir")
                ->onDelete("set null");
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("buku_besar_piutang");
    }
};
