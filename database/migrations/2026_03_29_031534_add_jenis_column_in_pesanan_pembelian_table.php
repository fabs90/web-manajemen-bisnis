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
        Schema::table('pesanan_pembelian', function (Blueprint $table) {
            $table->enum('jenis', ['transaksi_keluar', 'transaksi_masuk'])->nullable()->after('id');
            $table->unsignedBigInteger("supplier_id")->nullable()->after('pelanggan_id');
            $table->foreign("supplier_id")->references("id")->on("pelanggan");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan_pembelian', function (Blueprint $table) {
            $table->dropColumn("jenis");
            $table->dropColumn("supplier_id");
        });
    }
};