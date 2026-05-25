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
        Schema::create('surat_pesanan_penjualan', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('pelanggan_id')
                ->nullable()
                ->constrained('pelanggan')
                ->cascadeOnDelete();
            $table->string('jenis')->nullable();
            $table->string('nomor_pesanan_penjualan')->unique();
            $table->date('tanggal_pesanan_penjualan')->nullable();
            $table->date('tanggal_kirim_pesanan_penjualan')->nullable();
            $table->string('nama_bagian_pembelian')->nullable();
            $table->string('ttd_bagian_pembelian')->nullable();
            $table
                ->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('surat_pesanan_penjualan_detail', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('pesanan_penjualan_id')
                ->constrained('surat_pesanan_penjualan')
                ->onDelete('cascade');
            $table->string('nama_barang');
            $table->integer('kuantitas'); // jumlah dipesan
            $table->decimal('harga', 15, 2);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_pesanan_penjualan_detail');
        Schema::dropIfExists('surat_pesanan_penjualan');
    }
};
