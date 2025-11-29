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
        Schema::create("agenda_telpon", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("user_id")
                ->constrained("users")
                ->onDelete("cascade");
            // Agenda Janji Telpon
            $table->date("tgl_panggilan")->nullable();
            $table->time("waktu_panggilan")->nullable();
            $table->string("nama_penelpon")->nullable();
            $table->string("perusahaan")->nullable();
            $table->string("nomor_telpon")->nullable();

            // Jadwal Telpon
            $table->date("jadwal_tanggal")->nullable();
            $table->time("jadwal_waktu")->nullable();
            $table->string("jadwal_dengan")->nullable();

            // Keperluan
            $table->text("keperluan")->nullable();

            // Tindak Lanjut / Status Tingkat
            $table
                ->enum("tingkat_status", [
                    "urgent",
                    "penting",
                    "normal",
                    "dijadwalkan",
                ])
                ->nullable();

            // Catatan Khusus
            $table->text("catatan_khusus")->nullable();

            // Status Akhir
            $table
                ->enum("status", [
                    "terkonfirmasi",
                    "reschedule",
                    "dibatalkan",
                    "belum",
                    "selesai",
                ])
                ->default("belum");

            $table->string("dicatat_oleh")->nullable();
            $table->date("dicatat_tgl")->nullable();
            $table->boolean("is_done")->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("agenda_telpon");
    }
};
