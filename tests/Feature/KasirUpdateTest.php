<?php

use App\Models\Account;
use App\Models\Barang;
use App\Models\JenisPembayaran;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\KartuGudang;
use App\Models\KasirTransactionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function setupKasirTestContext(): array
{
    $user = User::factory()->create([
        'is_verified' => true,
        'alamat' => 'Jl. Merdeka No. 1',
        'nomor_telepon' => '081234567890',
    ]);

    $accounts = [
        '1101' => Account::create([
            'user_id' => $user->id,
            'code' => '1101',
            'name' => 'Kas Utama',
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
        '4102' => Account::create([
            'user_id' => $user->id,
            'code' => '4102',
            'name' => 'Potongan Penjualan',
            'category' => 'revenue',
            'normal_balance' => 'debit',
        ]),
        '5101' => Account::create([
            'user_id' => $user->id,
            'code' => '5101',
            'name' => 'Beban Operasional',
            'category' => 'expense',
            'normal_balance' => 'debit',
        ]),
    ];

    $tunai = JenisPembayaran::create(['nama' => 'Tunai']);
    $qris = JenisPembayaran::create(['nama' => 'QRIS']);

    $barang = Barang::create([
        'user_id' => $user->id,
        'kode_barang' => 'BRG-001',
        'nama' => 'Produk A',
        'satuan' => 'pcs',
        'kemasan' => 'dus',
        'jumlah_unit_per_kemasan' => 10,
        'harga_beli_per_unit' => 5000,
        'harga_jual_per_unit' => 10000,
    ]);

    KartuGudang::create([
        'user_id' => $user->id,
        'barang_id' => $barang->id,
        'tanggal' => '2026-06-01',
        'diterima' => 20,
        'dikeluarkan' => 0,
        'uraian' => 'Stok Awal',
        'saldo_persatuan' => 20,
        'saldo_perkemasan' => 2,
    ]);

    $entry = JournalEntry::create([
        'user_id' => $user->id,
        'reference_number' => 'TRX-20260601-ABC123',
        'date' => '2026-06-01',
        'description' => 'Penjualan Tunai Kasir: TRX-20260601-ABC123',
        'transaction_type' => 'pendapatan_tunai',
    ]);

    $entry->items()->create([
        'user_id' => $user->id,
        'account_id' => $accounts['1101']->id,
        'debit' => 20000,
        'credit' => 0,
    ]);
    $entry->items()->create([
        'user_id' => $user->id,
        'account_id' => $accounts['4101']->id,
        'debit' => 0,
        'credit' => 20000,
    ]);

    KartuGudang::create([
        'user_id' => $user->id,
        'barang_id' => $barang->id,
        'journal_entry_id' => $entry->id,
        'tanggal' => '2026-06-01',
        'diterima' => 0,
        'dikeluarkan' => 2,
        'uraian' => 'Pendapatan Kasir Tunai: Produk A',
        'saldo_persatuan' => 18,
        'saldo_perkemasan' => 2,
    ]);

    $log = KasirTransactionLog::create([
        'user_id' => $user->id,
        'journal_entry_id' => $entry->id,
        'uraian' => 'Penjualan Kasir - Pembayaran: Tunai',
        'tanggal_transaksi' => '2026-06-01',
        'bayar' => 25000,
        'kembalian' => 5000,
        'jumlah' => 20000,
        'diskon' => 0,
    ]);

    return compact('user', 'accounts', 'tunai', 'qris', 'barang', 'entry', 'log');
}

it('shows edit button on kasir index and renders kasir edit page', function () {
    $ctx = setupKasirTestContext();

    $this->actingAs($ctx['user'])
        ->get(route('keuangan.kasir.index'))
        ->assertSuccessful()
        ->assertSee(route('keuangan.kasir.edit', $ctx['log']->id));

    $this->actingAs($ctx['user'])
        ->get(route('keuangan.kasir.edit', $ctx['log']->id))
        ->assertSuccessful()
        ->assertSee('Produk A')
        ->assertSee($ctx['entry']->reference_number);
});

it('updates kasir transaction and recalculates kartu gudang stock and journal balances', function () {
    $ctx = setupKasirTestContext();

    $response = $this->actingAs($ctx['user'])->put(route('keuangan.kasir.update', $ctx['log']->id), [
        'jenis_pembayaran_id' => $ctx['qris']->id,
        'id_barang_terjual' => [$ctx['barang']->id],
        'jumlah_barang_dijual' => [5],
        'diskon_total' => 5000,
        'grand_total' => 45000,
        'uang_bayar' => 45000,
        'uang_kembalian' => 0,
    ]);

    $response->assertRedirect(route('keuangan.kasir.index'))
        ->assertSessionHas('success');

    $ctx['log']->refresh();
    expect((float) $ctx['log']->jumlah)->toBe(45000.0);
    expect((float) $ctx['log']->diskon)->toBe(5000.0);
    expect($ctx['log']->uraian)->toContain('QRIS');

    $kasSaldo = (float) JournalItem::where('journal_entry_id', $ctx['entry']->id)
        ->where('account_id', $ctx['accounts']['1101']->id)
        ->sum('debit');
    $diskonSaldo = (float) JournalItem::where('journal_entry_id', $ctx['entry']->id)
        ->where('account_id', $ctx['accounts']['4102']->id)
        ->sum('debit');
    $pendapatanSaldo = (float) JournalItem::where('journal_entry_id', $ctx['entry']->id)
        ->where('account_id', $ctx['accounts']['4101']->id)
        ->sum('credit');

    expect($kasSaldo)->toBe(45000.0);
    expect($diskonSaldo)->toBe(5000.0);
    expect($pendapatanSaldo)->toBe(50000.0);

    $latestKartu = KartuGudang::where('barang_id', $ctx['barang']->id)->latest('id')->first();
    expect((float) $latestKartu->saldo_persatuan)->toBe(15.0);
});

it('redirects pendapatan edit to kasir edit when entry has kasir log', function () {
    $ctx = setupKasirTestContext();

    $this->actingAs($ctx['user'])
        ->get(route('keuangan.pendapatan.edit', $ctx['entry']->id))
        ->assertRedirect(route('keuangan.kasir.edit', $ctx['log']->id));
});

it('allows editing pengeluaran from another module like pemesanan-barang', function () {
    $ctx = setupKasirTestContext();

    $sppEntry = JournalEntry::create([
        'user_id' => $ctx['user']->id,
        'reference_number' => 'SPP-20260601-001',
        'date' => '2026-06-01',
        'description' => 'Pesanan Pembelian - Supplier X',
        'transaction_type' => 'pemesanan-barang',
    ]);

    $sppEntry->items()->create([
        'user_id' => $ctx['user']->id,
        'account_id' => $ctx['accounts']['5101']->id,
        'debit' => 100000,
        'credit' => 0,
    ]);
    $sppEntry->items()->create([
        'user_id' => $ctx['user']->id,
        'account_id' => $ctx['accounts']['1101']->id,
        'debit' => 0,
        'credit' => 100000,
    ]);

    $this->actingAs($ctx['user'])
        ->get(route('keuangan.pengeluaran.show', $sppEntry->id))
        ->assertSuccessful()
        ->assertSee(route('keuangan.pengeluaran.edit', $sppEntry->id));

    $this->actingAs($ctx['user'])
        ->get(route('keuangan.pengeluaran.edit', $sppEntry->id))
        ->assertSuccessful();

    $this->actingAs($ctx['user'])
        ->put(route('keuangan.pengeluaran.update', $sppEntry->id), [
            'uraian_pengeluaran' => 'SPP Diedit',
            'tanggal' => '2026-06-02',
            'jenis_keperluan' => 'lain_lain',
            'jenis_pengeluaran' => 'tunai',
            'jumlah_manual' => 120000,
            'biaya_lain' => 0,
        ])
        ->assertRedirect(route('keuangan.pengeluaran.list'))
        ->assertSessionHas('success');

    $sppEntry->refresh();
    expect($sppEntry->description)->toContain('SPP Diedit');
});
