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
        Schema::create("agenda_surat_masuk", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            // Data utama surat
            $table->string("nomor_agenda")->unique();
            $table->date("tanggal_terima");
            $table->string("nomor_surat")->nullable();
            $table->date("tanggal_surat")->nullable();
            $table->string("pengirim");
            $table->string("perihal");

            // File surat
            $table->string("file_surat")->nullable();

            // Status disposisi
            $table
                ->enum("status_disposisi", ["pending", "selesai"])
                ->default("pending");

            // Disposisi
            $table->boolean("disp_segera")->default(false);
            $table->boolean("disp_teliti")->default(false);
            $table->boolean("disp_edarkan")->default(false);
            $table->boolean("disp_diketahui")->default(false);

            $table->boolean("disp_koordinasikan")->default(false);
            $table->boolean("disp_proses_lanjut")->default(false);
            $table->boolean("disp_arsipkan")->default(false);
            $table->boolean("disp_mohon_dijawab")->default(false);

            // Catatan disposisi
            $table->text("catatan")->nullable();

            // Tujuan
            $table->boolean("tujuan_keuangan")->default(false);
            $table->boolean("tujuan_gudang")->default(false);
            $table->boolean("tujuan_karyawan")->default(false);
            $table->boolean("tujuan_lainnya")->default(false);

            // Tambahan lain
            $table->date("tanggal_disposisi")->nullable();
            $table->string("ttd_pimpinan")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("agenda_surat_masuk");
    }
};
