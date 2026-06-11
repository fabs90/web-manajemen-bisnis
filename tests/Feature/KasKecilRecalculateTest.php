<?php

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\KasKecil;
use App\Models\User;
use App\Services\PengeluaranService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('recalculates petty cash balance when a record is deleted', function () {
    $user = User::factory()->create(['is_verified' => true]);

    Account::create([
        'user_id' => $user->id,
        'code' => '1101',
        'name' => 'Kas Utama',
        'category' => 'asset',
        'normal_balance' => 'debit',
    ]);
    Account::create([
        'user_id' => $user->id,
        'code' => '1102',
        'name' => 'Kas Kecil',
        'category' => 'asset',
        'normal_balance' => 'debit',
    ]);
    Account::create([
        'user_id' => $user->id,
        'code' => '5202',
        'name' => 'Beban Lain-lain',
        'category' => 'expense',
        'normal_balance' => 'debit',
    ]);

    $service = app(PengeluaranService::class);

    // Create 3 Petty Cash replenishment transactions
    // 1. First Replenishment: 100,000
    $service->store([
        'uraian_pengeluaran' => 'Pengisian ke-1',
        'jumlah' => 100000,
        'jumlah_manual' => 100000,
        'tanggal' => '2026-06-01',
        'jenis_pengeluaran' => 'tunai',
        'jenis_keperluan' => 'kas_kecil',
    ], $user->id);

    // 2. Second Replenishment: 150,000
    $service->store([
        'uraian_pengeluaran' => 'Pengisian ke-2',
        'jumlah' => 150000,
        'jumlah_manual' => 150000,
        'tanggal' => '2026-06-02',
        'jenis_pengeluaran' => 'tunai',
        'jenis_keperluan' => 'kas_kecil',
    ], $user->id);

    // 3. Third Replenishment: 50,000
    $service->store([
        'uraian_pengeluaran' => 'Pengisian ke-3',
        'jumlah' => 50000,
        'jumlah_manual' => 50000,
        'tanggal' => '2026-06-03',
        'jenis_pengeluaran' => 'tunai',
        'jenis_keperluan' => 'kas_kecil',
    ], $user->id);

    // Verify initial state
    $records = KasKecil::where('user_id', $user->id)->orderBy('id', 'asc')->get();
    expect($records)->toHaveCount(3);
    expect($records[0]->saldo_akhir)->toEqual(100000);
    expect($records[1]->saldo_akhir)->toEqual(250000);
    expect($records[2]->saldo_akhir)->toEqual(300000);

    // Get the second record and its corresponding JournalEntry ID
    $middleRecord = $records[1];
    $entry = JournalEntry::where('reference_number', $middleRecord->nomor_referensi)->firstOrFail();

    // Act: Delete the middle replenishment via the service destroy method (which is called by the controller)
    $service->destroy($entry->id, $user->id);

    // Assert: The middle record should be deleted
    expect(KasKecil::find($middleRecord->id))->toBeNull();

    // Assert: The third record's saldo_akhir should be recalculated to: 100,000 + 50,000 = 150,000
    $remainingRecords = KasKecil::where('user_id', $user->id)->orderBy('id', 'asc')->get();
    expect($remainingRecords)->toHaveCount(2);
    expect($remainingRecords[0]->saldo_akhir)->toEqual(100000);
    expect($remainingRecords[1]->saldo_akhir)->toEqual(150000); // 100,000 + 50,000
});
