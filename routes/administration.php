<?php

use App\Http\Controllers\AdministrasiSuratController;
use App\Http\Controllers\AgendaJanjiTemuController;
use App\Http\Controllers\AgendaPerjalananController;
use App\Http\Controllers\AgendaTelponController;
use App\Http\Controllers\Faktur\AdministrasiFakturController;
use App\Http\Controllers\KasKecilController;
use App\Http\Controllers\Memo\MemoKreditController;
use App\Http\Controllers\PernyataanPiutangController;
use App\Http\Controllers\Rapat\NotulenRapatController;
use App\Http\Controllers\Rapat\SuratUndanganRapatController;
use App\Http\Controllers\SPB\SuratPengirimanBarangController;
use App\Http\Controllers\SPB\SuratPesananPelangganController;
use App\Http\Controllers\SPP\SuratPesananPembelianController;
use App\Http\Controllers\SuratKeluar\SuratKeluarController;
use App\Http\Controllers\SuratMasukController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'ensureUserIsVerified', 'ensureProfileCompleted'])->group(function () {
    // =============================
    // 🛣️ GROUP: Administrasi Surat
    // =============================
    Route::prefix('dashboard/administrasi')->group(function () {
        Route::get('/', [AdministrasiSuratController::class, 'index'])->name('administrasi.surat.index');

        // Surat Masuk
        Route::get('/surat-masuk', [SuratMasukController::class, 'index'])->name('administrasi.surat-masuk.index');
        Route::get('/surat-masuk/create', [SuratMasukController::class, 'create'])->name('administrasi.surat-masuk.create');
        Route::post('/surat-masuk', [SuratMasukController::class, 'store'])->name('administrasi.surat-masuk.store');
        Route::delete('/surat-masuk/{id}', [SuratMasukController::class, 'destroy'])->name('administrasi.surat-masuk.destroy');
        Route::get('/surat-masuk/disposisi/{id}', [SuratMasukController::class, 'showDisposisi'])->name('administrasi.surat-masuk.disposisi.create');
        Route::get('/surat-masuk/{id}/file', [SuratMasukController::class, 'viewSurat'])->name('administrasi.surat-masuk.view-file');
        Route::post('/surat-masuk/disposisi/{id}', [SuratMasukController::class, 'storeDisposisi'])->name('administrasi.surat-masuk.disposisi.store');

        // Surat Keluar
        Route::get('/surat-keluar', [SuratKeluarController::class, 'index'])->name('administrasi.surat-keluar.index');
        Route::get('/surat-keluar/create', [SuratKeluarController::class, 'create'])->name('administrasi.surat-keluar.create');
        Route::post('/surat-keluar', [SuratKeluarController::class, 'store'])->name('administrasi.surat-keluar.store');
        Route::delete('/surat-keluar/{id}', [SuratKeluarController::class, 'destroy'])->name('administrasi.surat-keluar.destroy');
        Route::get('/surat-keluar/{id}/signature', [SuratKeluarController::class, 'viewSignature'])->name('administrasi.surat-keluar.view-signature');
        Route::get('/surat-keluar/{id}/lampiran', [SuratKeluarController::class, 'viewLampiran'])->name('administrasi.surat-keluar.view-lampiran');
        Route::get('/surat-keluar/{id}', [SuratKeluarController::class, 'downloadPdf'])->name('administrasi.surat-keluar.downloadPdf');

        // Surat Kas Kecil
        Route::get('/kas-kecil/', [KasKecilController::class, 'index'])->name('administrasi.kas-kecil.index');
        Route::get('/kas-kecil/create', [KasKecilController::class, 'create'])->name('administrasi.kas-kecil.create');
        Route::get('/kas-kecil/{id}', [KasKecilController::class, 'generatePdf'])->name('administrasi.kas-kecil.generatePdf');
        Route::post('/kas-kecil/', [KasKecilController::class, 'store'])->name('administrasi.kas-kecil.store');
        Route::delete('/kas-kecil/{kasId}', [KasKecilController::class, 'destroy'])->name('administrasi.kas-kecil.destroy');

        // Surat Agenda Telpon
        Route::get('/agenda-telpon/', [AgendaTelponController::class, 'index'])->name('administrasi.agenda-telpon.index');
        Route::get('/agenda-telpon/create', [AgendaTelponController::class, 'create'])->name('administrasi.agenda-telpon.create');
        Route::get('/agenda-telpon/{id}', [AgendaTelponController::class, 'show'])->name('administrasi.agenda-telpon.show');
        Route::post('/agenda-telpon/', [AgendaTelponController::class, 'store'])->name('administrasi.agenda-telpon.store');
        Route::patch('/administrasi/agenda-telpon/{id}/update-done', [AgendaTelponController::class, 'updateIsDone'])->name('administrasi.agenda-telpon.update-done');
        Route::patch('/administrasi/agenda-telpon/{id}', [AgendaTelponController::class, 'update'])->name('administrasi.agenda-telpon.update');
        Route::delete('/agenda-telpon/{agendaId}', [AgendaTelponController::class, 'destroy'])->name('administrasi.agenda-telpon.destroy');

        // Agenda Perjalanan
        Route::get('/agenda-perjalanan/', [AgendaPerjalananController::class, 'index'])->name('administrasi.agenda-perjalanan.index');
        Route::get('/agenda-perjalanan/create', [AgendaPerjalananController::class, 'create'])->name('administrasi.agenda-perjalanan.create');
        Route::get('/agenda-perjalanan/{id}', [AgendaPerjalananController::class, 'show'])->name('administrasi.agenda-perjalanan.show');
        Route::get('/agenda-perjalanan/{id}/pdf', [AgendaPerjalananController::class, 'pdf'])->name('administrasi.agenda-perjalanan.pdf');
        Route::get('/agenda-perjalanan/{id}/pdf-surat-tugas', [AgendaPerjalananController::class, 'pdfSuratTugas'])->name('administrasi.agenda-perjalanan.pdf-surat-tugas');
        Route::post('/agenda-perjalanan/', [AgendaPerjalananController::class, 'store'])->name('administrasi.agenda-perjalanan.store');
        Route::delete('/agenda-perjalanan/{agendaId}', [AgendaPerjalananController::class, 'destroy'])->name('administrasi.agenda-perjalanan.destroy');

        // Janji Temu
        Route::get('/janji-temu/', [AgendaJanjiTemuController::class, 'index'])->name('administrasi.janji-temu.index');
        Route::get('/janji-temu/create', [AgendaJanjiTemuController::class, 'create'])->name('administrasi.janji-temu.create');
        Route::get('/janji-temu/{id}', [AgendaJanjiTemuController::class, 'show'])->name('administrasi.janji-temu.show');
        Route::get('/janji-temu/{id}/pdf', [AgendaJanjiTemuController::class, 'pdf'])->name('administrasi.janji-temu.pdf');
        Route::post('/janji-temu/', [AgendaJanjiTemuController::class, 'store'])->name('administrasi.janji-temu.store');
        Route::delete('/janji-temu/{agendaId}', [AgendaJanjiTemuController::class, 'destroy'])->name('administrasi.janji-temu.destroy');

        // Surat Undangan Rapat
        Route::get('/surat-undangan-rapat/', [SuratUndanganRapatController::class, 'index'])->name('administrasi.surat-undangan-rapat.index');
        Route::get('/surat-undangan-rapat/create', [SuratUndanganRapatController::class, 'create'])->name('administrasi.surat-undangan-rapat.create');
        Route::get('/surat-undangan-rapat/{id}/pdf', [SuratUndanganRapatController::class, 'generatePdf'])->name('administrasi.surat-undangan-rapat.pdf');
        Route::get('/surat-undangan-rapat/{id}/lampiran', [SuratUndanganRapatController::class, 'viewLampiran'])->name('administrasi.surat-undangan-rapat.view-lampiran');
        Route::post('/surat-undangan-rapat/', [SuratUndanganRapatController::class, 'store'])->name('administrasi.surat-undangan-rapat.store');
        Route::get('/surat-undangan-rapat/{id}/edit', [SuratUndanganRapatController::class, 'edit'])->name('administrasi.surat-undangan-rapat.edit');
        Route::put('/surat-undangan-rapat/{id}', [SuratUndanganRapatController::class, 'update'])->name('administrasi.surat-undangan-rapat.update');
        Route::delete('/surat-undangan-rapat/{agendaId}', [SuratUndanganRapatController::class, 'destroy'])->name('administrasi.surat-undangan-rapat.destroy');

        // Rapat
        Route::get('/rapat', [NotulenRapatController::class, 'index'])->name('administrasi.rapat.index');
        Route::get('/rapat/create', [NotulenRapatController::class, 'create'])->name('administrasi.rapat.create');
        Route::get('/rapat/edit/{rapatId}', [NotulenRapatController::class, 'edit'])->name('administrasi.rapat.edit');
        Route::get('/rapat/generate/{rapatId}', [NotulenRapatController::class, 'generatePdf'])->name('administrasi.rapat.generatePdf');
        Route::post('/rapat/', [NotulenRapatController::class, 'store'])->name('administrasi.rapat.store');
        Route::put('/rapat/{rapatId}', [NotulenRapatController::class, 'update'])->name('administrasi.rapat.update');
        Route::delete('/rapat/{rapatId}', [NotulenRapatController::class, 'destroy'])->name('administrasi.rapat.destroy');

        // Hasil Keputusan Rapat
        Route::get('/rapat/hasil-keputusan', [NotulenRapatController::class, 'indexHasilKeputusan'])->name('administrasi.rapat.hasil-keputusan.index');
        Route::get('/rapat/hasil-keputusan/{hasilKeputusanId}/generate', [NotulenRapatController::class, 'generatePdfHasilKeputusan'])->name('administrasi.rapat.hasil-keputusan.generatePdf');

        // Surat Pesanan Pembelian (SPP)
        Route::get('/surat-pesanan-pembelian', [SuratPesananPembelianController::class, 'index'])->name('administrasi.spp.index');
        Route::get('/surat-pesanan-pembelian/create', [SuratPesananPembelianController::class, 'create'])->name('administrasi.spp.create');
        Route::post('/surat-pesanan-pembelian', [SuratPesananPembelianController::class, 'store'])->name('administrasi.spp.store');
        Route::get('/surat-pesanan-pembelian/{sppId}/generate-pdf', [SuratPesananPembelianController::class, 'generatePdf'])->name('administrasi.spp.generatePdf');
        Route::delete('/surat-pesanan-pembelian/{sppId}', [SuratPesananPembelianController::class, 'destroy'])->name('administrasi.spp.destroy');

        // Faktur Penjualan
        Route::get('/faktur-penjualan', [AdministrasiFakturController::class, 'index'])->name('administrasi.faktur-penjualan.index');
        Route::get('/faktur-penjualan/create', [AdministrasiFakturController::class, 'create'])->name('administrasi.faktur-penjualan.create');
        Route::get('/faktur-penjualan/{rapatId}/generate', [AdministrasiFakturController::class, 'generatePdf'])->name('administrasi.faktur-penjualan.generatePdf');
        Route::post('/faktur-penjualan', [AdministrasiFakturController::class, 'store'])->name('administrasi.faktur-penjualan.store');
        Route::get('/faktur-penjualan/{fakturPenjualanId}/edit', [AdministrasiFakturController::class, 'edit'])->name('administrasi.faktur-penjualan.edit');
        Route::put('/faktur-penjualan/{fakturPenjualanId}', [AdministrasiFakturController::class, 'update'])->name('administrasi.faktur-penjualan.update');
        Route::delete('/faktur-penjualan/{fakturPenjualanId}', [AdministrasiFakturController::class, 'destroy'])->name('administrasi.faktur-penjualan.destroy');

        // SPP Pelanggan (dibuat dari modul SPB)
        Route::get('/surat-pengiriman-barang/pesanan-pelanggan/create', [SuratPesananPelangganController::class, 'create'])->name('administrasi.spb.spp-pelanggan.create');
        Route::post('/surat-pengiriman-barang/pesanan-pelanggan', [SuratPesananPelangganController::class, 'store'])->name('administrasi.spb.spp-pelanggan.store');
        Route::delete('/surat-pengiriman-barang/pesanan-pelanggan/{id}', [SuratPesananPelangganController::class, 'destroy'])->name('administrasi.spb.spp-pelanggan.destroy');
        Route::get('/surat-pengiriman-barang/pesanan-pelanggan/{id}/generate-pdf', [SuratPesananPelangganController::class, 'generatePdf'])->name('administrasi.spb.spp-pelanggan.generatePdf');

        // Surat Pengiriman Barang (SPB)
        Route::get('/surat-pengiriman-barang', [SuratPengirimanBarangController::class, 'index'])->name('administrasi.spb.index');
        Route::get('/surat-pengiriman-barang/create', [SuratPengirimanBarangController::class, 'create'])->name('administrasi.spb.create');
        Route::get('/surat-pengiriman-barang/{id}/edit', [SuratPengirimanBarangController::class, 'edit'])->name('administrasi.spb.edit');
        Route::get('/surat-pengiriman-barang/{id}/generate', [SuratPengirimanBarangController::class, 'generatePdf'])->name('administrasi.spb.generatePdf');
        Route::post('/surat-pengiriman-barang', [SuratPengirimanBarangController::class, 'store'])->name('administrasi.spb.store');
        Route::put('/surat-pengiriman-barang/{id}', [SuratPengirimanBarangController::class, 'update'])->name('administrasi.spb.update');
        Route::delete('/surat-pengiriman-barang/{id}', [SuratPengirimanBarangController::class, 'destroy'])->name('administrasi.spb.destroy');

        // Memo Kredit
        Route::get('/memo-kredit', [MemoKreditController::class, 'index'])->name('administrasi.memo-kredit.index');
        Route::get('/memo-kredit/pelanggan', [MemoKreditController::class, 'pelanggan'])->name('administrasi.memo-kredit.pelanggan');
        Route::get('/memo-kredit/penjual', [MemoKreditController::class, 'penjual'])->name('administrasi.memo-kredit.penjual');
        Route::get('/memo-kredit/penjual/create/{sppId}', [MemoKreditController::class, 'createPenjual'])->name('administrasi.memo-kredit.create-penjual');
        Route::post('/memo-kredit/penjual', [MemoKreditController::class, 'storePenjual'])->name('administrasi.memo-kredit.store-penjual');
        Route::delete('/memo-kredit/penjual/{returId}', [MemoKreditController::class, 'destroyPenjual'])->name('administrasi.memo-kredit.destroy-penjual');
        Route::get('/memo-kredit/penjual/{returId}/generate', [MemoKreditController::class, 'generatePdfPenjual'])->name('administrasi.memo-kredit.generatePdf-penjual');
        Route::get('/memo-kredit/create/{fakturId}', [MemoKreditController::class, 'create'])->name('administrasi.memo-kredit.create');
        Route::get('/memo-kredit/{fakturId}/generate', [MemoKreditController::class, 'generatePdf'])->name('administrasi.memo-kredit.generatePdf');
        Route::post('/memo-kredit', [MemoKreditController::class, 'store'])->name('administrasi.memo-kredit.store');
        Route::delete('/memo-kredit/{fakturId}', [MemoKreditController::class, 'destroy'])->name('administrasi.memo-kredit.destroy');

        // Pernyataan Piutang
        Route::get('/pernyataan-piutang', [PernyataanPiutangController::class, 'index'])->name('administrasi.pernyataan-piutang.index');
        Route::get('/pernyataan-piutang/{pelangganId}/generate', [PernyataanPiutangController::class, 'generatePdf'])->name('administrasi.pernyataan-piutang.generatePdf');
    });
});
