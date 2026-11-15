<?php

use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\KartuGudang;
use App\Models\KasKecil;
use App\Models\Pelanggan;
use App\Models\User;
use App\Services\PengeluaranService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createVerifiedPengeluaranUser(): User
{
    return User::factory()->create([
        'is_verified' => true,
        'alamat' => 'Jl. Merdeka No. 1',
        'nomor_telepon' => '081234567890',
    ]);
}

function setupPengeluaranAccounts(User $user): array
{
    return [
        '1101' => Account::create([
            'user_id' => $user->id,
            'code' => '1101',
            'name' => 'Kas Utama',
            'category' => 'asset',
            'normal_balance' => 'debit',
        ]),
        '1102' => Account::create([
            'user_id' => $user->id,
            'code' => '1102',
            'name' => 'Kas Kecil',
            'category' => 'asset',
            'normal_balance' => 'debit',
        ]),
        '1105' => Account::create([
            'user_id' => $user->id,
            'code' => '1105',
            'name' => 'Persediaan Barang',
            'category' => 'asset',
            'normal_balance' => 'debit',
        ]),
        '2101' => Account::create([
            'user_id' => $user->id,
            'code' => '2101',
            'name' => 'Utang Usaha',
            'category' => 'liability',
            'normal_balance' => 'credit',
        ]),
        '5202' => Account::create([
            'user_id' => $user->id,
            'code' => '5202',
            'name' => 'Beban Lain-lain',
            'category' => 'expense',
            'normal_balance' => 'debit',
        ]),
    ];
}

it('updates pengeluaran lain_lain and recalculates kas & expense balances', function () {
    $user = createVerifiedPengeluaranUser();
    $accounts = setupPengeluaranAccounts($user);
    $service = app(PengeluaranService::class);

    $service->store([
        'uraian_pengeluaran' => 'Beli ATK',
        'jumlah' => 100000,
        'jumlah_manual' => 100000,
        'tanggal' => '2026-06-01',
        'jenis_pengeluaran' => 'tunai',
        'jenis_keperluan' => 'lain_lain',
    ], $user->id);

    $entry = JournalEntry::where('user_id', $user->id)->firstOrFail();

    $this->actingAs($user)
        ->get(route('keuangan.pengeluaran.show', $entry->id))
        ->assertSuccessful()
        ->assertSee(route('keuangan.pengeluaran.edit', $entry->id));

    $this->actingAs($user)
        ->get(route('keuangan.pengeluaran.edit', $entry->id))
        ->assertSuccessful();

    $response = $this->actingAs($user)->put(route('keuangan.pengeluaran.update', $entry->id), [
        'uraian_pengeluaran' => 'Beli ATK Revisi',
        'jumlah' => 175000,
        'jumlah_manual' => 180000,
        'potongan_pembelian' => 10000,
        'biaya_lain' => 5000,
        'admin_bank' => 0,
        'tanggal' => '2026-06-02',
        'jenis_pengeluaran' => 'tunai',
        'jenis_keperluan' => 'lain_lain',
    ]);

    $response->assertRedirect(route('keuangan.pengeluaran.list'));

    $entry->refresh();
    expect($entry->description)->toBe('Pengeluaran lain-lain: Beli ATK Revisi');

    $kasCredit = (float) JournalItem::where('user_id', $user->id)
        ->where('account_id', $accounts['1101']->id)
        ->sum('credit');

    $expenseNet = (float) JournalItem::where('user_id', $user->id)
        ->where('account_id', $accounts['5202']->id)
        ->selectRaw('SUM(debit - credit) as total')
        ->value('total');

    expect($kasCredit)->toBe(175000.0);
    expect($expenseNet)->toBe(175000.0);
});

it('updates membeli_barang and recalculates kartu gudang balances', function () {
    $user = createVerifiedPengeluaranUser();
    $accounts = setupPengeluaranAccounts($user);
    $service = app(PengeluaranService::class);

    $barang = Barang::create([
        'user_id' => $user->id,
        'kode_barang' => 'BRG-01',
        'nama' => 'Beras Premium',
        'jumlah_unit_per_kemasan' => 10,
        'harga_beli_per_unit' => 15000,
        'harga_jual_per_unit' => 20000,
    ]);

    // Purchase 1: 20 units
    $service->store([
        'uraian_pengeluaran' => 'Stok awal beras',
        'jumlah' => 300000,
        'tanggal' => '2026-06-01',
        'jenis_pengeluaran' => 'tunai',
        'jenis_keperluan' => 'membeli_barang',
        'barang_dibeli' => [$barang->id],
        'jumlah_barang_dibeli' => [20],
    ], $user->id);

    $firstEntry = JournalEntry::where('user_id', $user->id)->latest('id')->firstOrFail();

    // Purchase 2: 10 units (subsequent record whose running balance must be recalculated)
    $service->store([
        'uraian_pengeluaran' => 'Tambah stok beras',
        'jumlah' => 150000,
        'tanggal' => '2026-06-02',
        'jenis_pengeluaran' => 'tunai',
        'jenis_keperluan' => 'membeli_barang',
        'barang_dibeli' => [$barang->id],
        'jumlah_barang_dibeli' => [10],
    ], $user->id);

    // Update Purchase 1 from 20 units -> 35 units
    $response = $this->actingAs($user)->put(route('keuangan.pengeluaran.update', $firstEntry->id), [
        'uraian_pengeluaran' => 'Stok awal beras revisi',
        'jumlah' => 525000,
        'tanggal' => '2026-06-01',
        'jenis_pengeluaran' => 'tunai',
        'jenis_keperluan' => 'membeli_barang',
        'barang_dibeli' => [$barang->id],
        'jumlah_barang_dibeli' => [35],
    ]);

    $response->assertRedirect(route('keuangan.pengeluaran.list'));

    $kartuRows = KartuGudang::where('barang_id', $barang->id)->orderBy('id', 'asc')->get();
    expect($kartuRows)->toHaveCount(2);
    // Purchase 2 (still in DB first) + recreated Purchase 1: total should be 10 + 35 = 45
    expect((int) $kartuRows->last()->saldo_persatuan)->toBe(45);
    expect((int) $kartuRows->last()->saldo_perkemasan)->toBe(5);

    $persediaanTotal = (float) JournalItem::where('user_id', $user->id)
        ->where('account_id', $accounts['1105']->id)
        ->sum('debit');
    expect($persediaanTotal)->toBe(675000.0); // 525,000 + 150,000
});

it('updates kas_kecil replenishment and recalculates subsequent petty cash balances', function () {
    $user = createVerifiedPengeluaranUser();
    setupPengeluaranAccounts($user);
    $service = app(PengeluaranService::class);

    $service->store([
        'uraian_pengeluaran' => 'Pengisian 1',
        'jumlah' => 100000,
        'jumlah_manual' => 100000,
        'tanggal' => '2026-06-01',
        'jenis_pengeluaran' => 'tunai',
        'jenis_keperluan' => 'kas_kecil',
    ], $user->id);

    $service->store([
        'uraian_pengeluaran' => 'Pengisian 2',
        'jumlah' => 150000,
        'jumlah_manual' => 150000,
        'tanggal' => '2026-06-02',
        'jenis_pengeluaran' => 'tunai',
        'jenis_keperluan' => 'kas_kecil',
    ], $user->id);

    $service->store([
        'uraian_pengeluaran' => 'Pengisian 3',
        'jumlah' => 50000,
        'jumlah_manual' => 50000,
        'tanggal' => '2026-06-03',
        'jenis_pengeluaran' => 'tunai',
        'jenis_keperluan' => 'kas_kecil',
    ], $user->id);

    $records = KasKecil::where('user_id', $user->id)->orderBy('id', 'asc')->get();
    $middleEntry = JournalEntry::where('reference_number', $records[1]->nomor_referensi)->firstOrFail();

    // Update middle replenishment from 150,000 -> 250,000
    $this->actingAs($user)->put(route('keuangan.pengeluaran.update', $middleEntry->id), [
        'uraian_pengeluaran' => 'Pengisian 2 Revisi',
        'jumlah' => 250000,
        'jumlah_manual' => 250000,
        'tanggal' => '2026-06-02',
        'jenis_pengeluaran' => 'tunai',
        'jenis_keperluan' => 'kas_kecil',
    ])->assertRedirect(route('keuangan.pengeluaran.list'));

    $updatedRecords = KasKecil::where('user_id', $user->id)->orderBy('id', 'asc')->get();
    expect((float) $updatedRecords[0]->saldo_akhir)->toBe(100000.0);
    expect((float) $updatedRecords[1]->saldo_akhir)->toBe(350000.0);
    expect((float) $updatedRecords[2]->saldo_akhir)->toBe(400000.0);
});

it('updates membayar_hutang without falsely failing against its own previous payment', function () {
    $user = createVerifiedPengeluaranUser();
    $accounts = setupPengeluaranAccounts($user);
    $service = app(PengeluaranService::class);

    $kreditur = Pelanggan::create([
        'user_id' => $user->id,
        'nama' => 'Supplier Jaya',
        'kontak' => '08129876543',
        'jenis' => 'kreditur',
    ]);

    // Initial debt of 400,000
    $hutangEntry = JournalEntry::create([
        'user_id' => $user->id,
        'reference_number' => 'EXP-20260601-HUT001',
        'date' => '2026-06-01',
        'description' => 'Hutang Supplier Jaya',
        'transaction_type' => 'lain_lain',
    ]);
    $hutangEntry->items()->create([
        'user_id' => $user->id,
        'account_id' => $accounts['5202']->id,
        'debit' => 400000,
        'credit' => 0,
    ]);
    $hutangItem = $hutangEntry->items()->create([
        'user_id' => $user->id,
        'account_id' => $accounts['2101']->id,
        'debit' => 0,
        'credit' => 400000,
        'sub_ledger_type' => Pelanggan::class,
        'sub_ledger_id' => $kreditur->id,
    ]);

    // Full payment of 400,000
    $service->store([
        'uraian_pengeluaran' => 'Bayar lunas',
        'jumlah' => 400000,
        'jumlah_manual' => 400000,
        'tanggal' => '2026-06-02',
        'jenis_pengeluaran' => 'tunai',
        'jenis_keperluan' => 'membayar_hutang',
        'hutang_id' => $hutangItem->id,
    ], $user->id);

    $payEntry = JournalEntry::where('user_id', $user->id)
        ->where('transaction_type', 'membayar_hutang')
        ->firstOrFail();

    // Edit payment down to 250,000
    $this->actingAs($user)->put(route('keuangan.pengeluaran.update', $payEntry->id), [
        'uraian_pengeluaran' => 'Bayar sebagian',
        'jumlah' => 250000,
        'jumlah_manual' => 250000,
        'tanggal' => '2026-06-03',
        'jenis_pengeluaran' => 'tunai',
        'jenis_keperluan' => 'membayar_hutang',
        'hutang_id' => $hutangItem->id,
    ])->assertRedirect(route('keuangan.pengeluaran.list'));

    $remainingDebt = (float) JournalItem::where('user_id', $user->id)
        ->where('account_id', $accounts['2101']->id)
        ->where('sub_ledger_id', $kreditur->id)
        ->selectRaw('SUM(credit - debit) as saldo')
        ->value('saldo');

    expect($remainingDebt)->toBe(150000.0);
});
