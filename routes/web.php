<?php

use App\Http\Controllers\AdministrasiSuratController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PendapatanController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\Rapat\ManajemenRapatController;
use App\Http\Controllers\RugiLabaController;
use App\Http\Controllers\TutupBukuController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\DebiturController;
use App\Http\Controllers\AsetHutangController;
use App\Http\Controllers\Faktur\AdministrasiFakturController;
use App\Http\Controllers\JadwalPerjalananController;
use App\Http\Controllers\ManajemenKasKecilController;
use App\Http\Controllers\Memo\MemoKreditController;
use App\Http\Controllers\NeracaAkhirController;
use App\Http\Controllers\PernyataanPiutangController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReturController;
use App\Http\Controllers\SPB\SuratPengirimanBarangController;
use App\Http\Controllers\SPP\SuratPesananPembelianController;
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

        Route::delete("/pendapatan/piutang/{kode}", [
            PendapatanController::class,
            "destroyPiutang",
        ])->name("keuangan.piutang.destroy");

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

        Route::delete("/pengeluaran/hutang/{id}", [
            PengeluaranController::class,
            "destroyHutang",
        ])->name("keuangan.hutang.destroy");

        Route::delete("/keuangan/pengeluaran/pelunasan-hutang/{pengeluaran}", [
            PengeluaranController::class,
            "destroyPelunasanHutang",
        ])->name("keuangan.pengeluaran.pelunasan-hutang.destroy");

        // Kas Kecil
        Route::get("/pengeluaran-kas-kecil", [
            ManajemenKasKecilController::class,
            "index",
        ])->name("keuangan.pengeluaran-kas-kecil.index");

        Route::get("/pengeluaran-kas-kecil/create", [
            ManajemenKasKecilController::class,
            "create",
        ])->name("keuangan.pengeluaran-kas-kecil.create");

        Route::post("/pengeluaran-kas-kecil", [
            ManajemenKasKecilController::class,
            "store",
        ])->name("keuangan.pengeluaran-kas-kecil.store");

        // Kasir
        Route::get("/kasir", [KasirController::class, "index"])->name(
            "keuangan.kasir.index",
        );

        Route::get("/kasir/create", [KasirController::class, "create"])->name(
            "keuangan.kasir.create",
        );

        Route::post("/kasir", [KasirController::class, "store"])->name(
            "keuangan.kasir.store",
        );

        Route::delete("/kasir/{id}", [KasirController::class, "destroy"])->name(
            "keuangan.kasir.destroy",
        );
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
        // Route::get('/tutup-buku', [TutupBukuController::class, 'index'])->name('tutup-buku.index');
        // Route::post('/tutup-buku/proses', [TutupBukuController::class, 'proses'])->name('tutup-buku.proses');
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

        Route::get("/kas-kecil/{id}/generate", [
            AdministrasiSuratController::class,
            "pdfPermintaanKasKecil",
        ])->name("administrasi.kas-kecil.generatePdf");

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

        Route::patch("/administrasi/agenda-telpon/{id}/update-done", [
            AdministrasiSuratController::class,
            "updateIsDone",
        ])->name("administrasi.agenda-telpon.update-done");

        Route::patch("/administrasi/agenda-telpon/{id}", [
            AdministrasiSuratController::class,
            "updateAgendaTelpon",
        ])->name("administrasi.agenda-telpon.update");

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

        Route::get("/agenda-perjalanan/{id}/pdf", [
            AdministrasiSuratController::class,
            "pdfAgendaPerjalanan",
        ])->name("administrasi.agenda-perjalanan.pdf");

        Route::post("/agenda-perjalanan/", [
            AdministrasiSuratController::class,
            "storeAgendaPerjalanan",
        ])->name("administrasi.agenda-perjalanan.store");

        Route::delete("/agenda-perjalanan/{agendaId}", [
            AdministrasiSuratController::class,
            "destroyAgendaPerjalanan",
        ])->name("administrasi.agenda-perjalanan.destroy");

        // Janji Temu
        Route::get("/janji-temu/", [
            AdministrasiSuratController::class,
            "indexJanjiTemu",
        ])->name("administrasi.janji-temu.index");

        Route::get("/janji-temu/create", [
            AdministrasiSuratController::class,
            "createJanjiTemu",
        ])->name("administrasi.janji-temu.create");

        Route::get("/janji-temu/{id}", [
            AdministrasiSuratController::class,
            "showJanjiTemu",
        ])->name("administrasi.janji-temu.show");

        Route::get("/janji-temu/{id}/pdf", [
            AdministrasiSuratController::class,
            "pdfJanjiTemu",
        ])->name("administrasi.janji-temu.pdf");

        Route::post("/janji-temu/", [
            AdministrasiSuratController::class,
            "storeJanjiTemu",
        ])->name("administrasi.janji-temu.store");

        Route::delete("/janji-temu/{agendaId}", [
            AdministrasiSuratController::class,
            "destroyJanjiTemu",
        ])->name("administrasi.janji-temu.destroy");

        // Surat Undangan Rapat
        Route::get("/surat-undangan-rapat/", [
            AdministrasiSuratController::class,
            "indexSuratUndanganRapat",
        ])->name("administrasi.surat-undangan-rapat.index");

        Route::get("/surat-undangan-rapat/create", [
            AdministrasiSuratController::class,
            "createSuratUndanganRapat",
        ])->name("administrasi.surat-undangan-rapat.create");

        Route::get("/surat-undangan-rapat/{id}/pdf", [
            AdministrasiSuratController::class,
            "pdfSuratUndanganRapat",
        ])->name("administrasi.surat-undangan-rapat.pdf");

        Route::post("/surat-undangan-rapat/", [
            AdministrasiSuratController::class,
            "storeSuratUndanganRapat",
        ])->name("administrasi.surat-undangan-rapat.store");

        Route::delete("/surat-undangan-rapat/{agendaId}", [
            AdministrasiSuratController::class,
            "destroySuratUndanganRapat",
        ])->name("administrasi.surat-undangan-rapat.destroy");

        // Rapat
        Route::get("/rapat/", [ManajemenRapatController::class, "index"])->name(
            "administrasi.rapat.index",
        );

        Route::get("/rapat/create", [
            ManajemenRapatController::class,
            "create",
        ])->name("administrasi.rapat.create");

        Route::get("/rapat/edit/{rapatId}", [
            ManajemenRapatController::class,
            "edit",
        ])->name("administrasi.rapat.edit");

        Route::get("/rapat/generate/{rapatId}", [
            ManajemenRapatController::class,
            "generatePdf",
        ])->name("administrasi.rapat.generatePdf");

        Route::post("/rapat/", [
            ManajemenRapatController::class,
            "store",
        ])->name("administrasi.rapat.store");

        Route::put("/rapat/{rapatId}", [
            ManajemenRapatController::class,
            "update",
        ])->name("administrasi.rapat.update");

        Route::delete("/rapat/{rapatId}", [
            ManajemenRapatController::class,
            "destroy",
        ])->name("administrasi.rapat.destroy");

        // Hasil Keputusan Rapat
        Route::get("/rapat/hasil-keputusan", [
            ManajemenRapatController::class,
            "indexHasilKeputusan",
        ])->name("administrasi.rapat.hasil-keputusan.index");

        Route::get("/rapat/hasil-keputusan/create", [
            ManajemenRapatController::class,
            "createHasilKeputusan",
        ])->name("administrasi.rapat.hasil-keputusan.create");

        Route::get("/rapat/hasil-keputusan/{hasilKeputusanId}/generate", [
            ManajemenRapatController::class,
            "generatePdfHasilKeputusan",
        ])->name("administrasi.rapat.hasil-keputusan.generatePdf");

        Route::post("/rapat/hasil-keputusan", [
            ManajemenRapatController::class,
            "storeHasilKeputusan",
        ])->name("administrasi.rapat.hasil-keputusan.store");

        Route::delete("/rapat/hasil-keputusan/{hasilKeputusanId}", [
            ManajemenRapatController::class,
            "destroyHasilKeputusan",
        ])->name("administrasi.rapat.hasil-keputusan.destroy");

        // Surat Pesanan Pembelian (SPP/PO)
        Route::get("/surat-pesanan-pembelian", [
            SuratPesananPembelianController::class,
            "index",
        ])->name("administrasi.spp.index");

        Route::get("/surat-pesanan-pembelian/create", [
            SuratPesananPembelianController::class,
            "create",
        ])->name("administrasi.spp.create");

        Route::post("/surat-pesanan-pembelian", [
            SuratPesananPembelianController::class,
            "store",
        ])->name("administrasi.spp.store");

        Route::get("/surat-pesanan-pembelian/{sppId}/generate-pdf", [
            SuratPesananPembelianController::class,
            "generatePdf",
        ])->name("administrasi.spp.generatePdf");

        Route::delete("/surat-pesanan-pembelian/{sppId}", [
            SuratPesananPembelianController::class,
            "destroy",
        ])->name("administrasi.spp.destroy");

        // Faktur Penjualan
        Route::get("/faktur-penjualan", [
            AdministrasiFakturController::class,
            "index",
        ])->name("administrasi.faktur-penjualan.index");

        Route::get("/faktur-penjualan/create", [
            AdministrasiFakturController::class,
            "create",
        ])->name("administrasi.faktur-penjualan.create");

        Route::get("/faktur-penjualan/{rapatId}/generate", [
            AdministrasiFakturController::class,
            "generatePdf",
        ])->name("administrasi.faktur-penjualan.generatePdf");

        Route::post("/faktur-penjualan", [
            AdministrasiFakturController::class,
            "store",
        ])->name("administrasi.faktur-penjualan.store");

        Route::get("/faktur-penjualan/{fakturPenjualanId}/edit", [
            AdministrasiFakturController::class,
            "edit",
        ])->name("administrasi.faktur-penjualan.edit");

        Route::put("/faktur-penjualan/{fakturPenjualanId}", [
            AdministrasiFakturController::class,
            "update",
        ])->name("administrasi.faktur-penjualan.update");

        Route::delete("/faktur-penjualan/{fakturPenjualanId}", [
            AdministrasiFakturController::class,
            "destroy",
        ])->name("administrasi.faktur-penjualan.destroy");

        // Surat Pengiriman Barang (SPB)
        Route::get("/surat-pengiriman-barang", [
            SuratPengirimanBarangController::class,
            "index",
        ])->name("administrasi.spb.index");

        Route::get("/surat-pengiriman-barang/create", [
            SuratPengirimanBarangController::class,
            "create",
        ])->name("administrasi.spb.create");

        Route::get("/surat-pengiriman-barang/{id}/generate", [
            SuratPengirimanBarangController::class,
            "generatePdf",
        ])->name("administrasi.spb.generatePdf");

        Route::post("/surat-pengiriman-barang", [
            SuratPengirimanBarangController::class,
            "store",
        ])->name("administrasi.spb.store");

        Route::delete("/surat-pengiriman-barang/{id}", [
            SuratPengirimanBarangController::class,
            "destroy",
        ])->name("administrasi.spb.destroy");

        // Memo Kredit
        Route::get("/memo-kredit", [
            MemoKreditController::class,
            "index",
        ])->name("administrasi.memo-kredit.index");

        Route::get("/memo-kredit/create/{fakturId}", [
            MemoKreditController::class,
            "create",
        ])->name("administrasi.memo-kredit.create");

        Route::get("/memo-kredit/{fakturId}/generate", [
            MemoKreditController::class,
            "generatePdf",
        ])->name("administrasi.memo-kredit.generatePdf");

        Route::post("/memo-kredit", [
            MemoKreditController::class,
            "store",
        ])->name("administrasi.memo-kredit.store");

        Route::delete("/memo-kredit/{fakturId}", [
            MemoKreditController::class,
            "destroy",
        ])->name("administrasi.memo-kredit.destroy");

        // Pernyataan Piutang
        Route::get("/pernyataan-piutang", [
            PernyataanPiutangController::class,
            "index",
        ])->name("administrasi.pernyataan-piutang.index");

        Route::get("/pernyataan-piutang/{pelangganId}/generate", [
            PernyataanPiutangController::class,
            "generatePdf",
        ])->name("administrasi.pernyataan-piutang.generatePdf");
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
