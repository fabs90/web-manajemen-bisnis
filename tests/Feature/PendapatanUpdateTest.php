<?php

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createVerifiedUser(): User
{
    return User::factory()->create([
        'is_verified' => true,
        'alamat' => 'Jl. Merdeka No. 1',
        'nomor_telepon' => '081234567890',
    ]);
}

function setupPendapatanAccounts(User $user): array
{
    return [
        '1101' => Account::create([
            'user_id' => $user->id,
            'code' => '1101',
            'name' => 'Kas Utama',
            'category' => 'asset',
            'normal_balance' => 'debit',
        ]),
        '1104' => Account::create([
            'user_id' => $user->id,
            'code' => '1104',
            'name' => 'Piutang Usaha',
            'category' => 'asset',
            'normal_balance' => 'debit',
        ]),
        '4101' => Account::create([
            'user_id' => $user->id,
            'code' => '4101',
            'name' => 'Pendapatan Penjualan',
            'category' => 'revenue',
            'normal_balance' => 'credit',
        ]),
    ];
}

it('updates pendapatan_lain and recalculates journal balances', function () {
    $user = createVerifiedUser();
    $accounts = setupPendapatanAccounts($user);

    $entry = JournalEntry::create([
        'user_id' => $user->id,
        'reference_number' => 'PEND-20260601-ABC123',
        'date' => '2026-06-01',
        'description' => 'Pendapatan awal',
        'transaction_type' => 'pendapatan_lain',
    ]);

    $entry->items()->create([
        'user_id' => $user->id,
        'account_id' => $accounts['1101']->id,
        'debit' => 100000,
        'credit' => 0,
    ]);
    $entry->items()->create([
        'user_id' => $user->id,
        'account_id' => $accounts['4101']->id,
        'debit' => 0,
        'credit' => 100000,
    ]);

    $this->actingAs($user)
        ->get(route('keuangan.pendapatan.show', $entry->id))
        ->assertSuccessful()
        ->assertSee(route('keuangan.pendapatan.edit', $entry->id));

    $this->actingAs($user)
        ->get(route('keuangan.pendapatan.edit', $entry->id))
        ->assertSuccessful();

    $response = $this->actingAs($user)->put(route('keuangan.pendapatan.update', $entry->id), [
        'uraian_pendapatan' => 'Pendapatan setelah revisi',
        'tanggal' => '2026-06-05',
        'jenis_pendapatan' => 'lain_lain',
        'jumlah' => '250.000',
        'biaya_lain' => '50.000',
    ]);

    $response->assertRedirect(route('keuangan.pendapatan.list'));

    $entry->refresh();
    expect($entry->description)->toBe('Pendapatan setelah revisi');
    expect($entry->date->format('Y-m-d'))->toBe('2026-06-05');

    $kasBalance = (float) JournalItem::where('user_id', $user->id)
        ->where('account_id', $accounts['1101']->id)
        ->selectRaw('SUM(debit - credit) as saldo')
        ->value('saldo');

    $revenueBalance = (float) JournalItem::where('user_id', $user->id)
        ->where('account_id', $accounts['4101']->id)
        ->selectRaw('SUM(credit - debit) as saldo')
        ->value('saldo');

    expect($kasBalance)->toBe(300000.0);
    expect($revenueBalance)->toBe(300000.0);
});

it('updates pelunasan piutang without falsely failing against its own previous payment', function () {
    $user = createVerifiedUser();
    $accounts = setupPendapatanAccounts($user);

    $debitur = Pelanggan::create([
        'user_id' => $user->id,
        'nama' => 'Toko Maju',
        'kontak' => '08123456789',
        'jenis' => 'debitur',
    ]);

    // Initial receivable of 500,000
    $piutangEntry = JournalEntry::create([
        'user_id' => $user->id,
        'reference_number' => 'PEND-20260601-PIU001',
        'date' => '2026-06-01',
        'description' => 'Piutang Toko Maju',
        'transaction_type' => 'pendapatan_tunai',
    ]);
    $piutangEntry->items()->create([
        'user_id' => $user->id,
        'account_id' => $accounts['1104']->id,
        'debit' => 500000,
        'credit' => 0,
        'sub_ledger_type' => Pelanggan::class,
        'sub_ledger_id' => $debitur->id,
    ]);
    $piutangEntry->items()->create([
        'user_id' => $user->id,
        'account_id' => $accounts['4101']->id,
        'debit' => 0,
        'credit' => 500000,
    ]);

    // Existing full payment of 500,000
    $pelunasanEntry = JournalEntry::create([
        'user_id' => $user->id,
        'reference_number' => 'PEND-20260602-PAY001',
        'date' => '2026-06-02',
        'description' => 'Pelunasan Toko Maju',
        'transaction_type' => 'pendapatan_tunai',
    ]);
    $pelunasanEntry->items()->create([
        'user_id' => $user->id,
        'account_id' => $accounts['1101']->id,
        'debit' => 500000,
        'credit' => 0,
    ]);
    $pelunasanEntry->items()->create([
        'user_id' => $user->id,
        'account_id' => $accounts['1104']->id,
        'debit' => 0,
        'credit' => 500000,
        'sub_ledger_type' => Pelanggan::class,
        'sub_ledger_id' => $debitur->id,
    ]);

    // Edit payment from 500,000 down to 350,000
    $response = $this->actingAs($user)->put(route('keuangan.pendapatan.update', $pelunasanEntry->id), [
        'uraian_pendapatan' => 'Pelunasan sebagian Toko Maju',
        'tanggal' => '2026-06-03',
        'jenis_pendapatan' => 'kredit',
        'nama_pelanggan' => $debitur->id,
        'jumlah' => 0,
        'jumlah_kredit' => 350000,
    ]);

    $response->assertRedirect(route('keuangan.pendapatan.list'));

    $piutangSaldo = (float) JournalItem::where('user_id', $user->id)
        ->where('account_id', $accounts['1104']->id)
        ->where('sub_ledger_id', $debitur->id)
        ->selectRaw('SUM(debit - credit) as saldo')
        ->value('saldo');

    $kasSaldo = (float) JournalItem::where('user_id', $user->id)
        ->where('account_id', $accounts['1101']->id)
        ->selectRaw('SUM(debit - credit) as saldo')
        ->value('saldo');

    expect($piutangSaldo)->toBe(150000.0);
    expect($kasSaldo)->toBe(350000.0);
});

it('allows editing kasir transactions from pendapatan menu', function () {
    $user = createVerifiedUser();
    $accounts = setupPendapatanAccounts($user);

    $kasirEntry = JournalEntry::create([
        'user_id' => $user->id,
        'reference_number' => 'KASIR-20260601-001',
        'date' => '2026-06-01',
        'description' => 'Penjualan Kasir',
        'transaction_type' => 'pendapatan_tunai',
    ]);
    $kasirEntry->items()->create([
        'user_id' => $user->id,
        'account_id' => $accounts['1101']->id,
        'debit' => 50000,
        'credit' => 0,
    ]);
    $kasirEntry->items()->create([
        'user_id' => $user->id,
        'account_id' => $accounts['4101']->id,
        'debit' => 0,
        'credit' => 50000,
    ]);

    $this->actingAs($user)
        ->get(route('keuangan.pendapatan.show', $kasirEntry->id))
        ->assertSuccessful()
        ->assertSee(route('keuangan.pendapatan.edit', $kasirEntry->id));

    $this->actingAs($user)
        ->get(route('keuangan.pendapatan.edit', $kasirEntry->id))
        ->assertSuccessful();

    $this->actingAs($user)
        ->put(route('keuangan.pendapatan.update', $kasirEntry->id), [
            'uraian_pendapatan' => 'Penjualan Kasir Edited',
            'tanggal' => '2026-06-02',
            'jenis_pendapatan' => 'lain_lain',
            'jumlah' => 75000,
            'biaya_lain' => 0,
        ])
        ->assertRedirect(route('keuangan.pendapatan.list'))
        ->assertSessionHas('success');

    $kasirEntry->refresh();
    expect($kasirEntry->description)->toBe('Penjualan Kasir Edited');
});
