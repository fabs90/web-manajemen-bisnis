<?php

use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\Pelanggan;
use App\Models\User;
use App\Services\SuratPesananPenjualanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

it('can store a sales order from a customer', function () {
    $user = User::factory()->create();
    Auth::login($user);

    // Create required accounts
    Account::create(['user_id' => $user->id, 'code' => '1104', 'name' => 'Piutang Usaha', 'category' => 'asset', 'normal_balance' => 'debit', 'requires_sub_ledger' => true, 'is_active' => true]);
    Account::create(['user_id' => $user->id, 'code' => '1105', 'name' => 'Persediaan Barang Dagang', 'category' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
    Account::create(['user_id' => $user->id, 'code' => '4101', 'name' => 'Pendapatan Penjualan', 'category' => 'revenue', 'normal_balance' => 'credit', 'is_active' => true]);
    Account::create(['user_id' => $user->id, 'code' => '5101', 'name' => 'Harga Pokok Penjualan (HPP)', 'category' => 'expense', 'normal_balance' => 'debit', 'is_active' => true]);

    // Create required barang
    $barang = Barang::create([
        'nama' => 'Barang B',
        'kode_barang' => 'B002',
        'user_id' => $user->id,
        'harga_beli_per_unit' => 35000,
        'harga_jual_per_unit' => 50000,
    ]);

    $pelanggan = Pelanggan::create([
        'nama' => 'Pelanggan Test',
        'user_id' => $user->id,
    ]);

    $data = [
        'pelanggan_id' => $pelanggan->id,
        'nomor_pesanan_pembelian' => 'SO-001',
        'tanggal_pesanan_pembelian' => now()->format('Y-m-d'),
        'tanggal_kirim_pesanan_pembelian' => now()->addDays(3)->format('Y-m-d'),
        'nama_pelanggan' => 'Budi',
        'detail' => [
            [
                'nama_barang' => 'Barang B',
                'kuantitas' => '5',
                'harga' => '50000',
                'total' => '250000',
            ],
        ],
    ];

    $request = new Request($data);

    $service = app(SuratPesananPenjualanService::class);
    $spp = $service->storePelanggan($request);

    expect($spp)->not->toBeNull();
    expect($spp->nomor_pesanan_penjualan)->toBe('SO-001');
    expect($spp->pelanggan_id)->toBe($pelanggan->id);
    expect($spp->nama_bagian_pembelian)->toBe('Budi');

    $this->assertDatabaseHas('surat_pesanan_penjualan', [
        'nomor_pesanan_penjualan' => 'SO-001',
        'nama_bagian_pembelian' => 'Budi',
    ]);

    $this->assertDatabaseHas('surat_pesanan_penjualan_detail', [
        'pesanan_penjualan_id' => $spp->id,
        'nama_barang' => 'Barang B',
        'kuantitas' => 5,
        'harga' => 50000.00,
        'total' => 250000.00,
    ]);

    // Assert Kartu Gudang (Pengurangan Stok)
    $this->assertDatabaseHas('kartu_gudang', [
        'barang_id' => $barang->id,
        'diterima' => 0,
        'dikeluarkan' => 5,
        'uraian' => 'Pesanan Penjualan Barang - SO-001',
        'user_id' => $user->id,
    ]);

    // Assert Journal Entry
    $journalEntry = JournalEntry::where('user_id', $user->id)->first();
    expect($journalEntry)->not->toBeNull();
    expect($journalEntry->description)->toBe('Pesanan Penjualan - SO-001');
    expect($journalEntry->transaction_type)->toBe('penjualan');
    expect($journalEntry->items)->toHaveCount(4);

    // 1. Piutang Usaha (1104) - Debit 250.000
    $piutangItem = $journalEntry->items()->whereHas('account', fn ($q) => $q->where('code', '1104'))->first();
    expect((int) $piutangItem->debit)->toBe(250000);
    expect($piutangItem->sub_ledger_id)->toBe($pelanggan->id);

    // 2. Pendapatan Penjualan (4101) - Credit 250.000
    $pendapatanItem = $journalEntry->items()->whereHas('account', fn ($q) => $q->where('code', '4101'))->first();
    expect((int) $pendapatanItem->credit)->toBe(250000);

    // 3. HPP (5101) - Debit 175.000 (5 * 35.000)
    $hppItem = $journalEntry->items()->whereHas('account', fn ($q) => $q->where('code', '5101'))->first();
    expect((int) $hppItem->debit)->toBe(175000);

    // 4. Persediaan (1105) - Credit 175.000
    $persediaanItem = $journalEntry->items()->whereHas('account', fn ($q) => $q->where('code', '1105'))->first();
    expect((int) $persediaanItem->credit)->toBe(175000);
});

it('can destroy a sales order and reverse journal and stock entries', function () {
    $user = User::factory()->create();
    Auth::login($user);

    // Create required accounts
    Account::create(['user_id' => $user->id, 'code' => '1104', 'name' => 'Piutang Usaha', 'category' => 'asset', 'normal_balance' => 'debit', 'requires_sub_ledger' => true, 'is_active' => true]);
    Account::create(['user_id' => $user->id, 'code' => '1105', 'name' => 'Persediaan Barang Dagang', 'category' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
    Account::create(['user_id' => $user->id, 'code' => '4101', 'name' => 'Pendapatan Penjualan', 'category' => 'revenue', 'normal_balance' => 'credit', 'is_active' => true]);
    Account::create(['user_id' => $user->id, 'code' => '5101', 'name' => 'Harga Pokok Penjualan (HPP)', 'category' => 'expense', 'normal_balance' => 'debit', 'is_active' => true]);

    $barang = Barang::create([
        'nama' => 'Barang B',
        'kode_barang' => 'B002',
        'user_id' => $user->id,
        'harga_beli_per_unit' => 35000,
        'harga_jual_per_unit' => 50000,
    ]);

    $pelanggan = Pelanggan::create([
        'nama' => 'Pelanggan Test',
        'user_id' => $user->id,
    ]);

    $data = [
        'pelanggan_id' => $pelanggan->id,
        'nomor_pesanan_pembelian' => 'SO-999',
        'tanggal_pesanan_pembelian' => now()->format('Y-m-d'),
        'tanggal_kirim_pesanan_pembelian' => now()->addDays(3)->format('Y-m-d'),
        'nama_pelanggan' => 'Budi',
        'detail' => [
            [
                'nama_barang' => 'Barang B',
                'kuantitas' => '5',
                'harga' => '50000',
                'total' => '250000',
            ],
        ],
    ];

    $request = new Request($data);

    $service = app(SuratPesananPenjualanService::class);
    $spp = $service->storePelanggan($request);

    $this->assertDatabaseHas('surat_pesanan_penjualan', [
        'nomor_pesanan_penjualan' => 'SO-999',
    ]);

    // Destroy it
    $service->destroy($spp->id);

    $this->assertDatabaseMissing('surat_pesanan_penjualan', [
        'nomor_pesanan_penjualan' => 'SO-999',
    ]);

    // Assert that the journal entry was deleted
    $this->assertDatabaseMissing('journal_entries', [
        'description' => 'Pesanan Penjualan - SO-999',
    ]);

    // Assert stock is restored (we should have a reversal entry in kartu_gudang showing "diterima" = 5)
    $this->assertDatabaseHas('kartu_gudang', [
        'barang_id' => $barang->id,
        'diterima' => 5,
        'dikeluarkan' => 0,
        'uraian' => 'Pembatalan Pesanan Penjualan - SO-999',
        'user_id' => $user->id,
    ]);
});
