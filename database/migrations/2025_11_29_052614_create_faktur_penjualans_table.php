<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("pesanan_pembelian", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("pelanggan_id")
                ->constrained("pelanggan")
                ->cascadeOnDelete();
            $table->string("nomor_pesanan_pembelian")->unique();
            $table->date("tanggal_pesanan_pembelian");
            $table->date("tanggal_kirim_pesanan_pembelian")->nullable();
            $table->string("nama_bagian_pembelian");
            $table
                ->foreignId("user_id")
                ->constrained("users")
                ->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create("pesanan_pembelian_detail", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("spp_id")
                ->constrained("pesanan_pembelian")
                ->onDelete("cascade");
            $table->string("nama_barang");
            $table->integer("kuantitas"); // jumlah dipesan
            $table->decimal("harga", 15, 2);
            $table->decimal("diskon", 15, 2)->default(0);
            $table->decimal("total", 15, 2);
            $table->timestamps();
        });

        Schema::create("surat_pengiriman_barang", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("spp_id")
                ->constrained("pesanan_pembelian")
                ->cascadeOnDelete();
            $table->string("nomor_pengiriman_barang")->unique();
            $table->string("jenis_pengiriman");
            $table->date("tanggal_terima")->nullable();
            $table->string("keadaan")->default("baik");
            $table->string("keterangan")->nullable();
            $table->string("nama_penerima");
            $table->string("nama_pengirim");

            $table
                ->foreignId("user_id")
                ->constrained("users")
                ->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create("surat_pengiriman_barang_detail", function (
            Blueprint $table,
        ) {
            $table->id();
            $table
                ->foreignId("spb_id")
                ->constrained("surat_pengiriman_barang")
                ->onDelete("cascade");
            $table
                ->foreignId("spp_detail_id")
                ->constrained("pesanan_pembelian_detail")
                ->onDelete("cascade");
            $table->integer("jumlah_dikirim");
            $table->timestamps();
        });

        Schema::create("faktur_penjualan", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("spb_id")
                ->constrained("surat_pengiriman_barang")
                ->cascadeOnDelete();
            $table->string("kode_faktur")->unique();
            $table->date("tanggal_faktur");
            $table->string("nama_bagian_penjualan");
            $table
                ->foreignId("user_id")
                ->constrained("users")
                ->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create("faktur_penjualan_detail", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("faktur_penjualan_id")
                ->constrained("faktur_penjualan")
                ->onDelete("cascade");
            $table
                ->foreignId("spb_detail_id")
                ->constrained("surat_pengiriman_barang_detail")
                ->onDelete("cascade");
            $table->decimal("harga", 15, 2)->nullable(); // copy dari PO detail
            $table->decimal("total", 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("faktur_penjualan_detail");
        Schema::dropIfExists("faktur_penjualan");
        Schema::dropIfExists("surat_pengiriman_barang_detail");
        Schema::dropIfExists("surat_pengiriman_barang");
        Schema::dropIfExists("pesanan_pembelian_detail");
        Schema::dropIfExists("pesanan_pembelian");
    }
};
