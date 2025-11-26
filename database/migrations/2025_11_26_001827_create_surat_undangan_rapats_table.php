<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("surat_undangan_rapat", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->string("nomor_surat")->nullable();
            $table->string("lampiran")->nullable();
            $table->string("perihal")->nullable();
            $table->string("nama_penerima")->nullable();
            $table->string("jabatan_penerima")->nullable();
            $table->string("kota_penerima")->nullable();
            $table->string("judul_rapat")->nullable();
            $table->date("tanggal_rapat")->nullable();
            $table->string("hari")->nullable();
            $table->time("waktu_mulai")->nullable();
            $table->time("waktu_selesai")->nullable();
            $table->string("tempat")->nullable();
            $table->string("nama_penandatangan")->nullable();
            $table->string("jabatan_penandatangan")->nullable();
            $table->json("tembusan")->nullable();
            $table->timestamps();
        });

        Schema::create("surat_undangan_rapat_detail", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->foreignId("surat_undangan_rapat_id")
                ->constrained("surat_undangan_rapat") // tetap sesuai nama table di atas
                ->onDelete("cascade");
            $table->text("agenda")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("surat_undangan_rapat_detail");
        Schema::dropIfExists("surat_undangan_rapat");
    }
};