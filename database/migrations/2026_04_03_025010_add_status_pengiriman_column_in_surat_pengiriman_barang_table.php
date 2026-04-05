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
            $table->enum('status_pengiriman', ['diproses', 'dikirim', 'diterima', 'dibatalkan', 'dikembalikan'])->default('diproses')->after('jenis_pengiriman');
            $table->string('keadaan')->nullable()->change();
            $table->string('nama_penerima')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_pengiriman_barang', function (Blueprint $table) {
            $table->dropColumn('status_pengiriman');
            $table->string('keadaan')->change();
            $table->string('nama_penerima')->change();
        });
    }
};
