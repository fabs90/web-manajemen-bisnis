<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\DebiturController;
use App\Http\Controllers\KartuGudangController;
use App\Http\Controllers\ReturController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'ensureUserIsVerified', 'ensureProfileCompleted'])->group(function () {
    // =============================
    // 🚚 GROUP: Retur
    // =============================
    Route::prefix('dashboard/retur')->group(function () {
        Route::get('/penjualan', [ReturController::class, 'listPenjualan'])->name('retur.list-penjualan');
        Route::get('/pembelian', [ReturController::class, 'listPembelian'])->name('retur.list-pembelian');
    });

    // =============================
    // 💳 GROUP: DEBITUR
    // =============================
    Route::prefix('dashboard/debitur-kreditur')->group(function () {
        Route::get('/', [DebiturController::class, 'list'])->name('debitur-kreditur.list');
        Route::post('/', [DebiturController::class, 'store'])->name('debitur-kreditur.store');
        Route::delete('/{pelanggan}', [DebiturController::class, 'destroy'])->name('debitur-kreditur.destroy');
    });

    // =============================
    // 📦 GROUP: BARANG
    // =============================
    Route::prefix('dashboard/barang')->group(function () {
        // Barang utama
        Route::get('/list', [BarangController::class, 'index'])->name('barang.index');
        Route::get('/create', [BarangController::class, 'create'])->name('barang.create');
        Route::post('/', [BarangController::class, 'store'])->name('barang.store');
        Route::get('/detail/{id}', [BarangController::class, 'show'])->name('barang.show');
        Route::put('/{id}', [BarangController::class, 'update'])->name('barang.update');
        Route::delete('/{id}', [BarangController::class, 'destroy'])->name('barang.destroy');

        // Kartu Gudang
        Route::get('/kartu-gudang', [KartuGudangController::class, 'index'])->name('kartu-gudang.index');
        Route::get('/kartu-gudang/{barang_id}/detail', [KartuGudangController::class, 'detail'])->name('kartu-gudang.detail');
        Route::get('/kartu-gudang/{barang_id}', [KartuGudangController::class, 'create'])->name('kartu-gudang.create');
        Route::post('/kartu-gudang/{barang_id}', [KartuGudangController::class, 'store'])->name('kartu-gudang.store');
        Route::delete('/kartu-gudang/{id}', [KartuGudangController::class, 'destroy'])->name('kartu-gudang.destroy');
    });
});
