<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('agenda_janji_temu', function (Blueprint $table) {
            $table->string('jabatan_title')->nullable()->after('bertemu_dengan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agenda_janji_temu', function (Blueprint $table) {
            $table->dropColumn('jabatan_title');
        });
    }
};
