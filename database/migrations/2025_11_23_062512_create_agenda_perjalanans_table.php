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
        Schema::create("agenda_perjalanan", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("user_id")
                ->constrained("users")
                ->onDelete("cascade");
            $table->string("nama_pelaksana");
            $table->string("jabatan")->nullable();
            $table->string("tujuan");
            $table->date("tanggal_mulai");
            $table->date("tanggal_selesai");
            $table->string("keperluan")->nullable();
            $table->string("disiapkan_oleh")->nullable();
            $table->date("tanggal_disiapkan")->nullable();
            $table->string("disetujui_oleh")->nullable();
            $table->date("tanggal_disetujui")->nullable();
            $table->decimal("transport")->default(0);
            $table->decimal("akomodasi")->default(0);
            $table->decimal("konsumsi")->default(0);
            $table->decimal("lain_lain")->default(0);
            $table->decimal("total_biaya")->default(0);
            $table->timestamps();
        });

        Schema::create("agenda_perjalanan_details", function (
            Blueprint $table,
        ) {
            $table->id();
            $table
                ->foreignId("user_id")
                ->constrained("users")
                ->onDelete("cascade");
            $table
                ->foreignId("agenda_perjalanan_id")
                ->constrained("agenda_perjalanan")
                ->cascadeOnDelete();
            $table->string("hari");
            $table->date("tanggal");
            $table->string("waktu")->nullable();
            $table->string("kegiatan")->nullable();
            $table->string("lokasi")->nullable();

            $table->timestamps();
        });

        Schema::create("agenda_perjalanan_transportasi", function (
            Blueprint $table,
        ) {
            $table->id();
            $table
                ->foreignId("user_id")
                ->constrained("users")
                ->onDelete("cascade");
            $table
                ->foreignId("agenda_perjalanan_id")
                ->constrained("agenda_perjalanan")
                ->cascadeOnDelete();
            $table->string("penerbangan_pergi")->nullable();
            $table->string("penerbangan_pulang")->nullable();
            $table->string("kode_booking")->nullable();
            $table->string("transportasi_lokal")->nullable();

            $table->timestamps();
        });

        Schema::create("agenda_perjalanan_akomodasi", function (
            Blueprint $table,
        ) {
            $table->id();
            $table
                ->foreignId("user_id")
                ->constrained("users")
                ->onDelete("cascade");
            $table
                ->foreignId("agenda_perjalanan_id")
                ->constrained("agenda_perjalanan")
                ->cascadeOnDelete();
            $table->string("hotel")->nullable();
            $table->string("alamat")->nullable();
            $table->string("telepon")->nullable();
            $table->date("check_in")->nullable();
            $table->date("check_out")->nullable();
            $table->string("booking_number")->nullable();
            $table->timestamps();
        });

        Schema::create("agenda_perjalanan_kontak", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("user_id")
                ->constrained("users")
                ->onDelete("cascade");
            $table
                ->foreignId("agenda_perjalanan_id")
                ->constrained("agenda_perjalanan")
                ->cascadeOnDelete();
            $table->string("nama");
            $table->string("telepon")->nullable();
            $table->string("jenis")->nullable(); // nama1, nama2, emergency
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("agenda_perjalanan");
        Schema::dropIfExists("agenda_perjalanan_details");
        Schema::dropIfExists("agenda_perjalanan_transportasi");
        Schema::dropIfExists("agenda_perjalanan_akomodasi");
        Schema::dropIfExists("agenda_perjalanan_kontak");
    }
};
