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
        Schema::create("agenda_surat_keluar", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->string("nomor_surat");
            $table->string("lampiran")->nullable();
            $table->string("perihal");
            $table->date("tanggal_surat");

            $table->string("nama_penerima");
            $table->string("jabatan_penerima")->nullable();
            $table->text("alamat_penerima");

            $table->text("paragraf_pembuka");
            $table->longText("paragraf_isi");
            $table->text("paragraf_penutup");

            $table->string("nama_pengirim");
            $table->string("jabatan_pengirim");

            $table->text("tembusan")->nullable();

            $table->string("ttd")->nullable(); // file tanda tangan
            $table->string("file_lampiran")->nullable(); // file lampiran

            $table->boolean("is_sent_email")->default(false);
            $table->timestamp("sent_at")->nullable();

            $table->timestamps();
        });

        Schema::create("surat_keluar_email_logs", function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId("surat_keluar_id")
                ->constrained("agenda_surat_keluar")
                ->cascadeOnDelete();

            $table->string("email");
            $table->enum("status", ["success", "failed"])->default("success");
            $table->string("message")->nullable();
            $table->timestamp("sent_at")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("agenda_surat_keluar");
        Schema::dropIfExists("surat_keluar_email_logs");
    }
};
