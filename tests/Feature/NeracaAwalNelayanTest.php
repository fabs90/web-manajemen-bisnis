<?php

use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\KartuGudang;
use App\Models\User;
use App\Services\KeuanganService;
use Database\Seeders\DefaultAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->nelayan = User::factory()->create([
        'email' => 'nelayan_neraca@test.com',
        'role' => 'nelayan',
        'alamat' => 'Dermaga Muara Baru No. 10',
        'nomor_telepon' => '081298765432',
        'is_verified' => 1,
    ]);
    DefaultAccountSeeder::seedForUser($this->nelayan->id);

    $this->ukm = User::factory()->create([
        'email' => 'ukm_neraca@test.com',
        'role' => 'ukm',
        'alamat' => 'Jl. Pengrajin No. 5',
        'nomor_telepon' => '081234567890',
        'is_verified' => 1,
    ]);
    DefaultAccountSeeder::seedForUser($this->ukm->id);
});

test('nelayan create view renders with catch specific labels and correct inventory calculation data', function () {
    $ikan = Barang::create([
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-01',
        'nama' => 'Ikan Tongkol Segar',
        'jumlah_max' => 0,
        'jumlah_min' => 0,
        'jumlah_unit_per_kemasan' => 1,
        'harga_beli_per_kemas' => 0,
        'harga_beli_per_unit' => 0,
        'harga_jual_per_unit' => 40000,
    ]);

    KartuGudang::create([
        'user_id' => $this->nelayan->id,
        'barang_id' => $ikan->id,
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Tangkapan Awal',
        'diterima' => 50,
        'dikeluarkan' => 0,
        'saldo_persatuan' => 50,
        'saldo_perkemasan' => 50,
    ]);

    $response = $this->actingAs($this->nelayan)->get(route('laporan-keuangan.neraca-awal.create'));

    $response->assertSuccessful();
    $response->assertSee('Persediaan Hasil Tangkapan');
    $response->assertSee('Harga Jual per Kg');
    $response->assertSee('Saldo Akhir (Kg)');
    $response->assertSee('Ikan Tongkol Segar');
});

test('nelayan neraca awal records inventory value based on harga jual per unit times saldo akhir', function () {
    $ikan = Barang::create([
        'user_id' => $this->nelayan->id,
        'kode_barang' => 'IKN-02',
        'nama' => 'Ikan Cakalang',
        'jumlah_max' => 0,
        'jumlah_min' => 0,
        'jumlah_unit_per_kemasan' => 1,
        'harga_beli_per_kemas' => 0,
        'harga_beli_per_unit' => 0,
        'harga_jual_per_unit' => 50000,
    ]);

    KartuGudang::create([
        'user_id' => $this->nelayan->id,
        'barang_id' => $ikan->id,
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Stok Awal Ikan',
        'diterima' => 30,
        'dikeluarkan' => 0,
        'saldo_persatuan' => 30,
        'saldo_perkemasan' => 30,
    ]);

    // Expected persediaan: 30 kg * Rp 50.000 = Rp 1.500.000
    $expectedPersediaan = 30 * 50000;

    $response = $this->actingAs($this->nelayan)->post(route('laporan-keuangan.neraca-awal.store'), [
        'kas' => 10000000,
        'uraian_kas' => 'Kas Awal Nelayan',
        'barang_ids' => [$ikan->id],
        'total_persediaan' => $expectedPersediaan,
        'tanah_bangunan' => 0,
        'kendaraan' => 5000000,
        'meubel_peralatan' => 2000000,
        'tidak_ada_piutang' => '1',
        'tidak_ada_hutang' => '1',
    ]);

    $response->assertRedirect(route('laporan-keuangan.neraca-awal.index'));

    $entry = JournalEntry::where('user_id', $this->nelayan->id)
        ->where('transaction_type', 'neraca_awal')
        ->first();

    expect($entry)->not->toBeNull();

    // Pastikan Akun 1105 (Persediaan Barang) terisi di JournalItem dengan nilai yang tepat
    $itemPersediaan = $entry->items()
        ->whereHas('account', fn ($q) => $q->where('code', '1105'))
        ->first();

    expect($itemPersediaan)->not->toBeNull()
        ->and((float) $itemPersediaan->debit)->toBe((float) $expectedPersediaan);

    // Verifikasi tampilan detail Neraca Awal
    $showResponse = $this->actingAs($this->nelayan)->get(route('laporan-keuangan.neraca-awal.show', $entry->id));
    $showResponse->assertSuccessful();
    $showResponse->assertSee(number_format($expectedPersediaan, 0, ',', '.'));
    $showResponse->assertSee('Neraca Awal seimbang');

    // Verifikasi juga di KeuanganService untuk Neraca Akhir
    $keuangan = app(KeuanganService::class);
    $neraca = $keuangan->hitungNeraca(null, $this->nelayan->id);

    expect($neraca['nilaiPersediaan'])->toBe((float) $expectedPersediaan)
        ->and($neraca['totalAktiva'])->toBe($neraca['totalPasiva']);
});

test('ukm neraca awal still records inventory value based on harga beli per unit times saldo akhir', function () {
    $barang = Barang::create([
        'user_id' => $this->ukm->id,
        'kode_barang' => 'UKM-01',
        'nama' => 'Kerajinan Tas',
        'jumlah_max' => 100,
        'jumlah_min' => 10,
        'jumlah_unit_per_kemasan' => 1,
        'harga_beli_per_kemas' => 25000,
        'harga_beli_per_unit' => 25000,
        'harga_jual_per_unit' => 45000,
    ]);

    KartuGudang::create([
        'user_id' => $this->ukm->id,
        'barang_id' => $barang->id,
        'tanggal' => now()->format('Y-m-d'),
        'uraian' => 'Saldo Awal',
        'diterima' => 40,
        'dikeluarkan' => 0,
        'saldo_persatuan' => 40,
        'saldo_perkemasan' => 40,
    ]);

    // Expected persediaan UKM: 40 unit * Rp 25.000 = Rp 1.000.000
    $expectedPersediaan = 40 * 25000;

    $response = $this->actingAs($this->ukm)->post(route('laporan-keuangan.neraca-awal.store'), [
        'kas' => 5000000,
        'uraian_kas' => 'Kas UKM',
        'barang_ids' => [$barang->id],
        'total_persediaan' => $expectedPersediaan,
        'tanah_bangunan' => 0,
        'kendaraan' => 0,
        'meubel_peralatan' => 0,
        'tidak_ada_piutang' => '1',
        'tidak_ada_hutang' => '1',
    ]);

    $response->assertRedirect(route('laporan-keuangan.neraca-awal.index'));

    $entry = JournalEntry::where('user_id', $this->ukm->id)
        ->where('transaction_type', 'neraca_awal')
        ->first();

    $itemPersediaan = $entry->items()
        ->whereHas('account', fn ($q) => $q->where('code', '1105'))
        ->first();

    expect($itemPersediaan)->not->toBeNull()
        ->and((float) $itemPersediaan->debit)->toBe((float) $expectedPersediaan);
});
