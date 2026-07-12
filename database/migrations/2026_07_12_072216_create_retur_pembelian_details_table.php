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
        Schema::create('retur_pembelian_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retur_pembelian_id')->constrained('retur_pembelian')->onDelete('cascade');
            $table->string('nama_barang');
            $table->integer('kuantitas');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('jumlah', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_pembelian_detail');
    }
};
