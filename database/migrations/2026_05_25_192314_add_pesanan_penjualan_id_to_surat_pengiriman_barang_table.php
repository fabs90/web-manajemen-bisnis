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
        // 1. Make spp_id nullable
        Schema::table('surat_pengiriman_barang', function (Blueprint $table) {
            $table->foreignId('spp_id')->nullable()->change();
        });

        // 2. Add column pesanan_penjualan_id if not exists
        if (! Schema::hasColumn('surat_pengiriman_barang', 'pesanan_penjualan_id')) {
            Schema::table('surat_pengiriman_barang', function (Blueprint $table) {
                $table->unsignedBigInteger('pesanan_penjualan_id')->after('spp_id')->nullable();
            });
        }

        // 3. Try to add constraint safely outside Schema closure
        try {
            Schema::table('surat_pengiriman_barang', function (Blueprint $table) {
                $table->foreign('pesanan_penjualan_id', 'spb_so_id_foreign')
                    ->references('id')
                    ->on('surat_pesanan_penjualan')
                    ->nullOnDelete();
            });
        } catch (Exception $e) {
        }

        // 4. Make spp_detail_id nullable
        Schema::table('surat_pengiriman_barang_detail', function (Blueprint $table) {
            $table->foreignId('spp_detail_id')->nullable()->change();
        });

        // 5. Add detail column if not exists
        if (! Schema::hasColumn('surat_pengiriman_barang_detail', 'pesanan_penjualan_detail_id')) {
            Schema::table('surat_pengiriman_barang_detail', function (Blueprint $table) {
                $table->unsignedBigInteger('pesanan_penjualan_detail_id')->after('spp_detail_id')->nullable();
            });
        }

        // 6. Try to add detail constraint safely outside Schema closure
        try {
            Schema::table('surat_pengiriman_barang_detail', function (Blueprint $table) {
                $table->foreign('pesanan_penjualan_detail_id', 'spbd_so_detail_id_foreign')
                    ->references('id')
                    ->on('surat_pesanan_penjualan_detail')
                    ->nullOnDelete();
            });
        } catch (Exception $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_pengiriman_barang_detail', function (Blueprint $table) {
            $table->dropForeign('spbd_so_detail_id_foreign');
            $table->dropColumn('pesanan_penjualan_detail_id');
            $table->foreignId('spp_detail_id')->nullable(false)->change();
        });

        Schema::table('surat_pengiriman_barang', function (Blueprint $table) {
            $table->dropForeign('spb_so_id_foreign');
            $table->dropColumn('pesanan_penjualan_id');
            $table->foreignId('spp_id')->nullable(false)->change();
        });
    }
};
