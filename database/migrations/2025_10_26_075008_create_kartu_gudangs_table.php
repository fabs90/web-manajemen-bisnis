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
        Schema::create("kartu_gudang", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("barang_id")
                ->constrained("barang")
                ->onDelete("cascade");
            $table->date("tanggal");
            $table->integer("diterima")->default(0);
            $table->integer("dikeluarkan")->default(0);
            $table->string("uraian");
            $table->integer("saldo_persatuan")->default(0);
            $table->integer("saldo_perkemasan")->default(0);
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
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("kartu_gudang");
    }
};
