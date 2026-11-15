<?php

use App\Models\Account;
use App\Models\Barang;
use App\Models\JenisPembayaran;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\KartuGudang;
use App\Models\KasirTransactionLog;
use App\Models\User;
use App\Services\KeuanganService;
use Database\Seeders\DefaultAccountSeeder;

beforeEach(function () {
    $this->nelayan = User::factory()->create([
        'email' => 'nelayan_test@test.com',
        'role' => 'nelayan',
        'alamat' => 'Dermaga Muara Baru No. 12',
        'nomor_telepon' => '081298765432',
        'is_verified' => 1,
    ]);
});

test('nelayan index displays fishing catch specific view', function () {
    Barang::create([
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-001',
        'nama' => 'Ikan Cakalang',
        'jumlah_max' => 0,
        'jumlah_min' => 0,
        'jumlah_unit_per_kemasan' => 1,
        'harga_beli_per_kemas' => 0,
        'harga_beli_per_unit' => 0,
        'harga_jual_per_unit' => 35000,
    ]);

    $response = $this->actingAs($this->nelayan)->get(route('barang.index'));

    $response->assertSuccessful();
    $response->assertSee('Daftar Tangkapan Nelayan');
    $response->assertSee('Ikan Cakalang');
    $response->assertSee('Kilogram (Kg)');
    $response->assertSee('35.000');
    // Pastikan tidak ada kolom per-kemasan yang spesifik untuk non-nelayan
    $response->assertDontSee('Jumlah Min (Per-kemasan)');
});

test('nelayan create view displays tailored form without min max and buy price', function () {
    $response = $this->actingAs($this->nelayan)->get(route('barang.create'));

    $response->assertSuccessful();
    $response->assertSee('Form Tambah Hasil Tangkapan');
    $response->assertSee('Harga Jual per-Kg');
    $response->assertDontSee('Jumlah Stok Maksimum per-Kemasan');
    $response->assertDontSee('Harga Beli per-Kemas');
});

test('nelayan can store catch without min max or buy price', function () {
    $response = $this->actingAs($this->nelayan)->post(route('barang.store'), [
        'kode_barang' => 'IKN-002',
        'nama' => 'Udang Windu',
        'harga_jual_per_unit' => 85000,
    ]);

    $response->assertRedirect(route('barang.create'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('barang', [
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-002',
        'nama' => 'Udang Windu',
        'harga_jual_per_unit' => 85000,
        'jumlah_max' => 0,
        'jumlah_min' => 0,
        'jumlah_unit_per_kemasan' => 1,
        'harga_beli_per_kemas' => 0,
        'harga_beli_per_unit' => 0,
    ]);
});

test('nelayan edit view and update work properly with per-kg format', function () {
    $barang = Barang::create([
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-003',
        'nama' => 'Kepiting Bakau',
        'jumlah_max' => 0,
        'jumlah_min' => 0,
        'jumlah_unit_per_kemasan' => 1,
        'harga_beli_per_kemas' => 0,
        'harga_beli_per_unit' => 0,
        'harga_jual_per_unit' => 90000,
    ]);

    $editResponse = $this->actingAs($this->nelayan)->get(route('barang.show', $barang->id));
    $editResponse->assertSuccessful();
    $editResponse->assertSee('Form Edit Hasil Tangkapan');
    $editResponse->assertSee('Harga Jual per-Kg');
    $editResponse->assertDontSee('Harga Beli per-Kemas');

    $updateResponse = $this->actingAs($this->nelayan)->put(route('barang.update', $barang->id), [
        'kode_barang' => 'IKN-003',
        'nama' => 'Kepiting Bakau Super',
        'harga_jual_per_unit' => 110000,
    ]);

    $updateResponse->assertRedirect(route('barang.show', $barang->id));
    $updateResponse->assertSessionHas('success');

    $this->assertDatabaseHas('barang', [
        'id' => $barang->id,
        'nama' => 'Kepiting Bakau Super',
        'harga_jual_per_unit' => 110000,
    ]);
});

test('nelayan kartu gudang detail displays per-kilogram format and fish specific labels', function () {
    $barang = Barang::create([
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-004',
        'nama' => 'Ikan Tongkol',
        'jumlah_max' => 0,
        'jumlah_min' => 0,
        'jumlah_unit_per_kemasan' => 1,
        'harga_beli_per_kemas' => 0,
        'harga_beli_per_unit' => 0,
        'harga_jual_per_unit' => 25000,
    ]);

    KartuGudang::create([
        'user_id' => $this->nelayan->id,
        'barang_id' => $barang->id,
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Tangkapan Pagi',
        'diterima' => 50,
        'dikeluarkan' => 0,
        'saldo_persatuan' => 50,
        'saldo_perkemasan' => 50,
    ]);

    $response = $this->actingAs($this->nelayan)->get(route('kartu-gudang.detail', $barang->id));

    $response->assertSuccessful();
    $response->assertSee('Ikan Tongkol');
    $response->assertSee('Per-kilogram (Kg)');
    $response->assertSee('1 unit = 1 kg');
    $response->assertSee('Diterima (Kg)');
    $response->assertSee('Dikeluarkan (Kg)');
    $response->assertSee('Stok (Kg)');
    $response->assertSee('Tambah Stok Ikan');
    $response->assertDontSee('Saldo Akhir Kemasan');
});

test('nelayan kartu gudang create displays per-kilogram format and fish specific form', function () {
    $barang = Barang::create([
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-005',
        'nama' => 'Ikan Tenggiri',
        'satuan' => 'Kg',
        'jumlah_max' => 0,
        'jumlah_min' => 0,
        'jumlah_unit_per_kemasan' => 1,
        'harga_beli_per_kemas' => 0,
        'harga_beli_per_unit' => 0,
        'harga_jual_per_unit' => 60000,
    ]);

    $response = $this->actingAs($this->nelayan)->get(route('kartu-gudang.create', $barang->id));

    $response->assertSuccessful();
    $response->assertSee('Atur Stok Ikan');
    $response->assertSee('Ikan Tenggiri');
    $response->assertSee('Informasi Hasil Tangkapan');
    $response->assertSee('1 Unit = 1 Kg');
    $response->assertSee('Ikan Masuk (Per-kg)');
    $response->assertSee('Ikan Keluar (Per-kg)');
    $response->assertDontSee('Stok Kemasan');
});

test('nelayan can store and view fish with Ember and Ekor packaging', function () {
    // Store Ember package
    $this->actingAs($this->nelayan)->post(route('barang.store'), [
        'kode_barang' => 'IKN-EMB-01',
        'nama' => 'Ikan Teri Segar',
        'satuan' => 'Ember',
        'harga_jual_per_unit' => 150000,
    ])->assertRedirect(route('barang.create'));

    $this->assertDatabaseHas('barang', [
        'kode_barang' => 'IKN-EMB-01',
        'satuan' => 'Ember',
        'harga_jual_per_unit' => 150000,
    ]);

    // Store Ekor package
    $this->actingAs($this->nelayan)->post(route('barang.store'), [
        'kode_barang' => 'IKN-EKR-01',
        'nama' => 'Ikan Kerapu Cantik',
        'satuan' => 'Ekor',
        'harga_jual_per_unit' => 200000,
    ])->assertRedirect(route('barang.create'));

    $this->assertDatabaseHas('barang', [
        'kode_barang' => 'IKN-EKR-01',
        'satuan' => 'Ekor',
        'harga_jual_per_unit' => 200000,
    ]);

    // View index
    $indexResponse = $this->actingAs($this->nelayan)->get(route('barang.index'));
    $indexResponse->assertSuccessful();
    $indexResponse->assertSee('Ember');
    $indexResponse->assertSee('Ekor');
});

test('nelayan persediaan calculation calculates value using harga_jual_per_unit across packages', function () {
    $ikanEmber = Barang::create([
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-010',
        'nama' => 'Ikan Selar (Ember)',
        'satuan' => 'Ember',
        'harga_jual_per_unit' => 100000,
        'harga_beli_per_unit' => 0,
    ]);

    $ikanKg = Barang::create([
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-011',
        'nama' => 'Ikan Tuna (Kg)',
        'satuan' => 'Kg',
        'harga_jual_per_unit' => 50000,
        'harga_beli_per_unit' => 0,
    ]);

    KartuGudang::create([
        'user_id' => $this->nelayan->id,
        'barang_id' => $ikanEmber->id,
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Tangkapan 5 Ember',
        'diterima' => 5,
        'dikeluarkan' => 0,
        'saldo_persatuan' => 5,
        'saldo_perkemasan' => 5,
    ]);

    KartuGudang::create([
        'user_id' => $this->nelayan->id,
        'barang_id' => $ikanKg->id,
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Tangkapan 10 Kg',
        'diterima' => 10,
        'dikeluarkan' => 0,
        'saldo_persatuan' => 10,
        'saldo_perkemasan' => 10,
    ]);

    // Test nilaiPersediaan method: 5 * 100.000 = 500.000, and 10 * 50.000 = 500.000
    expect($ikanEmber->nilaiPersediaan('nelayan'))->toBe(500000.0);
    expect($ikanKg->nilaiPersediaan('nelayan'))->toBe(500000.0);

    // Test KeuanganService inventory metrics
    $keuanganService = app(KeuanganService::class);
    $labaRugi = $keuanganService->hitungLabaRugi(now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d'), $this->nelayan->id);

    // Total persediaan akhir = 500.000 + 500.000 = 1.000.000
    expect($labaRugi['persediaanAkhir'])->toBe(1000000.0);
});

test('kartu gudang index renders separate tailored views for nelayan and ukm', function () {
    // Nelayan view check
    $nelayanResponse = $this->actingAs($this->nelayan)->get(route('kartu-gudang.index'));
    $nelayanResponse->assertSuccessful();
    $nelayanResponse->assertViewIs('kartu-gudang.nelayan.index');
    $nelayanResponse->assertSee('Ringkasan Stok Ikan');
    $nelayanResponse->assertSee('Tambah Hasil Tangkapan');
    $nelayanResponse->assertDontSee('Saldo Kemasan');

    // UKM view check
    $ukm = User::factory()->create([
        'email' => 'ukm_kartu@test.com',
        'role' => 'ukm',
        'alamat' => 'Jl. Sudirman No. 1',
        'nomor_telepon' => '081234567890',
        'is_verified' => 1,
    ]);

    $ukmResponse = $this->actingAs($ukm)->get(route('kartu-gudang.index'));
    $ukmResponse->assertSuccessful();
    $ukmResponse->assertViewIs('kartu-gudang.index');
    $ukmResponse->assertSee('Ringkasan Stok Gudang');
    $ukmResponse->assertSee('Tambah Master Barang');
    $ukmResponse->assertSee('Saldo Kemasan');
});

test('kode_barang must be unique within the same user but allowed across different users', function () {
    Barang::create([
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-UNIQ-01',
        'nama' => 'Ikan Pertama',
        'satuan' => 'Kg',
        'harga_jual_per_unit' => 20000,
    ]);

    // Same user tries to store duplicate kode_barang -> Should fail
    $duplicateResponse = $this->actingAs($this->nelayan)->post(route('barang.store'), [
        'kode_barang' => 'IKN-UNIQ-01',
        'nama' => 'Ikan Kedua',
        'satuan' => 'Kg',
        'harga_jual_per_unit' => 25000,
    ]);
    $duplicateResponse->assertSessionHasErrors('kode_barang');

    // Different user tries to use same kode_barang -> Should succeed
    $otherNelayan = User::factory()->create([
        'email' => 'other_nelayan@test.com',
        'role' => 'nelayan',
        'alamat' => 'Dermaga Barat No. 5',
        'nomor_telepon' => '089999999999',
        'is_verified' => 1,
    ]);

    $otherResponse = $this->actingAs($otherNelayan)->post(route('barang.store'), [
        'kode_barang' => 'IKN-UNIQ-01',
        'nama' => 'Ikan Milik Nelayan Lain',
        'satuan' => 'Kg',
        'harga_jual_per_unit' => 30000,
    ]);
    $otherResponse->assertSessionHasNoErrors();
    $otherResponse->assertRedirect(route('barang.create'));
});

test('kode_barang update allows keeping current code but forbids duplicate with other item of same user', function () {
    $item1 = Barang::create([
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-UPD-01',
        'nama' => 'Item Satu',
        'satuan' => 'Kg',
        'harga_jual_per_unit' => 15000,
    ]);

    $item2 = Barang::create([
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-UPD-02',
        'nama' => 'Item Dua',
        'satuan' => 'Kg',
        'harga_jual_per_unit' => 25000,
    ]);

    // Updating item1 with its own kode_barang -> Should succeed
    $selfUpdateResponse = $this->actingAs($this->nelayan)->put(route('barang.update', $item1->id), [
        'kode_barang' => 'IKN-UPD-01',
        'nama' => 'Item Satu Diubah',
        'satuan' => 'Kg',
        'harga_jual_per_unit' => 18000,
    ]);
    $selfUpdateResponse->assertSessionHasNoErrors();

    // Updating item1 with item2's kode_barang -> Should fail
    $conflictResponse = $this->actingAs($this->nelayan)->put(route('barang.update', $item1->id), [
        'kode_barang' => 'IKN-UPD-02',
        'nama' => 'Item Satu Gagal',
        'satuan' => 'Kg',
        'harga_jual_per_unit' => 18000,
    ]);
    $conflictResponse->assertSessionHasErrors('kode_barang');
});

test('nelayan laba rugi calculation sets HPP and purchases to 0 with gross profit equal to net sales', function () {
    DefaultAccountSeeder::seedForUser($this->nelayan->id);

    $ikan = Barang::create([
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-PROFIT-01',
        'nama' => 'Ikan Cakalang',
        'satuan' => 'Kg',
        'harga_jual_per_unit' => 40000,
        'harga_beli_per_unit' => 0,
    ]);

    KartuGudang::create([
        'user_id' => $this->nelayan->id,
        'barang_id' => $ikan->id,
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Tangkapan 50 Kg',
        'diterima' => 50,
        'dikeluarkan' => 0,
        'saldo_persatuan' => 50,
        'saldo_perkemasan' => 50,
    ]);

    // Penjualan tunai/hasil tangkapan Rp 1.000.000
    $kasAccount = Account::where('user_id', $this->nelayan->id)->where('code', '1101')->first();
    $pendapatanAccount = Account::where('user_id', $this->nelayan->id)->where('code', '4101')->first();
    $biayaAccount = Account::where('user_id', $this->nelayan->id)->where('code', '5201')->first();

    $penjualanEntry = JournalEntry::create([
        'user_id' => $this->nelayan->id,
        'reference_number' => 'PJ-TEST-001',
        'date' => now()->format('Y-m-d'),
        'description' => 'Penjualan Tangkapan Ikan',
        'transaction_type' => 'penjualan',
    ]);

    $penjualanEntry->items()->create([
        'user_id' => $this->nelayan->id,
        'account_id' => $kasAccount->id,
        'debit' => 1000000,
        'credit' => 0,
    ]);
    $penjualanEntry->items()->create([
        'user_id' => $this->nelayan->id,
        'account_id' => $pendapatanAccount->id,
        'debit' => 0,
        'credit' => 1000000,
    ]);

    // Operasional expense (Solar kapal) Rp 200.000
    $operasionalEntry = JournalEntry::create([
        'user_id' => $this->nelayan->id,
        'reference_number' => 'BIAYA-TEST-001',
        'date' => now()->format('Y-m-d'),
        'description' => 'Bahan Bakar Kapal',
        'transaction_type' => 'pengeluaran',
    ]);
    $operasionalEntry->items()->create([
        'user_id' => $this->nelayan->id,
        'account_id' => $biayaAccount->id,
        'debit' => 200000,
        'credit' => 0,
    ]);
    $operasionalEntry->items()->create([
        'user_id' => $this->nelayan->id,
        'account_id' => $kasAccount->id,
        'debit' => 0,
        'credit' => 200000,
    ]);

    $keuangan = app(KeuanganService::class);
    $labaRugi = $keuangan->hitungLabaRugi(null, null, $this->nelayan->id);

    // Nelayan catch has no buy price: HPP must be 0, labaKotor == penjualanBersih
    expect($labaRugi['penjualanBersih'])->toBe(1000000.0)
        ->and($labaRugi['pembelianBersih'])->toBe(0.0)
        ->and($labaRugi['hpp'])->toBe(0.0)
        ->and($labaRugi['labaKotor'])->toBe(1000000.0)
        ->and($labaRugi['biayaOperasional'])->toBe(200000.0)
        ->and($labaRugi['labaOperasional'])->toBe(800000.0)
        ->and($labaRugi['labaSebelumPajak'])->toBe(800000.0)
        ->and($labaRugi['pajak'])->toBe(0.0)
        ->and($labaRugi['labaSetelahPajak'])->toBe(800000.0);

    // Test Rugi Laba view as nelayan
    $response = $this->actingAs($this->nelayan)->get(route('laporan-keuangan.rugi-laba'));
    $response->assertSuccessful();
    $response->assertSee('Persediaan Hasil Tangkapan Awal');
    $response->assertSee('Persediaan Hasil Tangkapan Akhir');

    // Test Neraca Akhir view as nelayan
    $neracaResponse = $this->actingAs($this->nelayan)->get(route('laporan-keuangan.neraca-akhir'));
    $neracaResponse->assertSuccessful();
    $neracaResponse->assertSee('Persediaan Hasil Tangkapan');
});

test('skenario A B C: input, hapus, dan input ulang hasil tangkapan menyinkronkan Kas, Rugi Laba, dan General Ledger secara akurat', function () {
    DefaultAccountSeeder::seedForUser($this->nelayan->id);
    $jenisTunai = JenisPembayaran::create(['nama' => 'Tunai']);
    $keuanganService = app(KeuanganService::class);

    // =========================================================================
    // SKENARIO A: Input Hasil Tangkapan, Stok Kartu Gudang, dan Transaksi Kasir
    // =========================================================================
    $this->actingAs($this->nelayan)->post(route('barang.store'), [
        'kode_barang' => 'IKN-PATIN-01',
        'nama' => 'Ikan Patin',
        'satuan' => 'Kg',
        'harga_jual_per_unit' => 60000,
    ])->assertRedirect(route('barang.create'));

    $ikanPatin = Barang::where('user_id', $this->nelayan->id)->where('kode_barang', 'IKN-PATIN-01')->firstOrFail();

    // Input hasil tangkapan ke Kartu Gudang: 10 Kg @ Rp 60.000 = Rp 600.000
    $this->actingAs($this->nelayan)->post(route('kartu-gudang.store', $ikanPatin->id), [
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Hasil Tangkapan Sore',
        'diterima' => 10,
        'dikeluarkan' => 0,
    ])->assertRedirect(route('kartu-gudang.detail', ['barang_id' => $ikanPatin->id]));

    $kgMasuk = KartuGudang::where('barang_id', $ikanPatin->id)->firstOrFail();
    expect($kgMasuk->journal_entry_id)->not->toBeNull();

    // Transaksi penjualan Kasir atas hasil tangkapan: 2 Kg @ Rp 60.000 = Rp 120.000
    $this->actingAs($this->nelayan)->post(route('keuangan.kasir.store'), [
        'jenis_pembayaran_id' => $jenisTunai->id,
        'id_barang_terjual' => [$ikanPatin->id],
        'jumlah_barang_dijual' => [2],
        'grand_total' => 120000,
        'diskon_total' => 0,
        'uang_bayar' => 120000,
        'uang_kembalian' => 0,
    ])->assertSessionHas('success');

    // Verifikasi Skenario A: Kas, Rugi/Laba, Neraca, dan General Ledger
    $labaRugiA = $keuanganService->hitungLabaRugi(null, null, $this->nelayan->id);
    $neracaA = $keuanganService->hitungNeraca(null, $this->nelayan->id);
    $totalDebitA = (float) JournalItem::where('user_id', $this->nelayan->id)->sum('debit');
    $totalCreditA = (float) JournalItem::where('user_id', $this->nelayan->id)->sum('credit');

    expect($neracaA['totalKas'])->toBe(120000.0)
        ->and($labaRugiA['totalPenjualan'])->toBe(120000.0)
        ->and($labaRugiA['penjualanBersih'])->toBe(120000.0)
        ->and($labaRugiA['persediaanAkhir'])->toBe(480000.0) // Sisa 8 Kg * 60.000
        ->and($totalDebitA)->toBe(720000.0) // 600.000 (KG) + 120.000 (Kasir)
        ->and($totalCreditA)->toBe(720000.0)
        ->and($totalDebitA)->toBe($totalCreditA);

    // =========================================================================
    // SKENARIO B: Hapus Hasil Tangkapan -> Kas, Rugi/Laba, dan GL terkoreksi
    // =========================================================================
    $this->actingAs($this->nelayan)
        ->delete(route('barang.destroy', $ikanPatin->id))
        ->assertRedirect(route('barang.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('barang', ['id' => $ikanPatin->id]);
    $this->assertDatabaseMissing('kartu_gudang', ['barang_id' => $ikanPatin->id]);

    $labaRugiB = $keuanganService->hitungLabaRugi(null, null, $this->nelayan->id);
    $neracaB = $keuanganService->hitungNeraca(null, $this->nelayan->id);
    $totalDebitB = (float) JournalItem::where('user_id', $this->nelayan->id)->sum('debit');
    $totalCreditB = (float) JournalItem::where('user_id', $this->nelayan->id)->sum('credit');

    expect((float) $neracaB['totalKas'])->toBe(0.0)
        ->and((float) $labaRugiB['totalPenjualan'])->toBe(0.0)
        ->and((float) $labaRugiB['penjualanBersih'])->toBe(0.0)
        ->and((float) $labaRugiB['persediaanAkhir'])->toBe(0.0)
        ->and($totalDebitB)->toBe(0.0)
        ->and($totalCreditB)->toBe(0.0)
        ->and(JournalEntry::where('user_id', $this->nelayan->id)->count())->toBe(0)
        ->and(KasirTransactionLog::where('user_id', $this->nelayan->id)->count())->toBe(0);

    // =========================================================================
    // SKENARIO C: Input Ulang Hasil Tangkapan -> Tidak Duplikat & Akurat
    // =========================================================================
    $this->actingAs($this->nelayan)->post(route('barang.store'), [
        'kode_barang' => 'IKN-PATIN-01',
        'nama' => 'Ikan Patin',
        'satuan' => 'Kg',
        'harga_jual_per_unit' => 60000,
    ])->assertRedirect(route('barang.create'));

    $ikanPatinBaru = Barang::where('user_id', $this->nelayan->id)->where('kode_barang', 'IKN-PATIN-01')->firstOrFail();

    $this->actingAs($this->nelayan)->post(route('kartu-gudang.store', $ikanPatinBaru->id), [
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Tangkapan Input Ulang',
        'diterima' => 5,
        'dikeluarkan' => 0,
    ])->assertRedirect(route('kartu-gudang.detail', ['barang_id' => $ikanPatinBaru->id]));

    $this->actingAs($this->nelayan)->post(route('keuangan.kasir.store'), [
        'jenis_pembayaran_id' => $jenisTunai->id,
        'id_barang_terjual' => [$ikanPatinBaru->id],
        'jumlah_barang_dijual' => [1],
        'grand_total' => 60000,
        'diskon_total' => 0,
        'uang_bayar' => 60000,
        'uang_kembalian' => 0,
    ])->assertSessionHas('success');

    $labaRugiC = $keuanganService->hitungLabaRugi(null, null, $this->nelayan->id);
    $neracaC = $keuanganService->hitungNeraca(null, $this->nelayan->id);
    $totalDebitC = (float) JournalItem::where('user_id', $this->nelayan->id)->sum('debit');
    $totalCreditC = (float) JournalItem::where('user_id', $this->nelayan->id)->sum('credit');

    expect($neracaC['totalKas'])->toBe(60000.0)
        ->and($labaRugiC['totalPenjualan'])->toBe(60000.0)
        ->and($labaRugiC['persediaanAkhir'])->toBe(240000.0) // Sisa 4 Kg * 60.000
        ->and($totalDebitC)->toBe(360000.0) // 300.000 (KG) + 60.000 (Kasir)
        ->and($totalCreditC)->toBe(360000.0)
        ->and(JournalEntry::where('user_id', $this->nelayan->id)->count())->toBe(2)
        ->and(KasirTransactionLog::where('user_id', $this->nelayan->id)->count())->toBe(1);
});

test('penghapusan salah satu hasil tangkapan pada transaksi Kasir multi-item hanya menyesuaikan porsi item yang dihapus tanpa menghapus item lain', function () {
    DefaultAccountSeeder::seedForUser($this->nelayan->id);
    $jenisTunai = JenisPembayaran::create(['nama' => 'Tunai']);
    $keuanganService = app(KeuanganService::class);

    $ikanBass = Barang::create([
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-BASS-01',
        'nama' => 'Ikan Bass',
        'satuan' => 'Kg',
        'harga_jual_per_unit' => 60000,
        'harga_beli_per_unit' => 0,
    ]);

    $ikanPatin = Barang::create([
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-PATIN-02',
        'nama' => 'Ikan Patin',
        'satuan' => 'Kg',
        'harga_jual_per_unit' => 40000,
        'harga_beli_per_unit' => 0,
    ]);

    // Tambah stok masing-masing 10 Kg
    $this->actingAs($this->nelayan)->post(route('kartu-gudang.store', $ikanBass->id), [
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Stok Bass',
        'diterima' => 10,
        'dikeluarkan' => 0,
    ]);
    $this->actingAs($this->nelayan)->post(route('kartu-gudang.store', $ikanPatin->id), [
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Stok Patin',
        'diterima' => 10,
        'dikeluarkan' => 0,
    ]);

    // Transaksi Kasir gabungan: 3 Kg Ikan Bass (180.000) + 3 Kg Ikan Patin (120.000) = 300.000
    $this->actingAs($this->nelayan)->post(route('keuangan.kasir.store'), [
        'jenis_pembayaran_id' => $jenisTunai->id,
        'id_barang_terjual' => [$ikanBass->id, $ikanPatin->id],
        'jumlah_barang_dijual' => [3, 3],
        'grand_total' => 300000,
        'diskon_total' => 0,
        'uang_bayar' => 300000,
        'uang_kembalian' => 0,
    ])->assertSessionHas('success');

    // Hapus hanya Ikan Patin (yang porsinya Rp 120.000)
    $this->actingAs($this->nelayan)
        ->delete(route('barang.destroy', $ikanPatin->id))
        ->assertRedirect(route('barang.index'));

    // Pastikan transaksi Ikan Bass (Rp 180.000) tetap utuh dan seimbang
    $labaRugi = $keuanganService->hitungLabaRugi(null, null, $this->nelayan->id);
    $neraca = $keuanganService->hitungNeraca(null, $this->nelayan->id);
    $totalDebit = (float) JournalItem::where('user_id', $this->nelayan->id)->sum('debit');
    $totalCredit = (float) JournalItem::where('user_id', $this->nelayan->id)->sum('credit');

    expect($neraca['totalKas'])->toBe(180000.0)
        ->and($labaRugi['totalPenjualan'])->toBe(180000.0)
        ->and($labaRugi['persediaanAkhir'])->toBe(420000.0) // 7 Kg Bass * 60.000
        ->and($totalDebit)->toBe($totalCredit)
        ->and((float) KasirTransactionLog::where('user_id', $this->nelayan->id)->first()->jumlah)->toBe(180000.0);
});

test('skenario D: kegagalan di tengah proses penghapusan hasil tangkapan melakukan rollback penuh tanpa meninggalkan data terhapus sebagian', function () {
    DefaultAccountSeeder::seedForUser($this->nelayan->id);
    $jenisTunai = JenisPembayaran::create(['nama' => 'Tunai']);

    $ikan = Barang::create([
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-RB-01',
        'nama' => 'Ikan Kerapu Rollback',
        'satuan' => 'Kg',
        'harga_jual_per_unit' => 100000,
        'harga_beli_per_unit' => 0,
    ]);

    $this->actingAs($this->nelayan)->post(route('kartu-gudang.store', $ikan->id), [
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Tangkapan Pagi',
        'diterima' => 10,
        'dikeluarkan' => 0,
    ]);

    $this->actingAs($this->nelayan)->post(route('keuangan.kasir.store'), [
        'jenis_pembayaran_id' => $jenisTunai->id,
        'id_barang_terjual' => [$ikan->id],
        'jumlah_barang_dijual' => [2],
        'grand_total' => 200000,
        'diskon_total' => 0,
        'uang_bayar' => 200000,
        'uang_kembalian' => 0,
    ]);

    // Simulasikan kegagalan (exception) setelah baris pertama KartuGudang dihapus di dalam transaksi
    Barang::deleting(function (Barang $model) use ($ikan) {
        if ($model->id === $ikan->id) {
            throw new RuntimeException('Simulasi kegagalan database saat menghapus hasil tangkapan');
        }
    });

    $response = $this->actingAs($this->nelayan)->delete(route('barang.destroy', $ikan->id));
    $response->assertRedirect(route('barang.index'));
    $response->assertSessionHas('error');

    // Bersihkan event listener model
    Barang::flushEventListeners();

    // Pastikan seluruh data (Barang, 2 baris KartuGudang, 2 JournalEntry, 4 JournalItem, dan KasirTransactionLog) utuh kembali karena rollback
    expect(Barang::where('id', $ikan->id)->exists())->toBeTrue()
        ->and(KartuGudang::where('barang_id', $ikan->id)->count())->toBe(2)
        ->and(JournalEntry::where('user_id', $this->nelayan->id)->count())->toBe(2)
        ->and(KasirTransactionLog::where('user_id', $this->nelayan->id)->count())->toBe(1)
        ->and((float) JournalItem::where('user_id', $this->nelayan->id)->sum('debit'))
        ->toBe((float) JournalItem::where('user_id', $this->nelayan->id)->sum('credit'));
});
