<?php

use App\Http\Controllers\KasirController;
use App\Http\Controllers\ManajemenKasKecilController;
use App\Http\Controllers\NeracaAkhirController;
use App\Http\Controllers\NeracaAwalController;
use App\Http\Controllers\PaketDiskonController;
use App\Http\Controllers\PendapatanController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\RugiLabaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'ensureUserIsVerified', 'ensureProfileCompleted'])->group(function () {
    // =============================
    // 📘 GROUP: KEUANGAN
    // =============================
    Route::prefix('dashboard/keuangan')->group(function () {
        // Pendapatan
        Route::get('/pendapatan', [PendapatanController::class, 'index'])->name('keuangan.pendapatan.list');
        Route::get('/pendapatan/create', [PendapatanController::class, 'create'])->name('keuangan.pendapatan.create');
        Route::get('/pendapatan/{id}/detail', [PendapatanController::class, 'show'])->name('keuangan.pendapatan.show');
        Route::post('/pendapatan', [PendapatanController::class, 'store'])->name('keuangan.pendapatan.store');
        Route::delete('/pendapatan/{id}', [PendapatanController::class, 'destroy'])->name('keuangan.pendapatan.destroy');
        Route::delete('/pendapatan/piutang/{kode}', [PendapatanController::class, 'destroyPiutang'])->name('keuangan.piutang.destroy');
        Route::get('/pendapatan/lain-lain', [PendapatanController::class, 'createLain'])->name('keuangan.pendapatan.create_lain');
        Route::post('/pendapatan/lain-lain', [PendapatanController::class, 'storeLain'])->name('keuangan.pendapatan.store_lain');

        // Pengeluaran
        Route::get('/pengeluaran', [PengeluaranController::class, 'list'])->name('keuangan.pengeluaran.list');
        Route::get('/pengeluaran/create', [PengeluaranController::class, 'create'])->name('keuangan.pengeluaran.create');
        Route::get('/pengeluaran/{id}/detail', [PengeluaranController::class, 'show'])->name('keuangan.pengeluaran.show');
        Route::post('/pengeluaran', [PengeluaranController::class, 'store'])->name('keuangan.pengeluaran.store');
        Route::delete('/pengeluaran/{id}', [PengeluaranController::class, 'destroy'])->name('keuangan.pengeluaran.destroy');
        Route::delete('/pengeluaran/hutang/{id}', [PengeluaranController::class, 'destroyHutang'])->name('keuangan.hutang.destroy');
        Route::delete('/keuangan/pengeluaran/pelunasan-hutang/{pengeluaran}', [PengeluaranController::class, 'destroyPelunasanHutang'])->name('keuangan.pengeluaran.pelunasan-hutang.destroy');

        // Kas Kecil (Form Pengeluaran)
        Route::get('/pengeluaran-kas-kecil', [ManajemenKasKecilController::class, 'index'])->name('keuangan.pengeluaran-kas-kecil.index');
        Route::get('/pengeluaran-kas-kecil/create', [ManajemenKasKecilController::class, 'create'])->name('keuangan.pengeluaran-kas-kecil.create');
        Route::post('/pengeluaran-kas-kecil', [ManajemenKasKecilController::class, 'store'])->name('keuangan.pengeluaran-kas-kecil.store');

        // Paket Diskon
        Route::resource('/paket-diskon', PaketDiskonController::class)
            ->names('keuangan.paket-diskon')
            ->except(['show']);

        // Kasir
        Route::get('/kasir', [KasirController::class, 'index'])->name('keuangan.kasir.index');
        Route::get('/kasir/create', [KasirController::class, 'create'])->name('keuangan.kasir.create');
        Route::post('/kasir', [KasirController::class, 'store'])->name('keuangan.kasir.store');
        Route::delete('/kasir/{id}', [KasirController::class, 'destroy'])->name('keuangan.kasir.destroy');
    });

    // =============================
    // 💵 GROUP: LAPORAN-KEUANGAN
    // =============================
    Route::prefix('dashboard/laporan-keuangan')->group(function () {
        // Neraca Awal
        Route::get('/neraca-awal', [NeracaAwalController::class, 'index'])->name('laporan-keuangan.neraca-awal.index');
        Route::get('/neraca-awal/create', [NeracaAwalController::class, 'create'])->name('laporan-keuangan.neraca-awal.create');
        Route::post('/neraca-awal', [NeracaAwalController::class, 'store'])->name('laporan-keuangan.neraca-awal.store');
        Route::get('/neraca-awal/{id}', [NeracaAwalController::class, 'show'])->name('laporan-keuangan.neraca-awal.show');
        Route::delete('/neraca-awal/{id}', [NeracaAwalController::class, 'destroy'])->name('laporan-keuangan.neraca-awal.destroy');

        // Rugi Laba
        Route::get('/rugi-laba', [RugiLabaController::class, 'index'])->name('laporan-keuangan.rugi-laba');
        Route::get('/rugi-laba/pdf-export', [RugiLabaController::class, 'exportToPdf'])->name('laporan-keuangan.rugi-laba.pdf');

        // Neraca Akhir
        Route::get('/neraca-akhir', [NeracaAkhirController::class, 'index'])->name('laporan-keuangan.neraca-akhir');
    });
});
