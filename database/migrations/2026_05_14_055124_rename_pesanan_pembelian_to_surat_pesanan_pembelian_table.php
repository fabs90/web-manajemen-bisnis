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
        Schema::table('pesanan_pembelian', function (Blueprint $table) {
            $table->rename('surat_pesanan_pembelian');
        });

        Schema::table('pesanan_pembelian_detail', function (Blueprint $table) {
            $table->rename('surat_pesanan_pembelian_detail');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_pesanan_pembelian', function (Blueprint $table) {
            $table->rename('pesanan_pembelian');
        });

        Schema::table('surat_pesanan_pembelian_detail', function (Blueprint $table) {
            $table->rename('pesanan_pembelian_detail');
        });
    }
};
