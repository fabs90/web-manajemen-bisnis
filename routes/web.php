<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Halaman publik
Route::get('/', [PageController::class, 'index'])->name('home');

// Dashboard utama
Route::middleware(['web', 'auth', 'ensureUserIsVerified', 'ensureProfileCompleted'])->group(function () {
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/chart-data', [PageController::class, 'chartData'])->name('dashboard.chartData');
    Route::get('/dashboard/get-started', [PageController::class, 'getStarted'])->name('dashboard.getStarted');
});

// Modular Routes
require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
require __DIR__.'/finance.php';
require __DIR__.'/inventory.php';
require __DIR__.'/administration.php';
require __DIR__.'/subscription.php';
require __DIR__.'/superadmin.php';
