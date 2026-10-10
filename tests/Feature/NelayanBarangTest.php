<?php

use App\Models\Barang;
use App\Models\KartuGudang;
use App\Models\User;

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
    $response->assertSee('Daftar Hasil Tangkapan Nelayan');
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
    $response->assertSee('1 unit = 1 kilogram');
    $response->assertSee('Diterima (Kg)');
    $response->assertSee('Dikeluarkan (Kg)');
    $response->assertSee('Saldo (Kg)');
    $response->assertSee('Tambah Stok Ikan');
    $response->assertDontSee('Saldo Akhir Kemasan');
});

test('nelayan kartu gudang create displays per-kilogram format and fish specific form', function () {
    $barang = Barang::create([
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-005',
        'nama' => 'Ikan Tenggiri',
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
