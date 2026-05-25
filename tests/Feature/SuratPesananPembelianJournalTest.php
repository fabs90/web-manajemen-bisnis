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

    public function test_it_creates_a_purchase_journal_entry_when_storing_a_purchase_order()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create required accounts
        Account::create(['user_id' => $user->id, 'code' => '1105', 'name' => 'Persediaan Barang Dagang', 'category' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
        Account::create(['user_id' => $user->id, 'code' => '2101', 'name' => 'Utang Usaha', 'category' => 'liability', 'normal_balance' => 'credit', 'requires_sub_ledger' => true, 'is_active' => true]);

        $supplier = Pelanggan::create([
            'nama' => 'Supplier Test',
            'user_id' => $user->id,
            'jenis' => 'supplier',
        ]);

        $barang = Barang::create([
            'nama' => 'Barang A',
            'kode_barang' => 'B001',
            'user_id' => $user->id,
            'harga_beli_per_unit' => 70000,
            'harga_jual_per_unit' => 100000,
        ]);

        $data = [
            'supplier_id' => $supplier->id,
            'nomor_pesanan_pembelian' => 'PO-001',
            'tanggal_pesanan_pembelian' => now()->format('Y-m-d'),
            'tanggal_kirim_pesanan_pembelian' => now()->addDays(7)->format('Y-m-d'),
            'nama_pimpinan_supplier' => 'Pimpinan Supplier',
            'email_supplier' => 'test@example.com',
            'detail' => [
                [
                    'nama_barang' => 'Barang A',
                    'kuantitas' => '10',
                    'harga' => '100000',
                    'total' => '1000000',
                ],
            ],
        ];

        $request = new Request($data);

        $service = app(SuratPesananPembelianService::class);
        $service->store($request);

        $journalEntry = JournalEntry::where('user_id', $user->id)->first();

        $this->assertNotNull($journalEntry);
        $this->assertEquals('Pesanan Pembelian - PO-001', $journalEntry->description);
        $this->assertEquals('pemesanan-barang', $journalEntry->transaction_type);
        $this->assertCount(2, $journalEntry->items);

        // 1. Debit: Persediaan (1105)
        $persediaanItem = $journalEntry->items()->whereHas('account', fn ($q) => $q->where('code', '1105'))->first();
        $this->assertEquals(1000000, (int) $persediaanItem->debit);

        // 2. Credit: Utang Usaha (2101)
        $utangItem = $journalEntry->items()->whereHas('account', fn ($q) => $q->where('code', '2101'))->first();
        $this->assertEquals(1000000, (int) $utangItem->credit);
        $this->assertEquals($supplier->id, $utangItem->sub_ledger_id);
    }

    public function test_it_deletes_the_journal_entry_when_destroying_a_purchase_order()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create required accounts
        Account::create(['user_id' => $user->id, 'code' => '1105', 'name' => 'Persediaan', 'category' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
        Account::create(['user_id' => $user->id, 'code' => '2101', 'name' => 'Utang', 'category' => 'liability', 'normal_balance' => 'credit', 'is_active' => true]);

        $supplier = Pelanggan::create([
            'nama' => 'Supplier Test',
            'user_id' => $user->id,
            'jenis' => 'supplier',
        ]);

        $data = [
            'supplier_id' => $supplier->id,
            'nomor_pesanan_pembelian' => 'PO-999',
            'tanggal_pesanan_pembelian' => now()->format('Y-m-d'),
            'tanggal_kirim_pesanan_pembelian' => now()->addDays(7)->format('Y-m-d'),
            'nama_pimpinan_supplier' => 'Pimpinan Supplier',
            'email_supplier' => 'test@example.com',
            'detail' => [
                [
                    'nama_barang' => 'Barang A',
                    'kuantitas' => '10',
                    'harga' => '100000',
                    'total' => '1000000',
                ],
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
