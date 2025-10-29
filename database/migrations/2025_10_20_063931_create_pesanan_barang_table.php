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
        Schema::create("pesanan_barang", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("barang_id")
                ->constrained("barang")
                ->onDelete("cascade");
            $table->foreignId("user_id")->constrained()->onDelete("cascade");

            $table->integer("stok_sekarang")->default(0);
            $table->integer("jumlah_pesanan")->default(0);
            $table
                ->enum("status", ["belum dipesan", "sudah dipesan"])
                ->default("belum dipesan");
            $table->date("tanggal_pesan")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("pesanan_barang");
    }
};
