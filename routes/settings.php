<?php

use App\Http\Controllers\PrinterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrisController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'ensureUserIsVerified'])->group(function () {
    // =============================
    // 👨🏻‍🏫 GROUP: Profile
    // =============================
    Route::prefix('dashboard/profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('profile.index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('profile.update');
    });

    // =============================
    // 🏦 GROUP: QRIS
    // =============================
    Route::prefix('dashboard/qris')->group(function () {
        Route::get('/', [QrisController::class, 'index'])->name('qris.index');
        Route::get('/image', [QrisController::class, 'showImage'])->name('qris.image');
        Route::post('/update', [QrisController::class, 'update'])->name('qris.update');
        Route::delete('/', [QrisController::class, 'destroy'])->name('qris.destroy');
    });

    // =============================
    // 🖨️ GROUP: PRINTER
    // =============================
    Route::prefix('dashboard/printer')->middleware('feature:thermal_printer')->group(function () {
        Route::get('/', [PrinterController::class, 'index'])->name('printer.index');
        Route::post('/update', [PrinterController::class, 'update'])->name('printer.update');
    });
});
