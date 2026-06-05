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
        Schema::table('agenda_perjalanan', function (Blueprint $table) {
            $table->string('tanda_tangan_disiapkan')->nullable();
            $table->string('tanda_tangan_disetujui')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agenda_perjalanan', function (Blueprint $table) {
            $table->dropColumn('tanda_tangan_disiapkan');
            $table->dropColumn('tanda_tangan_disetujui');
        });
    }
};
