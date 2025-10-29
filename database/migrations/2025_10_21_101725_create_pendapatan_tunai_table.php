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
        Schema::create("buku_besar_pendapatan_tunai", function (
            Blueprint $table,
        ) {
            $table->id();
            $table->date("tanggal");
            $table->string("uraian");
            $table->decimal("potongan_pembelian", 10, 2)->default(0);
            $table->decimal("piutang_dagang", 10, 2);
            $table->decimal("penjualan_tunai", 10, 2);
            $table->decimal("lain_lain", 10, 2)->default(0);
            $table->decimal("uang_diterima", 10, 2);
            $table->decimal("bunga_bank", 10, 2)->default(0);
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("buku_besar_pendapatan_tunai");
    }
};
