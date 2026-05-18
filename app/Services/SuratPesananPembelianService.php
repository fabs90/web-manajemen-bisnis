<?php

namespace App\Services;

use App\Jobs\SendSuratPesananPembelianJob;
use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\KartuGudang;
use App\Models\Pelanggan;
use App\Models\SPP\PesananPembelian;
use App\Models\SPP\PesananPembelianDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SuratPesananPembelianService
{
    public function __construct(protected FileUploadService $fileUploadService)
    {
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            if ($request->hasFile('ttd_pelanggan')) {
                $ttdFile = $this->fileUploadService->upload($request->ttd_pelanggan, 'surat-pesanan-pembelian/ttd_pelanggan', Auth::user()->email);
            }
            $suratPesananPembelian = PesananPembelian::create([
                'jenis' => 'transaksi_masuk',
                'pelanggan_id' => $request->pelanggan_id,
                'supplier_id' => null,
                'nomor_pesanan_pembelian' => $request->nomor_pesanan_pembelian,
                'tanggal_pesanan_pembelian' => $request->tanggal_pesanan_pembelian,
                'tanggal_kirim_pesanan_pembelian' => $request->tanggal_kirim_pesanan_pembelian,
                'nama_bagian_pembelian' => $request->nama_bagian_pelanggan,
                'ttd_pengirim' => $ttdFile ?? null,
                'user_id' => auth()->id(),
            ]);

            foreach ($request->detail as $item) {
                // $request->detail isinya:
                // [
                //     'nama_barang' => ,
                //     'kuantitas' => ,
                //     'harga' => ,
                //     'diskon' => ,
                //     'total' => ,
                // ]
                $kuantitas = $this->cleanRupiah($item['kuantitas']);

                PesananPembelianDetail::create([
                    'spp_id' => $suratPesananPembelian->id,
                    'nama_barang' => $item['nama_barang'],
                    'kuantitas' => $kuantitas,
                    'harga' => $this->cleanRupiah($item['harga']),
                    'diskon' => $item['diskon'] ?? 0,
                    'total' => $this->cleanRupiah($item['total']),
                ]);

                // Update Stok & Kartu Gudang
                $barang = Barang::where('nama', $item['nama_barang'])
                    ->where('user_id', auth()->id())
                    ->first();

                if ($barang) {
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
                        'tanggal' => $request->tanggal_pesanan_pembelian,
                        'diterima' => $diterima,
                        'dikeluarkan' => $dikeluarkan,
                        'uraian' => 'Pesanan Pembelian - ' . $suratPesananPembelian->nomor_pesanan_pembelian,
                        'saldo_persatuan' => $saldoPersatuanBaru,
                        'saldo_perkemasan' => $saldoPerKemasanBaru,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            // Calculate total Sales and total COGS (HPP)
            $totalSalesAmount = 0;
            $totalCogsAmount = 0;
            foreach ($request->detail as $item) {
                $totalSalesAmount += $this->cleanRupiah($item['total']);

                // Lookup barang for COGS calculation
                $barang = Barang::where('nama', $item['nama_barang'])
                    ->where('user_id', auth()->id())
                    ->first();

                if ($barang) {
                    // Total Harga Modal Saat Beli Barang, Buat dibandingin dengan harga jual saat ini
                    $totalCogsAmount += ($this->cleanRupiah($item['kuantitas']) * ($barang->harga_beli_per_unit ?? 0));
                }
            }

            // Get accounts for Sales Journal
            $receivableAccount = Account::where('user_id', auth()->id())->where('code', '1104')->first(); // Piutang Usaha
            $revenueAccount = Account::where('user_id', auth()->id())->where('code', '4101')->first();    // Pendapatan Penjualan
            $hppAccount = Account::where('user_id', auth()->id())->where('code', '5101')->first();        // HPP
            $inventoryAccount = Account::where('user_id', auth()->id())->where('code', '1105')->first();  // Persediaan Barang Dagang

            if ($receivableAccount && $revenueAccount && $hppAccount && $inventoryAccount) {
                $journalEntry = JournalEntry::create([
                    'user_id' => auth()->id(),
                    'reference_number' => 'SPP-' . date('Ymd', strtotime($request->tanggal_pesanan_pembelian)) . '-' . strtoupper(Str::random(6)),
                    'date' => $request->tanggal_pesanan_pembelian,
                    'description' => 'Pesanan Pembelian - ' . $suratPesananPembelian->nomor_pesanan_pembelian,
                    'transaction_type' => 'penjualan',
                ]);

                // 1. Debit: Piutang Usaha (1104)
                $journalEntry->items()->create([
                    'user_id' => auth()->id(),
                    'account_id' => $receivableAccount->id,
                    'debit' => $totalSalesAmount,
                    'credit' => 0,
                    'sub_ledger_type' => Pelanggan::class,
                    'sub_ledger_id' => $request->pelanggan_id,
                ]);

                // 2. Credit: Pendapatan Penjualan (4101)
                $journalEntry->items()->create([
                    'user_id' => auth()->id(),
                    'account_id' => $revenueAccount->id,
                    'debit' => 0,
                    'credit' => $totalSalesAmount,
                ]);

                // 3. Debit: HPP (5101)
                $journalEntry->items()->create([
                    'user_id' => auth()->id(),
                    'account_id' => $hppAccount->id,
                    'debit' => $totalCogsAmount,
                    'credit' => 0,
                ]);

                // 4. Credit: Persediaan (1105)
                $journalEntry->items()->create([
                    'user_id' => auth()->id(),
                    'account_id' => $inventoryAccount->id,
                    'debit' => 0,
                    'credit' => $totalCogsAmount,
                ]);
            }

            DB::commit();

            // dispatch job mail
            SendSuratPesananPembelianJob::dispatch(
                $suratPesananPembelian,
                auth()->user(),
                $request->email_pelanggan
            );

            return $suratPesananPembelian;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($sppId)
    {
        DB::beginTransaction();
        try {
            $suratPesananPembelian = PesananPembelian::with('pesananPembelianDetail')
                ->where('user_id', auth()->id())
                ->findOrFail($sppId);

            foreach ($suratPesananPembelian->pesananPembelianDetail as $detail) {
                // Cari Barang berdasarkan nama (mengikuti logika di store)
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
                        'tanggal' => now(), // Atau bisa menggunakan tanggal hari ini untuk pembatalan
                        'diterima' => $diterima,
                        'dikeluarkan' => $dikeluarkan,
                        'uraian' => 'Pembatalan Pesanan Pembelian - ' . $suratPesananPembelian->nomor_pesanan_pembelian,
                        'saldo_persatuan' => $saldoPersatuanBaru,
                        'saldo_perkemasan' => $saldoPerKemasanBaru,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            // Hapus detail dan data utama
            $suratPesananPembelian->pesananPembelianDetail()->delete();

            // Hapus Journal Entry terkait
            JournalEntry::where('user_id', auth()->id())
                ->where('description', 'Pesanan Pembelian - ' . $suratPesananPembelian->nomor_pesanan_pembelian)
                ->delete();

            $suratPesananPembelian->delete();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function generatePdf($sppId)
    {
        try {
            $data = PesananPembelian::with(
                'pelanggan',
                'pesananPembelianDetail',
            )
                ->where('user_id', auth()->user()->id)
                ->findOrFail($sppId);

            $profileUser = Auth::user();

            $pdf = Pdf::loadView(
                'administrasi.surat.surat-pesanan-pembelian.template-pdf',
                compact('data', 'profileUser'),
            )->setPaper('A4', 'portrait');

            return $pdf->download(
                Str::slug('surat-pesanan-pembelian-' .
                    $data->nomor_pesanan_pembelian) . '.pdf',
            );
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function cleanRupiah(string|int $value): int
    {
        return (int) preg_replace("/\D/", '', $value);
    }
}
