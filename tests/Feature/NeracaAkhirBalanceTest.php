<?php

use App\Models\Barang;
use App\Models\KartuGudang;
use App\Models\User;
use App\Services\KeuanganService;
use Database\Seeders\DefaultAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('neraca akhir seimbang setelah inisialisasi neraca awal', function () {
    $user = User::factory()->create([
        'alamat' => 'Jl. Test No. 1',
        'nomor_telepon' => '08123456789',
        'is_verified' => true,
    ]);
    $this->actingAs($user);
    DefaultAccountSeeder::seedForUser($user->id);

    $barang = Barang::create([
        'user_id' => $user->id,
        'kode_barang' => 'BRG01',
        'nama' => 'Barang A',
        'harga_beli_per_unit' => 100000,
        'harga_jual_per_unit' => 120000,
    ]);

    KartuGudang::create([
        'user_id' => $user->id,
        'barang_id' => $barang->id,
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Saldo Awal',
        'saldo_persatuan' => 100,
    ]);

    $response = $this->post(route('laporan-keuangan.neraca-awal.store'), [
        'kas' => 50000000,
        'uraian_kas' => 'Saldo kas awal',
        'barang_ids' => [$barang->id],
        'total_persediaan' => 10000000,
        'tanah_bangunan' => 200000000,
        'kendaraan' => 50000000,
        'meubel_peralatan' => 15000000,
        'tidak_ada_piutang' => '1',
        'tidak_ada_hutang' => '1',
    ]);

    $response->assertRedirect(route('laporan-keuangan.neraca-awal.index'));

    $keuangan = app(KeuanganService::class);
    $neraca = $keuangan->hitungNeraca();

    expect($neraca['totalAktiva'])->toBe((float) (50000000 + 10000000 + 200000000 + 50000000 + 15000000))
        ->and($neraca['totalPasiva'])->toBe((float) $neraca['totalAktiva']);
});

test('neraca akhir tetap seimbang setelah transaksi pengeluaran operasional', function () {
    $user = User::factory()->create([
        'alamat' => 'Jl. Test No. 1',
        'nomor_telepon' => '08123456789',
        'is_verified' => true,
    ]);
    $this->actingAs($user);
    DefaultAccountSeeder::seedForUser($user->id);

    $barang = Barang::create([
        'user_id' => $user->id,
        'kode_barang' => 'BRG01',
        'nama' => 'Barang A',
        'harga_beli_per_unit' => 100000,
        'harga_jual_per_unit' => 120000,
    ]);

    $this->post(route('laporan-keuangan.neraca-awal.store'), [
        'kas' => 10000000,
        'uraian_kas' => 'Saldo kas awal',
        'barang_ids' => [$barang->id],
        'total_persediaan' => 0,
        'tanah_bangunan' => 0,
        'kendaraan' => 0,
        'meubel_peralatan' => 0,
        'tidak_ada_piutang' => '1',
        'tidak_ada_hutang' => '1',
    ]);

    $this->post(route('keuangan.pengeluaran.store'), [
        'tanggal' => now()->format('Y-m-d'),
        'jenis_keperluan' => 'lain_lain',
        'jenis_pengeluaran' => 'tunai',
        'uraian_pengeluaran' => 'Bayar Listrik',
        'jumlah_manual' => 500000,
        'potongan_pembelian' => 0,
        'biaya_lain' => 0,
        'admin_bank' => 0,
        'jumlah' => 500000,
    ]);

    $keuangan = app(KeuanganService::class);
    $neraca = $keuangan->hitungNeraca();

    expect($neraca['totalAktiva'])->toBe((float) 9500000)
        ->and($neraca['totalPasiva'])->toBe((float) 9500000);
});

test('neraca akhir tetap seimbang setelah transaksi pendapatan tunai', function () {
    $user = User::factory()->create([
        'alamat' => 'Jl. Test No. 1',
        'nomor_telepon' => '08123456789',
        'is_verified' => true,
    ]);
    $this->actingAs($user);
    DefaultAccountSeeder::seedForUser($user->id);

    $barang = Barang::create([
        'user_id' => $user->id,
        'kode_barang' => 'BRG01',
        'nama' => 'Barang A',
        'harga_beli_per_unit' => 100000,
        'harga_jual_per_unit' => 120000,
    ]);

    $this->post(route('laporan-keuangan.neraca-awal.store'), [
        'kas' => 10000000,
        'uraian_kas' => 'Saldo kas awal',
        'barang_ids' => [$barang->id],
        'total_persediaan' => 0,
        'tanah_bangunan' => 0,
        'kendaraan' => 0,
        'meubel_peralatan' => 0,
        'tidak_ada_piutang' => '1',
        'tidak_ada_hutang' => '1',
    ]);

    // Transaksi pendapatan lain-lain tunai
    $this->post(route('keuangan.pendapatan.store_lain'), [
        'tanggal' => now()->format('Y-m-d'),
        'uraian_pendapatan' => 'Pendapatan Jasa / Lain',
        'jumlah' => 2000000,
    ]);

    $keuangan = app(KeuanganService::class);
    $neraca = $keuangan->hitungNeraca();

    expect($neraca['totalAktiva'])->toBe((float) 12000000)
        ->and($neraca['totalPasiva'])->toBe((float) 12000000);
});
