<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\Pelanggan;
use App\Models\User;
use App\Services\SuratPesananPembelianService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SuratPesananPembelianJournalTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_sales_journal_entry_when_storing_a_purchase_order()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create required accounts
        Account::create(['user_id' => $user->id, 'code' => '1104', 'name' => 'Piutang Usaha', 'category' => 'asset', 'normal_balance' => 'debit', 'requires_sub_ledger' => true, 'is_active' => true]);
        Account::create(['user_id' => $user->id, 'code' => '1105', 'name' => 'Persediaan Barang Dagang', 'category' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
        Account::create(['user_id' => $user->id, 'code' => '4101', 'name' => 'Pendapatan Penjualan', 'category' => 'revenue', 'normal_balance' => 'credit', 'is_active' => true]);
        Account::create(['user_id' => $user->id, 'code' => '5101', 'name' => 'Harga Pokok Penjualan (HPP)', 'category' => 'expense', 'normal_balance' => 'debit', 'is_active' => true]);

        $pelanggan = Pelanggan::create([
            'nama' => 'Customer Test',
            'user_id' => $user->id,
        ]);

        $barang = Barang::create([
            'nama' => 'Barang A',
            'kode_barang' => 'B001',
            'user_id' => $user->id,
            'harga_beli_per_unit' => 70000,
            'harga_jual_per_unit' => 100000,
        ]);

        $data = [
            'pelanggan_id' => $pelanggan->id,
            'nomor_pesanan_pembelian' => 'PO-001',
            'tanggal_pesanan_pembelian' => now()->format('Y-m-d'),
            'tanggal_kirim_pesanan_pembelian' => now()->addDays(7)->format('Y-m-d'),
            'nama_bagian_pelanggan' => 'Pembelian',
            'email_pelanggan' => 'test@example.com',
            'detail' => [
                [
                    'nama_barang' => 'Barang A',
                    'kuantitas' => '10',
                    'harga' => '100000',
                    'total' => '1000000',
                ]
            ],
        ];

        $request = new Request($data);

        $service = app(SuratPesananPembelianService::class);
        $service->store($request);

        $journalEntry = JournalEntry::where('user_id', $user->id)->first();

        $this->assertNotNull($journalEntry);
        $this->assertEquals('Pesanan Pembelian - PO-001', $journalEntry->description);
        $this->assertEquals('penjualan', $journalEntry->transaction_type);
        $this->assertCount(4, $journalEntry->items);

        // 1. Piutang Usaha (1104) - Debit 1.000.000
        $piutangItem = $journalEntry->items()->whereHas('account', fn($q) => $q->where('code', '1104'))->first();
        $this->assertEquals(1000000, (int)$piutangItem->debit);
        $this->assertEquals($pelanggan->id, $piutangItem->sub_ledger_id);

        // 2. Pendapatan Penjualan (4101) - Credit 1.000.000
        $pendapatanItem = $journalEntry->items()->whereHas('account', fn($q) => $q->where('code', '4101'))->first();
        $this->assertEquals(1000000, (int)$pendapatanItem->credit);

        // 3. HPP (5101) - Debit 700.000 (10 * 70.000)
        $hppItem = $journalEntry->items()->whereHas('account', fn($q) => $q->where('code', '5101'))->first();
        $this->assertEquals(700000, (int)$hppItem->debit);

        // 4. Persediaan (1105) - Credit 700.000
        $persediaanItem = $journalEntry->items()->whereHas('account', fn($q) => $q->where('code', '1105'))->first();
        $this->assertEquals(700000, (int)$persediaanItem->credit);
    }

    public function test_it_deletes_the_journal_entry_when_destroying_a_purchase_order()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create required accounts
        Account::create(['user_id' => $user->id, 'code' => '1104', 'name' => 'Piutang', 'category' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
        Account::create(['user_id' => $user->id, 'code' => '1105', 'name' => 'Persediaan', 'category' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
        Account::create(['user_id' => $user->id, 'code' => '4101', 'name' => 'Pendapatan', 'category' => 'revenue', 'normal_balance' => 'credit', 'is_active' => true]);
        Account::create(['user_id' => $user->id, 'code' => '5101', 'name' => 'HPP', 'category' => 'expense', 'normal_balance' => 'debit', 'is_active' => true]);

        $pelanggan = Pelanggan::create([
            'nama' => 'Customer Test',
            'user_id' => $user->id,
        ]);

        $data = [
            'pelanggan_id' => $pelanggan->id,
            'nomor_pesanan_pembelian' => 'PO-999',
            'tanggal_pesanan_pembelian' => now()->format('Y-m-d'),
            'tanggal_kirim_pesanan_pembelian' => now()->addDays(7)->format('Y-m-d'),
            'nama_bagian_pelanggan' => 'Pembelian',
            'email_pelanggan' => 'test@example.com',
            'detail' => [
                [
                    'nama_barang' => 'Barang A',
                    'kuantitas' => '10',
                    'harga' => '100000',
                    'total' => '1000000',
                ]
            ],
        ];

        $request = new Request($data);

        $service = app(SuratPesananPembelianService::class);
        $spp = $service->store($request);

        $this->assertDatabaseHas('journal_entries', [
            'description' => 'Pesanan Pembelian - PO-999',
        ]);

        $service->destroy($spp->id);

        $this->assertDatabaseMissing('journal_entries', [
            'description' => 'Pesanan Pembelian - PO-999',
        ]);
    }
}
