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
            $table->renameColumn("pimpinan_rapat", "pemimpin_rapat");
            $table->renameColumn("notulis", "nama_notulis");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("agenda_rapat", function (Blueprint $table) {
            $table->renameColumn("pemimpin_rapat", "pimpinan_rapat");
            $table->renameColumn("nama_notulis", "notulis");
        });
    }
};
