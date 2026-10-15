<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->string('satuan', 50)->nullable()->after('nama');
        });

        // Backfill existing records
        $nelayanUserIds = DB::table('users')->where('role', 'nelayan')->pluck('id');
        if ($nelayanUserIds->isNotEmpty()) {
            DB::table('barang')->whereIn('user_id', $nelayanUserIds)->whereNull('satuan')->update(['satuan' => 'Kg']);
        }
        DB::table('barang')->whereNull('satuan')->update(['satuan' => 'Unit']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn('satuan');
        });
    }
};
