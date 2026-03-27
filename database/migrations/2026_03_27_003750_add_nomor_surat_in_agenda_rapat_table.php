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
        Schema::table('agenda_rapat', function (Blueprint $table) {
            $table->string("nomor_surat")->nullable()->after("user_id");
            $table->renameColumn("notulis", "nama_notulis");
            $table->text("agenda_rapat")->nullable()->after("nama_notulis");
            $table->renameColumn("pimpinan_rapat", "pemimpin_rapat");
            $table->time("waktu_rapat_berikutnya")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agenda_rapat', function (Blueprint $table) {
            //
        });
    }
};
