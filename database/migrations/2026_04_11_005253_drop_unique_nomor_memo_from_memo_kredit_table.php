<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('memo_kredit', function (Blueprint $table) {
            $table->dropUnique('memo_kredit_nomor_memo_unique');
        });
    }

    public function down(): void
    {
        Schema::table('memo_kredit', function (Blueprint $table) {
            $table->unique('nomor_memo');
        });
    }
};