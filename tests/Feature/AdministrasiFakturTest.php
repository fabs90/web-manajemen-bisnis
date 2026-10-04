<?php

use App\Models\Account;
use App\Models\Barang;
use App\Models\Faktur\FakturPenjualan;
use App\Models\Pelanggan;
use App\Models\SPB\SuratPengirimanBarang;
use App\Models\SPB\SuratPengirimanBarangDetail;
use App\Models\SPP\SuratPesananPenjualan;
use App\Models\SPP\SuratPesananPenjualanDetail;
use App\Models\User;

beforeEach(function () {
    $this->userA = User::factory()->create([
        'email' => 'userA@test.com',
        'alamat' => 'Jl. Merdeka No. 1',
        'nomor_telepon' => '081234567890',
        'is_verified' => 1,
    ]);

    $this->userB = User::factory()->create([
        'email' => 'userB@test.com',
        'alamat' => 'Jl. Sudirman No. 2',
        'nomor_telepon' => '089876543210',
        'is_verified' => 1,
    ]);

    // Setup required accounts for User A
    Account::create(['user_id' => $this->userA->id, 'code' => '1104', 'name' => 'Piutang Usaha', 'category' => 'asset', 'normal_balance' => 'debit', 'requires_sub_ledger' => true, 'is_active' => true]);
    Account::create(['user_id' => $this->userA->id, 'code' => '1105', 'name' => 'Persediaan Barang Dagang', 'category' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
    Account::create(['user_id' => $this->userA->id, 'code' => '4101', 'name' => 'Pendapatan Penjualan', 'category' => 'revenue', 'normal_balance' => 'credit', 'is_active' => true]);
    Account::create(['user_id' => $this->userA->id, 'code' => '5101', 'name' => 'Harga Pokok Penjualan', 'category' => 'expense', 'normal_balance' => 'debit', 'is_active' => true]);
});

test('tenant cannot create sales invoice using another tenants spb (IDOR prevention)', function () {
    // Create SPB belonging to User B
    $pelangganB = Pelanggan::create([
        'user_id' => $this->userB->id,
        'nama' => 'Pelanggan Tenant B',
        'kontak' => '081234567891',
        'jenis' => 'debitur',
    ]);

    $sppB = SuratPesananPenjualan::create([
        'user_id' => $this->userB->id,
        'pelanggan_id' => $pelangganB->id,
        'nomor_pesanan_penjualan' => 'SPP-B-001',
        'tanggal_pesanan_penjualan' => now()->format('Y-m-d'),
    ]);

    $spbB = SuratPengirimanBarang::create([
        'user_id' => $this->userB->id,
        'pesanan_penjualan_id' => $sppB->id,
        'nomor_pengiriman_barang' => 'SPB-B-001',
        'tanggal_terima' => now()->format('Y-m-d'),
        'status_pengiriman' => 'terkirim',
        'jenis_pengiriman' => 'langsung',
        'keadaan' => 'baik',
        'nama_penerima' => 'Penerima B',
        'nama_pengirim' => 'Pengirim B',
    ]);

    // Acting as User A, try to invoice User B's SPB
    $response = $this->actingAs($this->userA)->post(route('administrasi.faktur-penjualan.store'), [
        'spb_id' => $spbB->id,
        'kode_faktur' => 'INV-TEST-001',
        'tanggal_faktur' => now()->format('Y-m-d'),
    ]);

    $response->assertSessionHasErrors(['spb_id']);
    expect(FakturPenjualan::count())->toBe(0);
});

test('tenant can create sales invoice for their own spb', function () {
    $pelangganA = Pelanggan::create([
        'user_id' => $this->userA->id,
        'nama' => 'Pelanggan Tenant A',
        'kontak' => '081234567890',
        'jenis' => 'debitur',
    ]);

    $barangA = Barang::create([
        'user_id' => $this->userA->id,
        'nama' => 'Barang A',
        'kode_barang' => 'BRG-A-001',
        'harga_beli_per_unit' => 10000,
        'harga_jual_per_unit' => 15000,
    ]);

    $sppA = SuratPesananPenjualan::create([
        'user_id' => $this->userA->id,
        'pelanggan_id' => $pelangganA->id,
        'nomor_pesanan_penjualan' => 'SPP-A-001',
        'tanggal_pesanan_penjualan' => now()->format('Y-m-d'),
    ]);

    $sppDetailA = SuratPesananPenjualanDetail::create([
        'pesanan_penjualan_id' => $sppA->id,
        'barang_id' => $barangA->id,
        'nama_barang' => $barangA->nama,
        'kuantitas' => 5,
        'harga' => 15000,
        'total' => 75000,
    ]);

    $spbA = SuratPengirimanBarang::create([
        'user_id' => $this->userA->id,
        'pesanan_penjualan_id' => $sppA->id,
        'nomor_pengiriman_barang' => 'SPB-A-001',
        'tanggal_terima' => now()->format('Y-m-d'),
        'status_pengiriman' => 'terkirim',
        'jenis_pengiriman' => 'langsung',
        'keadaan' => 'baik',
        'nama_penerima' => 'Penerima A',
        'nama_pengirim' => 'Pengirim A',
    ]);

    SuratPengirimanBarangDetail::create([
        'spb_id' => $spbA->id,
        'pesanan_penjualan_detail_id' => $sppDetailA->id,
        'jumlah_dikirim' => 5,
    ]);

    $response = $this->actingAs($this->userA)->post(route('administrasi.faktur-penjualan.store'), [
        'spb_id' => $spbA->id,
        'kode_faktur' => 'INV-A-001',
        'tanggal_faktur' => now()->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('administrasi.faktur-penjualan.index'));
    $response->assertSessionHas('success');

    expect(FakturPenjualan::where('kode_faktur', 'INV-A-001')->exists())->toBeTrue();
});

test('cannot create duplicate invoice for the same spb', function () {
    $pelangganA = Pelanggan::create([
        'user_id' => $this->userA->id,
        'nama' => 'Pelanggan Tenant A',
        'kontak' => '081234567890',
        'jenis' => 'debitur',
    ]);

    $barangA = Barang::create([
        'user_id' => $this->userA->id,
        'nama' => 'Barang A',
        'kode_barang' => 'BRG-A-002',
        'harga_beli_per_unit' => 10000,
        'harga_jual_per_unit' => 15000,
    ]);

    $sppA = SuratPesananPenjualan::create([
        'user_id' => $this->userA->id,
        'pelanggan_id' => $pelangganA->id,
        'nomor_pesanan_penjualan' => 'SPP-A-002',
        'tanggal_pesanan_penjualan' => now()->format('Y-m-d'),
    ]);

    $sppDetailA = SuratPesananPenjualanDetail::create([
        'pesanan_penjualan_id' => $sppA->id,
        'barang_id' => $barangA->id,
        'nama_barang' => $barangA->nama,
        'kuantitas' => 2,
        'harga' => 15000,
        'total' => 30000,
    ]);

    $spbA = SuratPengirimanBarang::create([
        'user_id' => $this->userA->id,
        'pesanan_penjualan_id' => $sppA->id,
        'nomor_pengiriman_barang' => 'SPB-A-002',
        'tanggal_terima' => now()->format('Y-m-d'),
        'status_pengiriman' => 'terkirim',
        'jenis_pengiriman' => 'langsung',
        'keadaan' => 'baik',
        'nama_penerima' => 'Penerima A',
        'nama_pengirim' => 'Pengirim A',
    ]);

    SuratPengirimanBarangDetail::create([
        'spb_id' => $spbA->id,
        'pesanan_penjualan_detail_id' => $sppDetailA->id,
        'jumlah_dikirim' => 2,
    ]);

    // First invoice
    $this->actingAs($this->userA)->post(route('administrasi.faktur-penjualan.store'), [
        'spb_id' => $spbA->id,
        'kode_faktur' => 'INV-FIRST',
        'tanggal_faktur' => now()->format('Y-m-d'),
    ]);

    // Second invoice with same SPB should fail validation
    $response = $this->actingAs($this->userA)->post(route('administrasi.faktur-penjualan.store'), [
        'spb_id' => $spbA->id,
        'kode_faktur' => 'INV-SECOND',
        'tanggal_faktur' => now()->format('Y-m-d'),
    ]);

    $response->assertSessionHasErrors(['spb_id']);
});
