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
        // ========================
        // 1. Tabel kas_kecil (header)
        // ========================
        Schema::create("kas_kecil", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->date("tanggal");
            $table->string("nomor_referensi")->nullable();
            $table->bigInteger("penerimaan")->default(0);
            $table->bigInteger("pengeluaran")->default(0);
            $table->bigInteger("saldo_akhir")->default(0);

            $table->timestamps();
        });

        // ========================
        // 2. Tabel formulir permintaan kas kecil (header tambahan/form)
        // ========================
        Schema::create("kas_kecil_formulir", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table
                ->foreignId("kas_kecil_id")
                ->constrained("kas_kecil")
                ->cascadeOnDelete();
            $table->string("nama_pemohon");
            $table->string("departemen");
            // Tanda tangan
            $table->string("ttd_nama_pemohon")->nullable();
            $table->string("nama_atasan_langsung");
            $table->string("ttd_atasan_langsung")->nullable();
            $table->string("nama_bagian_keuangan");
            $table->string("ttd_bagian_keuangan")->nullable();

            $table->timestamps();
        });

        // ========================
        // 3. Tabel kas_kecil_detail (ITEM PER BARIS)
        // ========================
        Schema::create("kas_kecil_detail", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("user_id")
                ->constrained("users")
                ->cascadeOnDelete();
            $table
                ->foreignId("kas_kecil_id")
                ->constrained("kas_kecil")
                ->cascadeOnDelete();
            $table->string("keterangan");
            $table->enum("kategori", [
                "transport",
                "bensin",
                "konsumsi",
                "atm",
                "lain",
            ]);
            $table->bigInteger("jumlah")->default(0);
        });
    }

    public function down(): void
    {
        // Hapus tabel baru
        Schema::dropIfExists("kas_kecil_formulir");
        Schema::dropIfExists("kas_kecil_detail");
        Schema::dropIfExists("kas_kecil");
    }
};
