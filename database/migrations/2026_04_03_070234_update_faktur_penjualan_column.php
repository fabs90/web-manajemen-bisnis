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
        Schema::table('faktur_penjualan', function (Blueprint $table) {
            $table->date('tanggal_faktur')->nullable()->change();
        });
        Schema::dropIfExists('faktur_penjualan_detail');
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faktur_penjualan', function (Blueprint $table) {
            $table->dropColumn('tanggal_faktur');
        });
        Schema::create("faktur_penjualan_detail", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("faktur_penjualan_id")
                ->constrained("faktur_penjualan")
                ->onDelete("cascade");
            $table
                ->foreignId("spb_detail_id")
                ->constrained("surat_pengiriman_barang_detail")
                ->onDelete("cascade");
            $table->decimal("harga", 15, 2)->nullable(); // copy dari PO detail
            $table->decimal("total", 15, 2)->nullable();
            $table->timestamps();
        });
    }
};
