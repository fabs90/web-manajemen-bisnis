<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\KartuGudang;
use App\Models\Pelanggan;
use App\Models\SPP\SuratPesananPenjualan;
use App\Models\SPP\SuratPesananPenjualanDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SuratPesananPenjualanService
{
    public function __construct(protected FileUploadService $fileUploadService) {}

    public function storePelanggan($request): SuratPesananPenjualan
    {
        DB::beginTransaction();
        try {
            $isRequest = $request instanceof Request;

            $ttdFile = null;
            if ($isRequest && $request->hasFile('ttd_pelanggan')) {
                $ttdFile = $this->fileUploadService->upload(
                    $request->file('ttd_pelanggan'),
                    'surat-pesanan-penjualan/ttd_pelanggan',
                    Auth::user()->email
                );
            } elseif (is_array($request) && isset($request['ttd_pelanggan']) && $request['ttd_pelanggan'] instanceof \Illuminate\Http\UploadedFile) {
                $ttdFile = $this->fileUploadService->upload(
                    $request['ttd_pelanggan'],
                    'surat-pesanan-penjualan/ttd_pelanggan',
                    Auth::user()->email
                );
            }

            $pelangganId = $isRequest ? $request->pelanggan_id : $request['pelanggan_id'];
            $nomorPesanan = $isRequest ? $request->nomor_pesanan_pembelian : $request['nomor_pesanan_pembelian'];
            $tanggalPesanan = $isRequest ? $request->tanggal_pesanan_pembelian : $request['tanggal_pesanan_pembelian'];
            $tanggalKirim = $isRequest ? ($request->tanggal_kirim_pesanan_pembelian ?? $request->tanggal_pesanan_pembelian) : ($request['tanggal_kirim_pesanan_pembelian'] ?? $request['tanggal_pesanan_pembelian']);
            $namaBagian = $isRequest ? ($request->nama_pelanggan ?? null) : ($request['nama_pelanggan'] ?? null);
            $detailItems = $isRequest ? $request->detail : $request['detail'];

            $suratPesananPenjualan = SuratPesananPenjualan::create([
                'pelanggan_id' => $pelangganId,
                'jenis' => 'transaksi_masuk',
                'nomor_pesanan_penjualan' => $nomorPesanan,
                'tanggal_pesanan_penjualan' => $tanggalPesanan,
                'tanggal_kirim_pesanan_penjualan' => $tanggalKirim,
                'nama_bagian_pembelian' => $namaBagian,
                'ttd_bagian_pembelian' => $ttdFile,
                'user_id' => auth()->id(),
            ]);

            $totalSalesAmount = 0;
            $totalHppAmount = 0;

            if (isset($detailItems) && is_array($detailItems)) {
                foreach ($detailItems as $item) {
                    $kuantitas = $this->cleanRupiah($item['kuantitas']);
                    $harga = $this->cleanRupiah($item['harga']);
                    $diskon = $item['diskon'] ?? 0;
                    $total = $this->cleanRupiah($item['total']);

                    $totalSalesAmount += $total;

                    SuratPesananPenjualanDetail::create([
                        'pesanan_penjualan_id' => $suratPesananPenjualan->id,
                        'nama_barang' => $item['nama_barang'],
                        'kuantitas' => $kuantitas,
                        'harga' => $harga,
                        'diskon' => $diskon,
                        'total' => $total,
                    ]);

                    // Update Stok & Kartu Gudang (Pengurangan Stok)
                    $barang = Barang::where('nama', $item['nama_barang'])
                        ->where('user_id', auth()->id())
                        ->first();

                    if ($barang) {
                        $totalHppAmount += $kuantitas * $barang->harga_beli_per_unit;

                        $lastKartu = KartuGudang::where('barang_id', $barang->id)
                            ->where('user_id', auth()->id())
                            ->latest()
                            ->first();

                        $saldoPersatuanSebelumnya = $lastKartu->saldo_persatuan ?? 0;
                        $saldoPerKemasanSebelumnya = $lastKartu->saldo_perkemasan ?? 0;

                        $diterima = 0;
                        $dikeluarkan = $kuantitas;

                        $saldoPersatuanBaru = $saldoPersatuanSebelumnya + $diterima - $dikeluarkan;

                        $pcsPerKemasan = $barang->jumlah_unit_per_kemasan ?: 1;
                        $saldoPerKemasanBaru = $saldoPerKemasanSebelumnya +
                            round(($diterima - $dikeluarkan) / $pcsPerKemasan, 0);

                        KartuGudang::create([
                            'barang_id' => $barang->id,
                            'tanggal' => $tanggalPesanan,
                            'diterima' => $diterima,
                            'dikeluarkan' => $dikeluarkan,
                            'uraian' => 'Pesanan Penjualan Barang - '.$suratPesananPenjualan->nomor_pesanan_penjualan,
                            'saldo_persatuan' => $saldoPersatuanBaru,
                            'saldo_perkemasan' => $saldoPerKemasanBaru,
                            'user_id' => auth()->id(),
                        ]);
                    }
                }
            }

            // Journaling (Penjurnalan)
            // Get accounts for Sales Journal
            $piutangAccount = Account::where('user_id', auth()->id())->where('code', '1104')->first();      // Piutang Usaha
            $persediaanAccount = Account::where('user_id', auth()->id())->where('code', '1105')->first();   // Persediaan Barang Dagang
            $pendapatanAccount = Account::where('user_id', auth()->id())->where('code', '4101')->first();   // Pendapatan Penjualan
            $hppAccount = Account::where('user_id', auth()->id())->where('code', '5101')->first();          // HPP

            if ($piutangAccount && $persediaanAccount && $pendapatanAccount && $hppAccount) {
                $journalEntry = JournalEntry::create([
                    'user_id' => auth()->id(),
                    'reference_number' => 'SPP-'.date('Ymd', strtotime($tanggalPesanan)).'-'.strtoupper(Str::random(6)),
                    'date' => $tanggalPesanan,
                    'description' => 'Pesanan Penjualan - '.$suratPesananPenjualan->nomor_pesanan_penjualan,
                    'transaction_type' => 'penjualan',
                ]);

                // Update KartuGudang records with this journal_entry_id so we can easily track/delete them later
                KartuGudang::where('user_id', auth()->id())
                    ->where('tanggal', $tanggalPesanan)
                    ->where('uraian', 'Pesanan Penjualan Barang - '.$suratPesananPenjualan->nomor_pesanan_penjualan)
                    ->update(['journal_entry_id' => $journalEntry->id]);

                // 1. Debit: Piutang Usaha (1104)
                $journalEntry->items()->create([
                    'user_id' => auth()->id(),
                    'account_id' => $piutangAccount->id,
                    'debit' => $totalSalesAmount,
                    'credit' => 0,
                    'sub_ledger_type' => Pelanggan::class,
                    'sub_ledger_id' => $pelangganId,
                ]);

                // 2. Credit: Pendapatan Penjualan (4101)
                $journalEntry->items()->create([
                    'user_id' => auth()->id(),
                    'account_id' => $pendapatanAccount->id,
                    'debit' => 0,
                    'credit' => $totalSalesAmount,
                ]);

                // 3. Debit: HPP (5101)
                $journalEntry->items()->create([
                    'user_id' => auth()->id(),
                    'account_id' => $hppAccount->id,
                    'debit' => $totalHppAmount,
                    'credit' => 0,
                ]);

                // 4. Credit: Persediaan (1105)
                $journalEntry->items()->create([
                    'user_id' => auth()->id(),
                    'account_id' => $persediaanAccount->id,
                    'debit' => 0,
                    'credit' => $totalHppAmount,
                ]);
            }

            DB::commit();

            return $suratPesananPenjualan;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($sppId): bool
    {
        DB::beginTransaction();
        try {
            $suratPesananPenjualan = SuratPesananPenjualan::with('details')
                ->where('user_id', auth()->id())
                ->findOrFail($sppId);

            foreach ($suratPesananPenjualan->details as $detail) {
                // Cari Barang berdasarkan nama
                $barang = Barang::where('nama', $detail->nama_barang)
                    ->where('user_id', auth()->id())
                    ->first();

                if ($barang) {
                    $lastKartu = KartuGudang::where('barang_id', $barang->id)
                        ->where('user_id', auth()->id())
                        ->latest()
                        ->first();

                    $saldoPersatuanSebelumnya = $lastKartu->saldo_persatuan ?? 0;
                    $saldoPerKemasanSebelumnya = $lastKartu->saldo_perkemasan ?? 0;

                    // Reversal: Kuantitas yang sebelumnya dikeluarkan, sekarang diterima kembali
                    $diterima = $detail->kuantitas;
                    $dikeluarkan = 0;

                    $saldoPersatuanBaru = $saldoPersatuanSebelumnya + $diterima - $dikeluarkan;

                    $pcsPerKemasan = $barang->jumlah_unit_per_kemasan ?: 1;
                    $saldoPerKemasanBaru = $saldoPerKemasanSebelumnya +
                        round(($diterima - $dikeluarkan) / $pcsPerKemasan, 0);

                    KartuGudang::create([
                        'barang_id' => $barang->id,
                        'tanggal' => now(),
                        'diterima' => $diterima,
                        'dikeluarkan' => $dikeluarkan,
                        'uraian' => 'Pembatalan Pesanan Penjualan - '.$suratPesananPenjualan->nomor_pesanan_penjualan,
                        'saldo_persatuan' => $saldoPersatuanBaru,
                        'saldo_perkemasan' => $saldoPerKemasanBaru,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            // Hapus detail
            $suratPesananPenjualan->details()->delete();

            // Hapus SPB
            $suratPesananPenjualan->suratPengirimanBarang()->delete();

            // Hapus Journal Entry terkait
            JournalEntry::where('user_id', auth()->id())
                ->where('description', 'Pesanan Penjualan - '.$suratPesananPenjualan->nomor_pesanan_penjualan)
                ->delete();

            // Hapus SPP Pelanggan
            $suratPesananPenjualan->delete();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function cleanRupiah(string|int $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        return (int) preg_replace("/\D/", '', $value);
    }
}
