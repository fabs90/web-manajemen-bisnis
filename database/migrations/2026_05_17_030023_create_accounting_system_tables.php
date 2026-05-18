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
        // 1. MASTER TABEL AKUN (Chart of Accounts)
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->enum('category', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('sub_category', ['current_asset', 'fixed_asset', 'current_liability', 'long_term_liability', 'equity'])->nullable();
            $table->enum('normal_balance', ['debit', 'credit']);

            // Tampung saldo awal dari data lama (ex: Modal 400jt, Kas 100jt, dll) di sini
            $table->decimal('opening_balance', 15, 2)->default(0.00);

            // [TAMBAHAN] Flag Sub-Ledger (Buku Pembantu)
            // Di view, Piutang dan Hutang butuh rincian nama Debitur/Kreditur.
            // Flag ini berguna sbg penanda: "Apakah akun ini butuh rincian detail?"
            $table->boolean('requires_sub_ledger')->default(false);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'code']);
        });

        // 2. HEADER JURNAL (Slip / Nota Transaksi umum)
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reference_number');
            $table->date('date'); // Tanggal transaksi keuangan
            $table->string('description');
            // [TAMBAHAN] Untuk memfilter/melacak sumber jurnal
            $table->string('transaction_type')->nullable(); // Contoh: 'neraca_awal', 'faktur_penjualan', 'kas_kecil'
            $table->timestamps();

            $table->unique(['user_id', 'reference_number']);
        });

        // 3. DETAIL JURNAL (Buku Besar Berpasangan)
        Schema::create('journal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('journal_entry_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounts')->onDelete('restrict');
            $table->nullableMorphs('sub_ledger');
            $table->decimal('debit', 15, 2)->default(0.00);
            $table->decimal('credit', 15, 2)->default(0.00);
            $table->timestamps();
        });

        // 4. RELASI ULANG TABEL OPERASIONAL YANG TADI DI-PUTUS
        // Pasang journal_entry_id sebagai jembatan audit trail finansialnya
        Schema::table('kartu_gudang', function (Blueprint $table) {
            $table->foreignId('journal_entry_id')->nullable()->after('saldo_perkemasan')->constrained('journal_entries')->onDelete('set null');
        });

        Schema::table('kasir_transaction_logs', function (Blueprint $table) {
            $table->foreignId('journal_entry_id')->nullable()->after('id')->constrained('journal_entries')->onDelete('cascade');
        });

        Schema::table('pengisian_kas_kecil_logs', function (Blueprint $table) {
            $table->foreignId('journal_entry_id')->nullable()->after('id')->constrained('journal_entries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengisian_kas_kecil_logs', function (Blueprint $table) {
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn('journal_entry_id');
        });

        Schema::table('kasir_transaction_logs', function (Blueprint $table) {
            $table->foreignId('journal_entry_id');
            $table->dropColumn('journal_entry_id');
        });

        Schema::table('kartu_gudang', function (Blueprint $table) {
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn('journal_entry_id');
        });

        Schema::dropIfExists('journal_items');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounts');
    }
};
