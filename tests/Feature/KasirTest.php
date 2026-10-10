<?php

use App\Models\Account;
use App\Models\Barang;
use App\Models\JenisPembayaran;
use App\Models\KartuGudang;
use App\Models\KasirTransactionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'is_verified' => true,
        'alamat' => 'Jl. Pengujian Kasir No. 1',
        'nomor_telepon' => '081234567890',
    ]);

    Account::create([
        'user_id' => $this->user->id,
        'code' => '1101',
        'name' => 'Kas',
        'category' => 'asset',
        'normal_balance' => 'debit',
    ]);

    Account::create([
        'user_id' => $this->user->id,
        'code' => '4101',
        'name' => 'Pendapatan Penjualan',
        'category' => 'revenue',
        'normal_balance' => 'credit',
    ]);

    $this->tunai = JenisPembayaran::create(['nama' => 'tunai']);
    $this->transfer = JenisPembayaran::create(['nama' => 'transfer_bank']);
    $this->qris = JenisPembayaran::create(['nama' => 'qris']);

    $this->barang = Barang::create([
        'user_id' => $this->user->id,
        'kode_barang' => 'BRG-KASIR-01',
        'nama' => 'Produk Kasir 1',
        'jumlah_max' => 100,
        'jumlah_min' => 1,
        'jumlah_unit_per_kemasan' => 10,
        'harga_beli_per_kemas' => 50000,
        'harga_beli_per_unit' => 5000,
        'harga_jual_per_unit' => 10000,
    ]);

    KartuGudang::create([
        'barang_id' => $this->barang->id,
        'user_id' => $this->user->id,
        'tanggal' => now(),
        'uraian' => 'Saldo Awal',
        'diterima' => 50,
        'dikeluarkan' => 0,
        'saldo_persatuan' => 50,
        'saldo_perkemasan' => 5,
    ]);
});

test('kasir create page displays correctly and has metadata element without internal script', function () {
    $response = $this->actingAs($this->user)->get(route('keuangan.kasir.create'));

    $response->assertSuccessful();
    $response->assertSee('id="kasir-metadata"', false);
    $response->assertSee('id="info-bayar-otomatis"', false);
    $response->assertDontSee('function updateStokInfo(selectElement)', false);
});

test('kasir store with tunai requires uang_bayar to be greater than or equal to grand_total', function () {
    $response = $this->actingAs($this->user)->post(route('keuangan.kasir.store'), [
        'jenis_pembayaran_id' => $this->tunai->id,
        'grand_total' => 20000,
        'id_barang_terjual' => [$this->barang->id],
        'jumlah_barang_dijual' => [2],
        'uang_bayar' => 15000, // kurang
        'uang_kembalian' => -5000,
    ]);

    $response->assertSessionHasErrors(['uang_bayar']);
});

test('kasir store with transfer_bank does not require uang_bayar and completes successfully', function () {
    $response = $this->actingAs($this->user)->post(route('keuangan.kasir.store'), [
        'jenis_pembayaran_id' => $this->transfer->id,
        'grand_total' => 20000,
        'id_barang_terjual' => [$this->barang->id],
        'jumlah_barang_dijual' => [2],
        // uang_bayar intentionally omitted
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('success', 'Transaksi berhasil');

    $log = KasirTransactionLog::where('user_id', $this->user->id)->latest('id')->first();
    expect($log)->not->toBeNull();
    expect((float) $log->bayar)->toEqual(20000.0);
    expect((float) $log->kembalian)->toEqual(0.0);
    expect((float) $log->jumlah)->toEqual(20000.0);
});

test('kasir store with tunai succeeds when uang_bayar is sufficient', function () {
    $response = $this->actingAs($this->user)->post(route('keuangan.kasir.store'), [
        'jenis_pembayaran_id' => $this->tunai->id,
        'grand_total' => 20000,
        'id_barang_terjual' => [$this->barang->id],
        'jumlah_barang_dijual' => [2],
        'uang_bayar' => '50.000',
        'uang_kembalian' => '30.000',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('success', 'Transaksi berhasil');

    $log = KasirTransactionLog::where('user_id', $this->user->id)->latest('id')->first();
    expect($log)->not->toBeNull();
    expect((float) $log->bayar)->toEqual(50000.0);
    expect((float) $log->kembalian)->toEqual(30000.0);
});

test('kasir store with qris does not require uang_bayar and completes successfully', function () {
    $response = $this->actingAs($this->user)->post(route('keuangan.kasir.store'), [
        'jenis_pembayaran_id' => $this->qris->id,
        'grand_total' => 10000,
        'id_barang_terjual' => [$this->barang->id],
        'jumlah_barang_dijual' => [1],
        // uang_bayar and uang_kembalian omitted
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('success', 'Transaksi berhasil');

    $log = KasirTransactionLog::where('user_id', $this->user->id)->latest('id')->first();
    expect($log)->not->toBeNull();
    expect((float) $log->bayar)->toEqual(10000.0);
    expect((float) $log->kembalian)->toEqual(0.0);
    expect((float) $log->jumlah)->toEqual(10000.0);
});
