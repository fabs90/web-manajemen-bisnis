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
        Schema::create("agenda_janji_temu", function (Blueprint $table) {
            $table->id();
            // Header Janji Temu
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->date("tgl_membuat")->nullable();
            $table->string("nama_pembuat")->nullable();
            $table->string("perusahaan")->nullable();
            $table->string("nomor_telpon")->nullable();

            // Jadwal Janji Temu
            $table->date("tgl_janji")->nullable();
            $table->time("waktu")->nullable();
            $table->string("bertemu_dengan")->nullable();
            $table->string("tempat_pertemuan")->nullable();

            // Keperluan
            $table->text("keperluan")->nullable();

            // Status
            $table
                ->enum("status", ["terkonfirmasi", "reschedule", "dibatalkan"])
                ->default("terkonfirmasi");

            // Dicatat oleh
            $table->string("dicatat_oleh")->nullable();
            $table->date("dicatat_tgl")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("agenda_janji_temu");
    }
};
