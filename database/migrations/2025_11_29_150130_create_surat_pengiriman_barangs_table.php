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
        Schema::create("surat_pengiriman_barang", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("user_id")
                ->constrained("users")
                ->onDelete("cascade");
            $table
                ->foreignId("faktur_penjualan_id")
                ->constrained("faktur_penjualan")
                ->onDelete("cascade");
            $table->string("nomor_surat")->unique();
            $table->date("tanggal_barang_diterima")->nullable();
            $table->enum("keadaan", ["baik", "rusak"]);
            $table->string("keterangan")->nullable();
            $table->string("nama_penerima");
            $table->string("nama_pengirim");
            $table->timestamps();
            $table->unique(
                ["user_id", "nomor_surat"],
                "unique_nomor_surat_per_user",
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("surat_pengiriman_barang");
    }
};
