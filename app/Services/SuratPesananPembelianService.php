<?php

namespace App\Services;

use App\Jobs\SendSuratPesananPembelianJob;
use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\KartuGudang;
use App\Models\Pelanggan;
use App\Models\SPP\SuratPesananPembelian;
use App\Models\SPP\SuratPesananPembelianDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SuratPesananPembelianService
{
    public function __construct(protected FileUploadService $fileUploadService) {}

    public function store($request)
    {
        DB::beginTransaction();
        try {
            if ($request->hasFile('ttd_pimpinan_supplier')) {
                $ttdFile = $this->fileUploadService->upload($request->ttd_pimpinan_supplier, 'surat-pesanan-pembelian/ttd_pimpinan_supplier', Auth::user()->email);
            }
            $suratSuratPesananPembelian = SuratPesananPembelian::create([
                'jenis' => 'transaksi_keluar',
                'pelanggan_id' => null,
                'supplier_id' => $request->supplier_id,
                'nomor_pesanan_pembelian' => $request->nomor_pesanan_pembelian,
                'tanggal_pesanan_pembelian' => $request->tanggal_pesanan_pembelian,
                'tanggal_kirim_pesanan_pembelian' => $request->tanggal_kirim_pesanan_pembelian,
                'nama_bagian_pembelian' => $request->nama_pimpinan_supplier,
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

                SuratPesananPembelianDetail::create([
                    'spp_id' => $suratSuratPesananPembelian->id,
                    'barang_id' => $item['barang_id'] ?? null,
                    'nama_barang' => $item['nama_barang'],
                    'kuantitas' => $kuantitas,
                    'harga' => $this->cleanRupiah($item['harga']),
                    'diskon' => $item['diskon'] ?? 0,
                    'total' => $this->cleanRupiah($item['total']),
                ]);

                // Update Stok & Kartu Gudang
                if (isset($item['barang_id'])) {
                    $barang = Barang::where('id', $item['barang_id'])->where('user_id', auth()->id())->first();
                } else {
                    $barang = Barang::where('nama', $item['nama_barang'])->where('user_id', auth()->id())->first();
                }

                if ($barang) {
                    $lastKartu = KartuGudang::where('barang_id', $barang->id)
                        ->where('user_id', auth()->id())
                        ->latest()
                        ->first();

                    $saldoPersatuanSebelumnya = $lastKartu->saldo_persatuan ?? 0;
                    $saldoPerKemasanSebelumnya = $lastKartu->saldo_perkemasan ?? 0;

                    $diterima = $kuantitas;
                    $dikeluarkan = 0;

                    $saldoPersatuanBaru = $saldoPersatuanSebelumnya + $diterima - $dikeluarkan;

                    $pcsPerKemasan = $barang->jumlah_unit_per_kemasan ?: 1;
                    $saldoPerKemasanBaru = $saldoPerKemasanSebelumnya +
                        round(($diterima - $dikeluarkan) / $pcsPerKemasan, 0);

                    KartuGudang::create([
                        'barang_id' => $barang->id,
                        'tanggal' => $request->tanggal_pesanan_pembelian,
                        'diterima' => $diterima,
                        'dikeluarkan' => $dikeluarkan,
                        'uraian' => 'Pesanan Pembelian Barang - '.$suratSuratPesananPembelian->nomor_pesanan_pembelian,
                        'saldo_persatuan' => $saldoPersatuanBaru,
                        'saldo_perkemasan' => $saldoPerKemasanBaru,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            // Calculate total Purchase Amount
            $totalPurchaseAmount = 0;
            foreach ($request->detail as $item) {
                $totalPurchaseAmount += $this->cleanRupiah($item['total']);
            }

            // Get accounts for Purchase Journal
            $inventoryAccount = Account::where('user_id', auth()->id())->where('code', '1105')->first();  // Persediaan Barang Dagang
            $payableAccount = Account::where('user_id', auth()->id())->where('code', '2101')->first(); // Utang Usaha

            if ($payableAccount && $inventoryAccount) {
                $journalEntry = JournalEntry::create([
                    'user_id' => auth()->id(),
                    'reference_number' => 'SPP-'.date('Ymd', strtotime($request->tanggal_pesanan_pembelian)).'-'.strtoupper(Str::random(6)),
                    'date' => $request->tanggal_pesanan_pembelian,
                    'description' => 'Pesanan Pembelian - '.$suratSuratPesananPembelian->nomor_pesanan_pembelian,
                    'transaction_type' => 'pemesanan-barang',
                ]);

                // 1. Debit: Persediaan (1105)
                $journalEntry->items()->create([
                    'user_id' => auth()->id(),
                    'account_id' => $inventoryAccount->id,
                    'debit' => $totalPurchaseAmount,
                    'credit' => 0,
                ]);

                // 2. Credit: Utang Usaha (2101)
                $journalEntry->items()->create([
                    'user_id' => auth()->id(),
                    'account_id' => $payableAccount->id,
                    'debit' => 0,
                    'credit' => $totalPurchaseAmount,
                    'sub_ledger_type' => Pelanggan::class,
                    'sub_ledger_id' => $request->supplier_id,
                ]);
            }

            DB::commit();

            // dispatch job mail
            SendSuratPesananPembelianJob::dispatch(
                $suratSuratPesananPembelian,
                auth()->user(),
                $request->email_supplier
            );

            return $suratSuratPesananPembelian;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function storePelanggan($request)
    {
        DB::beginTransaction();
        try {
            if ($request->hasFile('ttd_pelanggan')) {
                $ttdFile = $this->fileUploadService->upload($request->ttd_pelanggan, 'surat-pesanan-pembelian/ttd_pelanggan', Auth::user()->email);
            }
            $suratSuratPesananPembelian = SuratPesananPembelian::create([
                'jenis' => 'transaksi_masuk',
                'pelanggan_id' => $request->pelanggan_id,
                'supplier_id' => null,
                'nomor_pesanan_pembelian' => $request->nomor_pesanan_pembelian,
                'tanggal_pesanan_pembelian' => $request->tanggal_pesanan_pembelian,
                'tanggal_kirim_pesanan_pembelian' => $request->tanggal_pesanan_pembelian, // Using the same date or can be nullable
                'nama_bagian_pembelian' => $request->nama_pelanggan ?? null,
                'ttd_pengirim' => $ttdFile ?? null,
                'user_id' => auth()->id(),
            ]);

            if (isset($request->detail) && is_array($request->detail)) {
                foreach ($request->detail as $item) {
                    SuratPesananPembelianDetail::create([
                        'spp_id' => $suratSuratPesananPembelian->id,
                        'barang_id' => $item['barang_id'] ?? null,
                        'nama_barang' => $item['nama_barang'],
                        'kuantitas' => $this->cleanRupiah($item['kuantitas']),
                        'harga' => $this->cleanRupiah($item['harga']),
                        'diskon' => $item['diskon'] ?? 0,
                        'total' => $this->cleanRupiah($item['total']),
                    ]);
                }
            }

            DB::commit();

            return $suratSuratPesananPembelian;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($sppId)
    {
        DB::beginTransaction();
        try {
            $suratSuratPesananPembelian = SuratPesananPembelian::with('pesananPembelianDetail')
                ->where('user_id', auth()->id())
                ->findOrFail($sppId);

            foreach ($suratSuratPesananPembelian->pesananPembelianDetail as $detail) {
                if ($detail->barang_id) {
                    $barang = Barang::where('id', $detail->barang_id)->where('user_id', auth()->id())->first();
                } else {
                    $barang = Barang::where('nama', $detail->nama_barang)->where('user_id', auth()->id())->first();
                }

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
                        'uraian' => 'Pembatalan Pesanan Pembelian - '.$suratSuratPesananPembelian->nomor_pesanan_pembelian,
                        'saldo_persatuan' => $saldoPersatuanBaru,
                        'saldo_perkemasan' => $saldoPerKemasanBaru,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            // Hapus detail dan data utama
            $suratSuratPesananPembelian->pesananPembelianDetail()->delete();

            // Hapus Journal Entry terkait
            JournalEntry::where('user_id', auth()->id())
                ->where('description', 'Pesanan Pembelian - '.$suratSuratPesananPembelian->nomor_pesanan_pembelian)
                ->delete();

            $suratSuratPesananPembelian->delete();

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
            $data = SuratPesananPembelian::with(
                'supplier',
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
                Str::slug('surat-pesanan-pembelian-'.
                    $data->nomor_pesanan_pembelian).'.pdf',
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
