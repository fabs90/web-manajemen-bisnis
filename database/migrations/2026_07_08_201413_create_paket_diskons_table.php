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
        Schema::create('paket_diskons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('barang_id')->nullable()->constrained('barang')->cascadeOnDelete();
            $table->string('nama_paket');
            $table->enum('jenis_diskon', ['persentase', 'nominal']);
            $table->decimal('nilai_diskon', 15, 2);
            $table->integer('minimal_pembelian')->default(0)->comment('Minimum total transaksi / harga beli agar diskon berlaku');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_diskons');
    }
};
