<?php

use App\Models\Barang;
use App\Models\KartuGudang;
use App\Models\User;
use App\Services\KeuanganService;
use Database\Seeders\DefaultAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('nilai laba bersih di dashboard sama dengan laba bersih di laporan rugi laba', function () {
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
        'saldo_persatuan' => 50,
    ]);

    $this->post(route('laporan-keuangan.neraca-awal.store'), [
        'kas' => 20000000,
        'uraian_kas' => 'Saldo kas awal',
        'barang_ids' => [$barang->id],
        'total_persediaan' => 5000000,
        'tanah_bangunan' => 10000000,
        'kendaraan' => 5000000,
        'meubel_peralatan' => 2000000,
        'tidak_ada_piutang' => '1',
        'tidak_ada_hutang' => '1',
    ]);

    // Transaksi pendapatan operasional (penjualan/pendapatan lain)
    $this->post(route('keuangan.pendapatan.store_lain'), [
        'tanggal' => now()->format('Y-m-d'),
        'uraian_pendapatan' => 'Pendapatan Jasa Service',
        'jumlah' => 3000000,
    ]);

    // Transaksi beban operasional
    $this->post(route('keuangan.pengeluaran.store'), [
        'tanggal' => now()->format('Y-m-d'),
        'jenis_keperluan' => 'lain_lain',
        'jenis_pengeluaran' => 'tunai',
        'uraian_pengeluaran' => 'Bayar Listrik & Internet',
        'jumlah_manual' => 500000,
        'potongan_pembelian' => 0,
        'biaya_lain' => 0,
        'admin_bank' => 0,
        'jumlah' => 500000,
    ]);

    // Akses laporan rugi laba
    $rugiLabaResponse = $this->get(route('laporan-keuangan.rugi-laba'));
    $rugiLabaResponse->assertOk();
    $labaSetelahPajakRugiLaba = $rugiLabaResponse->viewData('labaSetelahPajak');

    // Akses dashboard
    $dashboardResponse = $this->get(route('dashboard'));
    $dashboardResponse->assertOk();
    $labaBersihDashboard = $dashboardResponse->viewData('labaBersih');

    // Pastikan laba bersih di dashboard sama persis dengan di laporan rugi laba
    expect($labaBersihDashboard)->toBe($labaSetelahPajakRugiLaba);

    // Pastikan juga nilai yang dihitung oleh KeuanganService sama persis
    $keuangan = app(KeuanganService::class);
    $labaRugiService = $keuangan->hitungLabaRugi();
    expect($labaBersihDashboard)->toBe($labaRugiService['labaSetelahPajak']);
});

test('nilai laba bersih di dashboard dan laporan rugi laba sama saat belum ada transaksi', function () {
    $user = User::factory()->create([
        'alamat' => 'Jl. Test No. 1',
        'nomor_telepon' => '08123456789',
        'is_verified' => true,
    ]);
    $this->actingAs($user);
    DefaultAccountSeeder::seedForUser($user->id);

    $rugiLabaResponse = $this->get(route('laporan-keuangan.rugi-laba'));
    $rugiLabaResponse->assertOk();
    $labaSetelahPajakRugiLaba = $rugiLabaResponse->viewData('labaSetelahPajak');

    $dashboardResponse = $this->get(route('dashboard'));
    $dashboardResponse->assertOk();
    $labaBersihDashboard = $dashboardResponse->viewData('labaBersih');

    expect($labaBersihDashboard)->toEqual(0)
        ->and($labaBersihDashboard)->toBe($labaSetelahPajakRugiLaba);
});
