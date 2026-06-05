<?php

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not show neraca_awal in daftar penerimaan but keeps it in buku besar piutang', function () {
    $user = User::factory()->create([
        'is_verified' => true,
    ]);

    // Create required accounts
    $kas = Account::create([
        'user_id' => $user->id,
        'code' => '1101',
        'name' => 'Kas',
        'category' => 'asset',
    ]);
    $pendapatan = Account::create([
        'user_id' => $user->id,
        'code' => '4101',
        'name' => 'Pendapatan Penjualan',
        'category' => 'revenue',
    ]);
    $piutang = Account::create([
        'user_id' => $user->id,
        'code' => '1104',
        'name' => 'Piutang Usaha',
        'category' => 'asset',
    ]);

    // 1. Create a Neraca Awal entry (should NOT be in allDatas, but should be in dataPiutang)
    $neracaAwal = JournalEntry::create([
        'user_id' => $user->id,
        'reference_number' => 'NA-001',
        'date' => now()->format('Y-m-d'),
        'description' => 'Saldo Awal',
        'transaction_type' => 'neraca_awal',
    ]);

    // Piutang item for Neraca Awal
    JournalItem::create([
        'user_id' => $user->id,
        'journal_entry_id' => $neracaAwal->id,
        'account_id' => $piutang->id,
        'debit' => 100000,
        'credit' => 0,
    ]);

    // Kas item for Neraca Awal (to simulate cash initial balance)
    JournalItem::create([
        'user_id' => $user->id,
        'journal_entry_id' => $neracaAwal->id,
        'account_id' => $kas->id,
        'debit' => 500000,
        'credit' => 0,
    ]);

    // 2. Create a Normal Pendapatan entry (should be in allDatas)
    $normalPendapatan = JournalEntry::create([
        'user_id' => $user->id,
        'reference_number' => 'PEN-001',
        'date' => now()->format('Y-m-d'),
        'description' => 'Pendapatan Normal',
        'transaction_type' => 'penerimaan_tunai',
    ]);

    // Kas item for Normal Pendapatan
    JournalItem::create([
        'user_id' => $user->id,
        'journal_entry_id' => $normalPendapatan->id,
        'account_id' => $kas->id,
        'debit' => 200000,
        'credit' => 0,
    ]);
    // Pendapatan item
    JournalItem::create([
        'user_id' => $user->id,
        'journal_entry_id' => $normalPendapatan->id,
        'account_id' => $pendapatan->id,
        'debit' => 0,
        'credit' => 200000,
    ]);

    $response = $this->actingAs($user)->get(route('keuangan.pendapatan.list'));

    $response->assertSuccessful();

    $allDatas = $response->viewData('allDatas');
    $dataPiutang = $response->viewData('dataPiutang');

    // Assert that Neraca Awal is NOT in allDatas, but Normal Pendapatan IS
    expect($allDatas->pluck('id')->toArray())
        ->not->toContain($neracaAwal->id)
        ->toContain($normalPendapatan->id);

    // Flatten dataPiutang correctly since it's grouped by sub_ledger_id
    // Wait, the dataPiutang is grouped by sub_ledger_id. The sub_ledger_id is null here.
    // So the key will be an empty string or null.
    $piutangIds = $dataPiutang->flatten(1)->pluck('id')->toArray();

    // Assert that Neraca Awal's JournalItem is in dataPiutang
    $neracaAwalPiutangItem = $neracaAwal->items()->where('account_id', $piutang->id)->first();
    expect($piutangIds)->toContain($neracaAwalPiutangItem->id);
});
