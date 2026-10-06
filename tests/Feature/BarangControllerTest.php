<?php

use App\Models\Account;
use App\Models\Barang;
use App\Models\KartuGudang;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'barang_test@test.com',
        'alamat' => 'Jl. Pengujian Barang No. 1',
        'nomor_telepon' => '081234567890',
        'is_verified' => 1,
    ]);
});

test('barang index can be displayed for authenticated user', function () {
    Barang::create([
        'user_id' => $this->user->id,
        'kode_barang' => 'BRG-001',
        'nama' => 'Barang Uji 1',
        'jumlah_max' => 100,
        'jumlah_min' => 10,
        'jumlah_unit_per_kemasan' => 10,
        'harga_beli_per_kemas' => 50000,
        'harga_beli_per_unit' => 5000,
        'harga_jual_per_unit' => 7000,
    ]);

    $response = $this->actingAs($this->user)->get(route('barang.index'));

    $response->assertSuccessful();
    $response->assertSee('BRG-001');
    $response->assertSee('Barang Uji 1');
});

test('store barang validates required fields via StoreBarangRequest', function () {
    $response = $this->actingAs($this->user)->post(route('barang.store'), []);

    $response->assertSessionHasErrors([
        'kode_barang',
        'nama',
        'jumlah_max',
        'jumlah_min',
        'jumlah_unit_per_kemasan',
        'harga_beli_per_kemas',
        'harga_beli_per_unit',
        'harga_jual_per_unit',
    ]);
});

test('store barang validates minimum value via StoreBarangRequest', function () {
    $response = $this->actingAs($this->user)->post(route('barang.store'), [
        'kode_barang' => 'BRG-ERR',
        'nama' => 'Barang Minus',
        'jumlah_max' => -1,
        'jumlah_min' => -1,
        'jumlah_unit_per_kemasan' => -1,
        'harga_beli_per_kemas' => -100,
        'harga_beli_per_unit' => -10,
        'harga_jual_per_unit' => -20,
    ]);

    $response->assertSessionHasErrors([
        'jumlah_max',
        'jumlah_min',
        'jumlah_unit_per_kemasan',
        'harga_beli_per_kemas',
        'harga_beli_per_unit',
        'harga_jual_per_unit',
    ]);
});

test('store barang creates a record successfully when validated', function () {
    $response = $this->actingAs($this->user)->post(route('barang.store'), [
        'kode_barang' => 'BRG-OK',
        'nama' => 'Barang Bagus',
        'jumlah_max' => 500,
        'jumlah_min' => 20,
        'jumlah_unit_per_kemasan' => 25,
        'harga_beli_per_kemas' => 100000,
        'harga_beli_per_unit' => 4000,
        'harga_jual_per_unit' => 6000,
    ]);

    $response->assertRedirect(route('barang.create'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('barang', [
        'user_id' => $this->user->id,
        'kode_barang' => 'BRG-OK',
        'nama' => 'Barang Bagus',
    ]);
});

test('update barang validates min 1 for jumlah unit per kemasan via UpdateBarangRequest', function () {
    $barang = Barang::create([
        'user_id' => $this->user->id,
        'kode_barang' => 'BRG-UPD',
        'nama' => 'Barang Update',
        'jumlah_max' => 100,
        'jumlah_min' => 10,
        'jumlah_unit_per_kemasan' => 10,
        'harga_beli_per_kemas' => 50000,
        'harga_beli_per_unit' => 5000,
        'harga_jual_per_unit' => 7000,
    ]);

    $response = $this->actingAs($this->user)->put(route('barang.update', $barang->id), [
        'kode_barang' => 'BRG-UPD',
        'nama' => 'Barang Update Baru',
        'jumlah_max' => 100,
        'jumlah_min' => 10,
        'jumlah_unit_per_kemasan' => 0, // min is 1 for update
        'harga_beli_per_kemas' => 50000,
        'harga_beli_per_unit' => 5000,
        'harga_jual_per_unit' => 7000,
    ]);

    $response->assertSessionHasErrors(['jumlah_unit_per_kemasan']);
});

test('update barang successfully updates record when valid', function () {
    $barang = Barang::create([
        'user_id' => $this->user->id,
        'kode_barang' => 'BRG-UPD-2',
        'nama' => 'Barang Lama',
        'jumlah_max' => 100,
        'jumlah_min' => 10,
        'jumlah_unit_per_kemasan' => 10,
        'harga_beli_per_kemas' => 50000,
        'harga_beli_per_unit' => 5000,
        'harga_jual_per_unit' => 7000,
    ]);

    $response = $this->actingAs($this->user)->put(route('barang.update', $barang->id), [
        'kode_barang' => 'BRG-UPD-MOD',
        'nama' => 'Barang Terupdate',
        'jumlah_max' => 120,
        'jumlah_min' => 15,
        'jumlah_unit_per_kemasan' => 12,
        'harga_beli_per_kemas' => 60000,
        'harga_beli_per_unit' => 5000,
        'harga_jual_per_unit' => 8000,
    ]);

    $response->assertRedirect(route('barang.show', $barang->id));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('barang', [
        'id' => $barang->id,
        'kode_barang' => 'BRG-UPD-MOD',
        'nama' => 'Barang Terupdate',
        'jumlah_max' => 120,
    ]);
});

test('destroy barang deletes record and its kartu gudang', function () {
    $barang = Barang::create([
        'user_id' => $this->user->id,
        'kode_barang' => 'BRG-DEL',
        'nama' => 'Barang Dihapus',
        'jumlah_max' => 50,
        'jumlah_min' => 5,
        'jumlah_unit_per_kemasan' => 5,
        'harga_beli_per_kemas' => 25000,
        'harga_beli_per_unit' => 5000,
        'harga_jual_per_unit' => 7000,
    ]);

    KartuGudang::create([
        'user_id' => $this->user->id,
        'barang_id' => $barang->id,
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Stok Awal',
        'diterima' => 10,
        'dikeluarkan' => 0,
        'saldo_persatuan' => 10,
        'saldo_perkemasan' => 2,
    ]);

    $response = $this->actingAs($this->user)->delete(route('barang.destroy', $barang->id));

    $response->assertRedirect(route('barang.index'));
    $this->assertDatabaseMissing('barang', ['id' => $barang->id]);
    $this->assertDatabaseMissing('kartu_gudang', ['barang_id' => $barang->id]);
});

test('store kartu gudang validates via StoreKartuGudangRequest and saves entry', function () {
    Account::create(['user_id' => $this->user->id, 'code' => '1105', 'name' => 'Persediaan Barang Dagang', 'category' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
    Account::create(['user_id' => $this->user->id, 'code' => '3100', 'name' => 'Modal Pemilik', 'category' => 'equity', 'normal_balance' => 'credit', 'is_active' => true]);

    $barang = Barang::create([
        'user_id' => $this->user->id,
        'kode_barang' => 'BRG-KG',
        'nama' => 'Barang KG',
        'jumlah_max' => 100,
        'jumlah_min' => 10,
        'jumlah_unit_per_kemasan' => 10,
        'harga_beli_per_kemas' => 50000,
        'harga_beli_per_unit' => 5000,
        'harga_jual_per_unit' => 7000,
    ]);

    // Validation failure test
    $responseErr = $this->actingAs($this->user)->post(route('kartu-gudang.store', $barang->id), []);
    $responseErr->assertSessionHasErrors(['tanggal', 'uraian']);

    // Successful store
    $response = $this->actingAs($this->user)->post(route('kartu-gudang.store', $barang->id), [
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Penerimaan Supplier',
        'diterima' => 20,
        'dikeluarkan' => 0,
    ]);

    $response->assertRedirect(route('kartu-gudang.detail', ['barang_id' => $barang->id]));
    $this->assertDatabaseHas('kartu_gudang', [
        'user_id' => $this->user->id,
        'barang_id' => $barang->id,
        'diterima' => 20,
        'saldo_persatuan' => 20,
    ]);
});

test('kartu gudang index displays summary table of barang with formatted package balance', function () {
    $barang = Barang::create([
        'user_id' => $this->user->id,
        'kode_barang' => 'BRG-TBL',
        'nama' => 'Barang Tabel Ringkasan',
        'jumlah_max' => 50,
        'jumlah_min' => 5,
        'jumlah_unit_per_kemasan' => 10,
        'harga_beli_per_kemas' => 50000,
        'harga_beli_per_unit' => 5000,
        'harga_jual_per_unit' => 7000,
    ]);

    KartuGudang::create([
        'user_id' => $this->user->id,
        'barang_id' => $barang->id,
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Saldo Awal',
        'diterima' => 99,
        'dikeluarkan' => 0,
        'saldo_persatuan' => 99,
        'saldo_perkemasan' => 10,
    ]);

    $response = $this->actingAs($this->user)->get(route('kartu-gudang.index'));

    $response->assertSuccessful();
    $response->assertSee('BRG-TBL');
    $response->assertSee('Barang Tabel Ringkasan');
    $response->assertSee('9 kemas + 9 unit');
    $response->assertSee(route('kartu-gudang.detail', $barang->id));
});

test('kartu gudang detail displays transaction history and packaging breakdown 9 kemas + 9 unit', function () {
    $barang = Barang::create([
        'user_id' => $this->user->id,
        'kode_barang' => 'BRG-PKG',
        'nama' => 'Barang Kemasan Test',
        'jumlah_max' => 20,
        'jumlah_min' => 2,
        'jumlah_unit_per_kemasan' => 10, // 1 kemas = 10 unit
        'harga_beli_per_kemas' => 100000,
        'harga_beli_per_unit' => 10000,
        'harga_jual_per_unit' => 12000,
    ]);

    // Transaksi 1: Penerimaan 10 kemas = 100 unit
    KartuGudang::create([
        'user_id' => $this->user->id,
        'barang_id' => $barang->id,
        'tanggal' => now()->subDay()->format('Y-m-d'),
        'uraian' => 'Penerimaan Awal 10 Kemas',
        'diterima' => 100,
        'dikeluarkan' => 0,
        'saldo_persatuan' => 100,
        'saldo_perkemasan' => 10,
    ]);

    // Transaksi 2: Kasir menjual 1 unit, sisa saldo persatuan = 99 unit
    $kartuJual = KartuGudang::create([
        'user_id' => $this->user->id,
        'barang_id' => $barang->id,
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Penjualan Kasir 1 Unit',
        'diterima' => 0,
        'dikeluarkan' => 1,
        'saldo_persatuan' => 99,
        'saldo_perkemasan' => 10,
    ]);

    $response = $this->actingAs($this->user)->get(route('kartu-gudang.detail', $barang->id));

    $response->assertSuccessful();
    $response->assertSee('BRG-PKG');
    $response->assertSee('Barang Kemasan Test');
    // Saldo 100 unit harus terformat "10 kemas"
    $response->assertSee('10 kemas');
    // Saldo 99 unit harus terformat "9 kemas + 9 unit"
    $response->assertSee('9 kemas + 9 unit');

    // Test delete kartu gudang redirects back to detail
    $deleteResponse = $this->actingAs($this->user)->delete(route('kartu-gudang.destroy', $kartuJual->id));
    $deleteResponse->assertRedirect(route('kartu-gudang.detail', ['barang_id' => $barang->id]));
    $this->assertDatabaseMissing('kartu_gudang', ['id' => $kartuJual->id]);
});

test('user cannot view kartu gudang detail of another user barang', function () {
    $otherUser = User::factory()->create([
        'email' => 'other_user@test.com',
        'is_verified' => 1,
    ]);

    $otherBarang = Barang::create([
        'user_id' => $otherUser->id,
        'kode_barang' => 'BRG-OTHER',
        'nama' => 'Barang Milik User Lain',
        'jumlah_max' => 10,
        'jumlah_min' => 1,
        'jumlah_unit_per_kemasan' => 5,
        'harga_beli_per_kemas' => 20000,
        'harga_beli_per_unit' => 4000,
        'harga_jual_per_unit' => 5000,
    ]);

    $response = $this->actingAs($this->user)->get(route('kartu-gudang.detail', $otherBarang->id));

    $response->assertRedirect(route('kartu-gudang.index'));
    $response->assertSessionHas('error');
});
