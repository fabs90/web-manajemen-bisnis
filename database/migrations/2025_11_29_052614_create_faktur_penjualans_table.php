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
        Schema::create("faktur_penjualan", function (Blueprint $table) {
            $table->id();
            $table->string("kode_faktur", 50)->unique();
            $table->string("nama_pembeli");
            $table->string("alamat_pembeli");
            $table->string("nomor_pesanan")->unique()->nullable();
            $table->string("nomor_spb")->unique()->nullable();
            $table->date("tanggal");
            $table->string("jenis_pengiriman");
            $table->string("nama_bagian_penjualan");
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create("faktur_penjualan_detail", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("faktur_penjualan_id")
                ->constrained("faktur_penjualan")
                ->onDelete("cascade");
            $table->integer("jumlah_dipesan");
            $table->integer("jumlah_dikirim");
            $table->string("nama_barang");
            $table->decimal("harga", 10, 2);
            $table->decimal("diskon", 5, 2)->default(0);
            $table->decimal("total", 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("faktur_penjualan_detail");
        Schema::dropIfExists("faktur_penjualan");
    }
};
