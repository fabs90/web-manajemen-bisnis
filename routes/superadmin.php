<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VerifyUserController;
use App\Http\Controllers\Plan\PlanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'checkIsAdmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');

        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

        Route::prefix('verify-account')
            ->name('verify-account.')
            ->group(function () {
                Route::get('/', [VerifyUserController::class, 'index'])->name('index');
                Route::post('/{id}/verify', [VerifyUserController::class, 'verify'])->name('verify');
            });

        Route::prefix('user-management')
            ->name('user-management.')
            ->group(function () {
                Route::get('/', [UserManagementController::class, 'index'])->name('index');
                Route::get('/{id}', [UserManagementController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [UserManagementController::class, 'edit'])->name('edit');
                Route::put('/{id}', [UserManagementController::class, 'update'])->name('update');
                Route::delete('/{id}', [UserManagementController::class, 'destroy'])->name('destroy');
                Route::post('/{id}/restore-neraca-awal', [UserManagementController::class, 'restoreUserNeracaAwal'])->name('restore-neraca-awal');
            });

        Route::prefix('plan')
            ->name('plan.')
            ->group(function () {
                Route::get('/', [PlanController::class, 'index'])->name('index');
                Route::get('/create', [PlanController::class, 'create'])->name('create');
                Route::post('/', [PlanController::class, 'store'])->name('store');
                Route::get('/{plan}/edit', [PlanController::class, 'edit'])->name('edit');
                Route::put('/{plan}', [PlanController::class, 'update'])->name('update');
                Route::delete('/{plan}', [PlanController::class, 'destroy'])->name('destroy');
            });
    });
