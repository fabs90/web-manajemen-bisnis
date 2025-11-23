<?php

use App\Http\Controllers\AdministrasiSuratController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PendapatanController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\RugiLabaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\DebiturController;
use App\Http\Controllers\AsetHutangController;
use App\Http\Controllers\JadwalPerjalananController;
use App\Http\Controllers\NeracaAkhirController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReturController;
use App\Http\Middleware\EnsureUserIsVerified;

// Halaman publik
Route::get("/", [PageController::class, "index"]);

Route::middleware(["web", "auth", "ensureUserIsVerified"])->group(function () {
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
    // 👨🏻‍🏫 GROUP: Profile
    // =============================
    Route::prefix("dashboard/profile")->group(function () {
        Route::get("/", [ProfileController::class, "index"])->name(
            "profile.index",
        );
        Route::get("/edit", [ProfileController::class, "edit"])->name(
            "profile.edit",
        );
        Route::put("/update", [ProfileController::class, "update"])->name(
            "profile.update",
        );
    });

    // =============================
    // 📘 GROUP: KEUANGAN
    // =============================
    Route::prefix("dashboard/keuangan")->group(function () {
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
    // 💵 GROUP: LAPORAN-KEUANGAN
    // =============================
    Route::prefix("dashboard/laporan-keuangan")->group(function () {
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
    Route::prefix("dashboard/retur")->group(function () {
        Route::get("/", [ReturController::class, "list"])->name("retur.list");

        Route::get("/create/penjualan", [
            ReturController::class,
            "create",
        ])->name("retur.create-penjualan");
        Route::post("/", [ReturController::class, "store"])->name(
            "retur.store-penjualan",
        );

        Route::get("/create/pembelian", [
            ReturController::class,
            "create_retur_pembelian",
        ])->name("retur.create-pembelian");

        Route::post("/pembelian", [
            ReturController::class,
            "store_retur_pembelian",
        ])->name("retur.store-pembelian");
    });

    // =============================
    // 💳 GROUP: DEBITUR
    // =============================
    Route::prefix("dashboard/debitur-kreditur")->group(function () {
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
    Route::prefix("dashboard/barang")->group(function () {
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
    // 👤 GROUP: PROFILE
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

    // =============================
    // 🛣️ GROUP: Administrasi Surat
    // =============================
    Route::prefix("dashboard/administrasi/surat")->group(function () {
        Route::get("/", [AdministrasiSuratController::class, "index"])->name(
            "administrasi.surat.index",
        );

        // Surat Masuk
        Route::get("/surat-masuk/create", [
            AdministrasiSuratController::class,
            "create",
        ])->name("administrasi.surat-masuk.create");

        Route::post("/surat-masuk", [
            AdministrasiSuratController::class,
            "store",
        ])->name("administrasi.surat-masuk.store");

        Route::delete("/surat-masuk/{id}", [
            AdministrasiSuratController::class,
            "destroyAgendaMasuk",
        ])->name("administrasi.surat-masuk.destroy");

        Route::get("/surat-masuk/disposisi/{id}", [
            AdministrasiSuratController::class,
            "showDisposisi",
        ])->name("administrasi.surat-masuk.disposisi.create");

        Route::post("/surat-masuk/disposisi/{id}", [
            AdministrasiSuratController::class,
            "storeDisposisi",
        ])->name("administrasi.surat-masuk.disposisi.store");

        // Surat Keluar
        Route::get("/surat-keluar/", [
            AdministrasiSuratController::class,
            "indexSuratKeluar",
        ])->name("administrasi.surat-keluar.index");

        Route::get("/surat-keluar/create", [
            AdministrasiSuratController::class,
            "createSuratKeluar",
        ])->name("administrasi.surat-keluar.create");

        Route::post("/surat-keluar/", [
            AdministrasiSuratController::class,
            "storeSuratKeluar",
        ])->name("administrasi.surat-keluar.store");

        Route::delete("/surat-keluar/{id}", [
            AdministrasiSuratController::class,
            "destroyAgendaSuratKeluar",
        ])->name("administrasi.surat-keluar.destroy");

        // Surat Kas Kecil
        Route::get("/kas-kecil/", [
            AdministrasiSuratController::class,
            "indexKasKecil",
        ])->name("administrasi.kas-kecil.index");

        Route::get("/kas-kecil/create", [
            AdministrasiSuratController::class,
            "createKasKecil",
        ])->name("administrasi.kas-kecil.create");

        Route::post("/kas-kecil/", [
            AdministrasiSuratController::class,
            "storeKasKecil",
        ])->name("administrasi.kas-kecil.store");

        Route::delete("/kas-kecil/{kasId}", [
            AdministrasiSuratController::class,
            "destroyKasKecil",
        ])->name("administrasi.kas-kecil.destroy");

        // Surat Agenda Telpon
        Route::get("/agenda-telpon/", [
            AdministrasiSuratController::class,
            "indexAgendaTelpon",
        ])->name("administrasi.agenda-telpon.index");

        Route::get("/agenda-telpon/create", [
            AdministrasiSuratController::class,
            "createAgendaTelpon",
        ])->name("administrasi.agenda-telpon.create");

        Route::get("/agenda-telpon/{id}", [
            AdministrasiSuratController::class,
            "showAgendaTelpon",
        ])->name("administrasi.agenda-telpon.show");

        Route::post("/agenda-telpon/", [
            AdministrasiSuratController::class,
            "storeAgendaTelpon",
        ])->name("administrasi.agenda-telpon.store");

        Route::delete("/agenda-telpon/{agendaId}", [
            AdministrasiSuratController::class,
            "destroyAgendaTelpon",
        ])->name("administrasi.agenda-telpon.destroy");

        // Agenda Perjalanan
        Route::get("/agenda-perjalanan/", [
            AdministrasiSuratController::class,
            "indexAgendaPerjalanan",
        ])->name("administrasi.agenda-perjalanan.index");

        Route::get("/agenda-perjalanan/create", [
            AdministrasiSuratController::class,
            "createAgendaPerjalanan",
        ])->name("administrasi.agenda-perjalanan.create");

        Route::get("/agenda-perjalanan/{id}", [
            AdministrasiSuratController::class,
            "showAgendaPerjalanan",
        ])->name("administrasi.agenda-perjalanan.show");

        Route::post("/agenda-perjalanan/", [
            AdministrasiSuratController::class,
            "storeAgendaPerjalanan",
        ])->name("administrasi.agenda-perjalanan.store");

        Route::delete("/agenda-perjalanan/{agendaId}", [
            AdministrasiSuratController::class,
            "destroyAgendaPerjalanan",
        ])->name("administrasi.agenda-perjalanan.destroy");
    });
});

Route::middleware(["web", "auth"])
    ->prefix("superadmin")
    ->group(function () {
        Route::get("/dashboard", [
            PageController::class,
            "dashboard_superadmin",
        ])->name("superadmin.dashboard");
    });

require __DIR__ . "/auth.php";
