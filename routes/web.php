<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\PendapatanController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\RugiLabaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\DebiturController;
use App\Http\Controllers\AsetHutangController;
use App\Http\Controllers\NeracaAkhirController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReturController;

// Halaman publik (tidak perlu login)
Route::get("/", [PageController::class, "index"]);

// Halaman Dashboard — hanya bisa diakses setelah login
Route::middleware(["web", "auth"])->group(function () {
    Route::get("/dashboard", [PageController::class, "dashboard"])->name(
        "dashboard",
    );

    Route::get("/dashboard/chart-data", [
        PageController::class,
        "chartData",
    ])->name("dashboard.chartData");

    Route::get("/dashboard/get-started", [
        PageController::class,
        "getStarted",
    ])->name("dashboard.getStarted");

    // =============================
    // 📘 GROUP: KEUANGAN
    // =============================
    Route::prefix("keuangan")->group(function () {
        // Pendapatan
        Route::get("/pendapatan", [PendapatanController::class, "index"])->name(
            "keuangan.pendapatan.list",
        );
        Route::get("/pendapatan/create", [
            PendapatanController::class,
            "create",
        ])->name("keuangan.pendapatan.create");

        Route::post("/pendapatan", [
            PendapatanController::class,
            "store",
        ])->name("keuangan.pendapatan.store");

        Route::delete("/pendapatan/{id}", [
            PendapatanController::class,
            "destroy",
        ])->name("keuangan.pendapatan.destroy");

        Route::get("/pendapatan/lain-lain", [
            PendapatanController::class,
            "createLain",
        ])->name("keuangan.pendapatan.create_lain");

        Route::post("/pendapatan/lain-lain", [
            PendapatanController::class,
            "storeLain",
        ])->name("keuangan.pendapatan.store_lain");

        // Pengeluaran
        Route::get("/pengeluaran", [
            PengeluaranController::class,
            "list",
        ])->name("keuangan.pengeluaran.list");

        Route::get("/pengeluaran/create", [
            PengeluaranController::class,
            "create",
        ])->name("keuangan.pengeluaran.create");

        Route::post("/pengeluaran", [
            PengeluaranController::class,
            "store",
        ])->name("keuangan.pengeluaran.store");

        Route::delete("/pengeluaran/{id}", [
            PengeluaranController::class,
            "destroy",
        ])->name("keuangan.pengeluaran.destroy");
    });

    // =============================
    // 💵 GROUP: LAPORAN-KEUNGAN
    // =============================
    Route::prefix("laporan-keuangan")->group(function () {
        // Aset & Hutang
        Route::get("/aset-hutang", [
            AsetHutangController::class,
            "index",
        ])->name("laporan-keuangan.aset-hutang.index");
        Route::get("/aset-hutang/create", [
            AsetHutangController::class,
            "create",
        ])->name("laporan-keuangan.aset-hutang.create");
        Route::post("/aset-hutang", [
            AsetHutangController::class,
            "store",
        ])->name("laporan-keuangan.aset-hutang.store");
        Route::get("/aset-hutang/{id}", [
            AsetHutangController::class,
            "show",
        ])->name("laporan-keuangan.aset-hutang.show");
        Route::delete("/aset-hutang/{id}", [
            AsetHutangController::class,
            "destroy",
        ])->name("laporan-keuangan.aset-hutang.destroy");

        // Rugi Laba
        Route::get("/rugi-laba", [RugiLabaController::class, "index"])->name(
            "laporan-keuangan.rugi-laba",
        );

        Route::get("/rugi-laba/pdf-export", [
            RugiLabaController::class,
            "exportToPdf",
        ])->name("laporan-keuangan.rugi-laba.pdf");

        // Neraca Akhir
        Route::get("/neraca-akhir", [
            NeracaAkhirController::class,
            "index",
        ])->name("laporan-keuangan.neraca-akhir");
    });

    // =============================
    // 🚚 GROUP: Retur
    // =============================
    Route::prefix("retur")->group(function () {
        Route::get("/", [ReturController::class, "list"])->name("retur.list");

        Route::get("/create/pendapatan", [
            ReturController::class,
            "create",
        ])->name("retur.create");
        Route::post("/", [ReturController::class, "store"])->name(
            "retur.store",
        );

        Route::get("/create/pengeluaran", [
            ReturController::class,
            "create_pengeluaran",
        ])->name("retur.create-pengeluaran");

        Route::post("/pengeluaran", [
            ReturController::class,
            "store_pengeluaran",
        ])->name("retur.store-pengeluaran");
    });

    // =============================
    // 💳 GROUP: DEBITUR
    // =============================
    Route::prefix("debitur-kreditur")->group(function () {
        Route::get("/list", [DebiturController::class, "list"])->name(
            "debitur-kreditur.list",
        );
        Route::get("/create", [DebiturController::class, "create"])->name(
            "debitur-kreditur.create",
        );
        Route::post("/", [DebiturController::class, "store"])->name(
            "debitur-kreditur.store",
        );
    });

    // =============================
    // 📦 GROUP: BARANG
    // =============================
    Route::prefix("barang")->group(function () {
        // Barang utama
        Route::get("/list", [BarangController::class, "index"])->name(
            "barang.index",
        );
        Route::get("/create", [BarangController::class, "create"])->name(
            "barang.create",
        );
        Route::post("/", [BarangController::class, "store"])->name(
            "barang.store",
        );

        Route::get("/detail/{id}", [BarangController::class, "show"])->name(
            "barang.show",
        );

        Route::put("/{id}", [BarangController::class, "update"])->name(
            "barang.update",
        );

        Route::delete("/{id}", [BarangController::class, "destroy"])->name(
            "barang.destroy",
        );

        // Kartu Gudang
        Route::get("/kartu-gudang", [
            BarangController::class,
            "indexKartuGudang",
        ])->name("kartu-gudang.index");
        Route::get("/kartu-gudang/{barang_id}", [
            BarangController::class,
            "createKartuGudang",
        ])->name("kartu-gudang.create");
        Route::post("/kartu-gudang/{barang_id}", [
            BarangController::class,
            "storeKartuGudang",
        ])->name("kartu-gudang.store");
        Route::delete("/kartu-gudang/{id}", [
            BarangController::class,
            "deleteKartuGudang",
        ])->name("kartu-gudang.destroy");
    });

    // =============================
    // 👤 GROUP: PROFILE (Bawaan Breeze)
    // =============================
    Route::prefix("profile")->group(function () {
        Route::get("/", [ProfileController::class, "edit"])->name(
            "profile.edit",
        );
        Route::patch("/", [ProfileController::class, "update"])->name(
            "profile.update",
        );
        Route::delete("/", [ProfileController::class, "destroy"])->name(
            "profile.destroy",
        );
    });
});

require __DIR__ . "/auth.php";
