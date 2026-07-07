<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('surat_pesanan_pembelian_detail', function (Blueprint $table) {
            $table->foreignId('barang_id')->nullable()->constrained('barang')->nullOnDelete()->after('pesanan_pembelian_id');
        });

        Schema::table('surat_pesanan_penjualan_detail', function (Blueprint $table) {
            $table->foreignId('barang_id')->nullable()->constrained('barang')->nullOnDelete()->after('pesanan_penjualan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_pesanan_pembelian_detail', function (Blueprint $table) {
            $table->dropForeign(['barang_id']);
            $table->dropColumn('barang_id');
        });

        Schema::table('surat_pesanan_penjualan_detail', function (Blueprint $table) {
            $table->dropForeign(['barang_id']);
            $table->dropColumn('barang_id');
        });
    }
};
