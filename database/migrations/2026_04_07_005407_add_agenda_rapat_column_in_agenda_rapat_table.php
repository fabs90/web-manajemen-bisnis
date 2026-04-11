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
        Schema::table("agenda_rapat", function (Blueprint $table) {
            $table->text("agenda_rapat")->nullable()->after("nama_notulis");
            $table
                ->time("waktu_rapat_berikutnya")
                ->nullable()
                ->after("agenda_rapat_berikutnya");
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("agenda_rapat", function (Blueprint $table) {
            $table->dropColumn("agenda_rapat");
            $table->dropColumn("waktu_rapat_berikutnya");
        });
    }
};
