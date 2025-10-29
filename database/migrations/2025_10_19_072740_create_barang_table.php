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
        Schema::create("barang", function (Blueprint $table) {
            $table->id();
            $table->string("kode_barang")->unique();
            $table->string("nama");
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->integer("jumlah_max")->required();
            $table->integer("jumlah_min")->required();
            $table->integer("jumlah_unit_per_kemasan")->required();
            $table->decimal("harga_beli_per_kemas", 15, 2)->required();
            $table->decimal("harga_beli_per_unit", 15, 2)->required();
            $table->decimal("harga_jual_per_unit", 15, 2)->required();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("barang");
    }
};
