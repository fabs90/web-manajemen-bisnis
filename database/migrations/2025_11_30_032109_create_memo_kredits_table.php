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
        Schema::create("memo_kredit", function (Blueprint $table) {
            $table->id();
            $table->string("nomor_memo")->unique();
            $table->date("tanggal");
            $table
                ->foreignId("faktur_penjualan_id")
                ->constrained("faktur_penjualan")
                ->onDelete("cascade");
            $table->text("alasan_pengembalian"); // Barang tidak sesuai / barang rusak / dll
            $table->decimal("total")->default(0);
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->timestamps();
        });

        Schema::create("memo_kredit_detail", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("memo_kredit_id")
                ->constrained("memo_kredit")
                ->onDelete("cascade");
            $table->string("nama_barang");
            $table->integer("kuantitas");
            $table->decimal("harga_satuan", 15, 2);
            $table->decimal("jumlah", 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("memo_kredit_detail");
        Schema::dropIfExists("memo_kredit");
    }
};
