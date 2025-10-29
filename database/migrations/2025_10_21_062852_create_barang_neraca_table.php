<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("barang_neraca_awal", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("neraca_awal_id")
                ->constrained("neraca_awal")
                ->onDelete("cascade");
            $table
                ->foreignId("barang_id")
                ->constrained("barang")
                ->onDelete("cascade");
            $table
                ->foreignId("user_id")
                ->constrained("users")
                ->onDelete("cascade");
            $table->timestamps();

            $table->unique(["neraca_awal_id", "barang_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("barang_neraca_awal");
    }
};
