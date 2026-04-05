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
        Schema::table('surat_pengiriman_barang', function (Blueprint $table) {
            $table->string('nama_pengirim')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_pengiriman_barang', function (Blueprint $table) {
            $table->string('nama_pengirim')->nullable(false)->change();
        });
    }
};