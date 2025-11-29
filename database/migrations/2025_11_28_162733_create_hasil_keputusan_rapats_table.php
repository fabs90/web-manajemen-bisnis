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
        Schema::create("hasil_keputusan_rapat", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("agenda_rapat_id")
                ->constrained("agenda_rapat")
                ->onDelete("cascade");
            $table->string("nomor_surat");
            $table->text("keputusan_rapat");
            $table->string("kota_tujuan");
            $table->date("tanggal_tujuan");
            $table->string("jabatan_penanggung_jawab");
            $table->string("nama_penanggung_jawab");
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("hasil_keputusan_rapat");
    }
};
