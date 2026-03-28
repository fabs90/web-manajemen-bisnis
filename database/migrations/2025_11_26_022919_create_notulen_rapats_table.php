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
        Schema::create("agenda_rapat", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->string("nomor_surat")->nullable();
            $table->string("judul_rapat");
            $table->date("tanggal")->nullable();
            $table->string("tempat")->nullable();
            $table->time("waktu")->nullable();
            $table->string("pemimpin_rapat")->nullable();
            $table->string("nama_notulis")->nullable();
            $table->text("agenda_rapat")->nullable();
            $table->text("keputusan_rapat")->nullable();
            $table->string("nama_kota")->nullable();
            $table->date("tanggal_rapat_berikutnya")->nullable();
            $table->time("waktu_rapat_berikutnya")->nullable();
            $table->string("agenda_rapat_berikutnya")->nullable();
            $table->timestamps();
        });

        // Peserta Rapat
        Schema::create("peserta_rapat", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("agenda_rapat_id")
                ->constrained("agenda_rapat")
                ->onDelete("cascade");
            $table->string("nama");
            $table->string("jabatan")->nullable();
            $table->string("tanda_tangan")->nullable();
            $table->timestamps();
        });

        // Pembahasan Rapat
        Schema::create("rapat_detail", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("agenda_rapat_id")
                ->constrained("agenda_rapat")
                ->onDelete("cascade");
            $table->string("judul_agenda");
            $table->string("pembicara");
            $table->text("pembahasan")->nullable();
            $table->timestamps();
        });

        // Tindak Lanjut Rapat
        Schema::create("tindak_lanjut_rapat", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("agenda_rapat_id")
                ->constrained("agenda_rapat")
                ->onDelete("cascade");
            $table->text("tindakan")->nullable();
            $table->string("pelaksana")->nullable();
            $table->date("target_selesai")->nullable();
            $table->string("status")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("peserta_rapat");
        Schema::dropIfExists("keputusan_rapat");
        Schema::dropIfExists("tindak_lanjut_rapat");
        Schema::dropIfExists("rapat_detail");
        Schema::dropIfExists("agenda_rapat");
    }
};
