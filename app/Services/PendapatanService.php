<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Faktur\FakturPenjualan;
use App\Models\JournalEntry;
use App\Models\KartuGudang;
use App\Models\SPB\SuratPengirimanBarang;
use App\Models\SPB\SuratPengirimanBarangDetail;
use App\Models\SPP\SuratPesananPenjualan;
use App\Models\SPP\SuratPesananPenjualanDetail;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PendapatanService
{
    public function storePendapatan($request, $userId, $accounts)
    {
        $prefix = 'PEND';
        $date = Carbon::parse($request->tanggal)->format('Ymd');
        $random = strtoupper(Str::random(6));
        $referenceNumber = "{$prefix}-{$date}-{$random}";

        $transactionType = $request->jenis_pendapatan === 'lain_lain' ? 'pendapatan_lain' : 'pendapatan_tunai';

        // 1. Create Journal Entry
        $entry = JournalEntry::create([
            'user_id' => $userId,
            'reference_number' => $referenceNumber,
            'date' => $request->tanggal,
            'description' => $request->uraian_pendapatan,
            'transaction_type' => $transactionType,
        ]);

        $totalAmount = $request->jumlah + ($request->biaya_lain ?? 0);

        // 2. Handle Jenis Pendapatan
        switch ($request->jenis_pendapatan) {
            case 'lain_lain':
                $this->storeLainLain($request, $userId, $accounts, $entry, $totalAmount);
                break;
            case 'piutang':
                $this->storePiutang($request, $userId, $accounts, $entry);
                break;
            case 'kredit': // Pelunasan
                $this->storeKredit($request, $userId, $accounts, $entry);
                break;
        }

        // Handle Biaya Lain jika ada
        if ($request->biaya_lain > 0) {
            $this->storeBiayaLain($request, $userId, $accounts, $entry);
        }

        // Logika Update Stok & Kartu Gudang dihapus sesuai permintaan

        return $entry;
    }

    protected function storeLainLain($request, $userId, $accounts, $entry, $totalAmount)
    {
        // Debit: Kas Utama (1101)
        $entry->items()->create([
            'user_id' => $userId,
            'journal_entry_id' => $entry->id,
            'account_id' => $accounts['1101']->id,
            'debit' => $totalAmount,
            'credit' => 0,
        ]);

        // Credit: Pendapatan Penjualan (4101)
        $entry->items()->create([
            'user_id' => $userId,
            'journal_entry_id' => $entry->id,
            'account_id' => $accounts['4101']->id,
            'debit' => 0,
            'credit' => $request->jumlah,
        ]);
    }

    protected function storePiutang($request, $userId, $accounts, $entry)
    {
        $piutangAmount = $request->jumlah + ($request->jumlah_piutang ?? 0);
        $subledgerId = $request->nama_pelanggan;

        // Debit: Piutang Usaha (1104)
        $entry->items()->create([
            'user_id' => $userId,
            'journal_entry_id' => $entry->id,
            'account_id' => $accounts['1104']->id,
            'debit' => $piutangAmount,
            'credit' => 0,
            'sub_ledger_type' => 'App\Models\Pelanggan',
            'sub_ledger_id' => $subledgerId,
        ]);

        // Credit: Pendapatan Penjualan (4101)
        $entry->items()->create([
            'user_id' => $userId,
            'journal_entry_id' => $entry->id,
            'account_id' => $accounts['4101']->id,
            'debit' => 0,
            'credit' => $piutangAmount,
        ]);

        // Otomatis buat SPP, SPB, dan Faktur
        $tanggalPesan = $request->tanggal_pesan ?? $request->tanggal;
        $tanggalKirim = $request->tanggal_kirim ?? $request->tanggal;

        $todayStr = Carbon::now()->format('Ymd');

        $sppCount = SuratPesananPenjualan::where('user_id', $userId)->whereDate('created_at', Carbon::today())->count() + 1;
        $sppNumber = sprintf('SPP/%s/%02d/%03d', $todayStr, $userId, $sppCount);

        $spp = SuratPesananPenjualan::create([
            'pelanggan_id' => $subledgerId,
            'jenis' => 'transaksi_masuk',
            'nomor_pesanan_penjualan' => $sppNumber,
            'tanggal_pesanan_penjualan' => $tanggalPesan,
            'tanggal_kirim_pesanan_penjualan' => $tanggalKirim,
            'nama_bagian_pembelian' => null,
            'ttd_bagian_pembelian' => null,
            'user_id' => $userId,
        ]);

        $spbCount = SuratPengirimanBarang::where('user_id', $userId)->whereDate('created_at', Carbon::today())->count() + 1;
        $spbNumber = sprintf('SPB/%s/%02d/%03d', $todayStr, $userId, $spbCount);

        $spb = SuratPengirimanBarang::create([
            'pesanan_penjualan_id' => $spp->id,
            'nomor_pengiriman_barang' => $spbNumber,
            'tanggal_terima' => $tanggalKirim,
            'status_pengiriman' => 'diproses',
            'jenis_pengiriman' => 'darat',
            'keadaan' => 'baik',
            'keterangan' => 'Auto Generate (Piutang) - '.$request->uraian_pendapatan,
            'nama_penerima' => null,
            'nama_pengirim' => null,
            'user_id' => $userId,
        ]);

        $fakturCount = FakturPenjualan::where('user_id', $userId)->whereDate('created_at', Carbon::today())->count() + 1;
        $fakturNumber = sprintf('INV/%s/%02d/%03d', $todayStr, $userId, $fakturCount);

        FakturPenjualan::create([
            'spb_id' => $spb->id,
            'kode_faktur' => $fakturNumber,
            'tanggal_faktur' => $tanggalKirim,
            'user_id' => $userId,
        ]);

        $entry->update([
            'description' => 'Faktur Penjualan - '.$fakturNumber.' - '.$entry->description,
        ]);

        // Create Details jika ada barang terjual
        if ($request->filled('barang_terjual') && is_array($request->barang_terjual)) {
            foreach ($request->barang_terjual as $index => $barangId) {
                if (! $barangId) {
                    continue;
                }

                $detailBarang = Barang::find($barangId);
                if (! $detailBarang) {
                    continue;
                }

                $jumlahDijual = $request->jumlah_barang_dijual[$index] ?? 0;
                $harga = $detailBarang->harga_jual_per_unit ?? 0;
                $diskon = $request->potongan_pembelian[$index] ?? 0;
                $total = ($harga * $jumlahDijual) - $diskon;

                $sppDetail = SuratPesananPenjualanDetail::create([
                    'pesanan_penjualan_id' => $spp->id,
                    'barang_id' => $barangId,
                    'nama_barang' => $detailBarang->nama,
                    'kuantitas' => $jumlahDijual,
                    'harga' => $harga,
                    'diskon' => $diskon,
                    'total' => $total,
                ]);

                SuratPengirimanBarangDetail::create([
                    'spb_id' => $spb->id,
                    'pesanan_penjualan_detail_id' => $sppDetail->id,
                    'jumlah_dikirim' => $jumlahDijual,
                    'keterangan' => 'Auto Generate',
                ]);

                // Update Stok & Kartu Gudang
                $barangItem = KartuGudang::where('barang_id', $barangId)->where('user_id', $userId)->latest('id')->first();
                $saldoSatuanAwal = $barangItem ? $barangItem->saldo_persatuan : 0;
                $unitPerKemasan = $detailBarang->jumlah_unit_per_kemasan;

                if ($saldoSatuanAwal < $jumlahDijual) {
                    throw new \Exception("Saldo barang '{$detailBarang->nama}' tidak mencukupi.");
                }

                $satuanBaru = $saldoSatuanAwal - $jumlahDijual;
                $saldoPerKemasanBaru = $unitPerKemasan > 0 ? ceil($satuanBaru / $unitPerKemasan) : 0;

                KartuGudang::create([
                    'barang_id' => $barangId,
                    'tanggal' => $tanggalKirim,
                    'diterima' => 0,
                    'dikeluarkan' => $jumlahDijual,
                    'uraian' => 'Pengiriman Penjualanan Barang - '.$spbNumber,
                    'saldo_persatuan' => $satuanBaru,
                    'saldo_perkemasan' => $saldoPerKemasanBaru,
                    'journal_entry_id' => $entry->id,
                    'user_id' => $userId,
                ]);
            }
        }
    }

    protected function storeKredit($request, $userId, $accounts, $entry)
    {
        $kreditAmount = $request->jumlah + ($request->jumlah_kredit ?? 0);
        $subledgerId = $request->nama_pelanggan;

        // Debit: Kas Utama (1101)
        $entry->items()->create([
            'user_id' => $userId,
            'journal_entry_id' => $entry->id,
            'account_id' => $accounts['1101']->id,
            'debit' => $kreditAmount,
            'credit' => 0,
        ]);

        // Credit: Piutang Usaha (1104)
        $entry->items()->create([
            'user_id' => $userId,
            'journal_entry_id' => $entry->id,
            'account_id' => $accounts['1104']->id,
            'debit' => 0,
            'credit' => $kreditAmount,
            'sub_ledger_type' => 'App\Models\Pelanggan',
            'sub_ledger_id' => $subledgerId,
        ]);
    }

    protected function storeBiayaLain($request, $userId, $accounts, $entry)
    {
        $entry->items()->create([
            'user_id' => $userId,
            'journal_entry_id' => $entry->id,
            'account_id' => $accounts['4101']->id,
            'debit' => 0,
            'credit' => $request->biaya_lain,
        ]);
    }
}
