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
        Schema::create('jenis_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::table('buku_besar_pendapatan_tunai', function (Blueprint $table) {
            $table->foreignId('jenis_pembayaran_id')->nullable()->constrained('jenis_pembayaran')->after('tanggal');
        });

        Schema::table('buku_besar_pengeluaran_tunai', function (Blueprint $table) {
            $table->foreignId('jenis_pembayaran_id')->nullable()->constrained('jenis_pembayaran')->after('tanggal');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_pembayaran');

        Schema::table('buku_besar_pendapatan_tunai', function (Blueprint $table) {
            $table->dropForeign(['jenis_pembayaran_id']);
            $table->dropColumn('jenis_pembayaran_id');
        });

        Schema::table('buku_besar_pengeluaran_tunai', function (Blueprint $table) {
            $table->dropForeign(['jenis_pembayaran_id']);
            $table->dropColumn('jenis_pembayaran_id');
        });
    }
};
