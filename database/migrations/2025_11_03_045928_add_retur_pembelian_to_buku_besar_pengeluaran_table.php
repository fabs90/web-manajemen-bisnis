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
        Schema::table("buku_besar_pengeluaran_tunai", function (
            Blueprint $table,
        ) {
            if (
                !Schema::hasColumn(
                    "buku_besar_pengeluaran_tunai",
                    "jumlah_retur_pembelian",
                )
            ) {
                $table
                    ->decimal("jumlah_retur_pembelian", 15, 2)
                    ->default(0)
                    ->after("admin_bank");
            }

            if (
                !Schema::hasColumn(
                    "buku_besar_pengeluaran_tunai",
                    "jenis_retur",
                )
            ) {
                $table
                    ->enum("jenis_retur", ["kurangi_piutang", "tunai_kembali"])
                    ->nullable()
                    ->after("jumlah_retur_pembelian");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("buku_besar_pengeluaran_tunai", function (
            Blueprint $table,
        ) {
            if (
                Schema::hasColumn(
                    "buku_besar_pengeluaran_tunai",
                    "jumlah_retur_pembelian",
                )
            ) {
                $table->dropColumn("jumlah_retur_pembelian");
            }

            if (
                Schema::hasColumn("buku_besar_pengeluaran_tunai", "jenis_retur")
            ) {
                $table->dropColumn("jenis_retur");
            }
        });
    }
};
