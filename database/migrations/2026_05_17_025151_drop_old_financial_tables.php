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
        // 1. Putus dulu foreign key di tabel operasional yang mengarah ke tabel finansial lama
        Schema::table('kartu_gudang', function (Blueprint $table) {
            if (Schema::hasColumn('kartu_gudang', 'buku_besar_pendapatan_id')) {
                $table->dropForeign(['buku_besar_pendapatan_id']);
                $table->dropColumn('buku_besar_pendapatan_id');
            }
            if (Schema::hasColumn('kartu_gudang', 'buku_besar_pengeluaran_id')) {
                $table->dropForeign(['buku_besar_pengeluaran_id']);
                $table->dropColumn('buku_besar_pengeluaran_id');
            }
        });

        Schema::table('kasir_transaction_logs', function (Blueprint $table) {
            if (Schema::hasColumn('kasir_transaction_logs', 'buku_besar_kas_id')) {
                $table->dropForeign(['buku_besar_kas_id']);
                $table->dropColumn('buku_besar_kas_id');
            }
            if (Schema::hasColumn('kasir_transaction_logs', 'pendapatan_id')) {
                $table->dropForeign(['pendapatan_id']);
                $table->dropColumn('pendapatan_id');
            }
        });

        Schema::table('pengisian_kas_kecil_logs', function (Blueprint $table) {
            if (Schema::hasColumn('pengisian_kas_kecil_logs', 'buku_besar_kas_id')) {
                $table->dropForeign(['buku_besar_kas_id']);
                $table->dropColumn('buku_besar_kas_id');
            }
        });

        // 2. Drop tabel-tabel Buku Besar yang terpecah dan saling silang
        Schema::dropIfExists('buku_besar_piutang');
        Schema::dropIfExists('buku_besar_hutang');
        Schema::dropIfExists('buku_besar_modal');
        Schema::dropIfExists('buku_besar_pengeluaran_tunai');
        Schema::dropIfExists('buku_besar_pendapatan_tunai');
        Schema::dropIfExists('buku_besar_kas');

        // 3. Drop tabel Barang Neraca Awal (pivot) dan tabel Laporan Transaksional
        Schema::dropIfExists('barang_neraca_awal');
        Schema::dropIfExists('neraca_awal');
        Schema::dropIfExists('neraca_akhir');
        Schema::dropIfExists('rugi_laba');
        Schema::dropIfExists('biaya_operasional'); // Nanti dicatat via Jurnal Umum
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Untuk method down() dikosongkan saja (atau biarkan return),
        // karena kita melakukan rekonstruksi struktur besar-besaran.
    }
};
