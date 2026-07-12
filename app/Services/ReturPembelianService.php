<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\KartuGudang;
use App\Models\ReturPembelian;
use App\Models\ReturPembelianDetail;
use App\Models\Pelanggan;
use App\Models\SPP\SuratPesananPembelian;
use App\Models\SPP\SuratPesananPembelianDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReturPembelianService
{
    public function store($request)
    {
        DB::beginTransaction();
        try {
            $totalPurchaseAmount = 0;

            $nomorRetur = $request->nomor_retur;
            $spp = SuratPesananPembelian::findOrFail($request->spp_id);

            // Simpan memo utama (Retur Pembelian)
            $retur = ReturPembelian::create([
                'nomor_retur' => $nomorRetur,
                'tanggal' => $request->tanggal,
                'pesanan_pembelian_id' => $request->spp_id,
                'supplier_id' => $spp->supplier_id,
                'alasan_pengembalian' => $request->alasan_pengembalian,
                'total' => 0, // Akan diupdate nanti
                'user_id' => auth()->id(),
            ]);

            // Simpan detail retur
            foreach ($request->barang_id as $index => $barangDetailId) {
                $qty = $request->jumlah_dikembalikan[$index];
                if ($qty <= 0) continue;

                $hargaSatuan = $this->cleanRupiah($request->harga[$index]);
                $jumlah = $this->cleanRupiah($request->total[$index]);

                $sppDetail = SuratPesananPembelianDetail::findOrFail($barangDetailId);

                ReturPembelianDetail::create([
                    'retur_pembelian_id' => $retur->id,
                    'nama_barang' => $sppDetail->nama_barang,
                    'kuantitas' => $qty,
                    'harga_satuan' => $hargaSatuan,
                    'jumlah' => $jumlah,
                ]);

                $totalPurchaseAmount += $jumlah;

                // Update Stok & Kartu Gudang (Barang Keluar)
                $barang = Barang::where('nama', $sppDetail->nama_barang)
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
                    $dikeluarkan = $qty; // Barang dikembalikan ke supplier

                    $saldoPersatuanBaru = $saldoPersatuanSebelumnya + $diterima - $dikeluarkan;

                    $pcsPerKemasan = $barang->jumlah_unit_per_kemasan ?: 1;
                    $saldoPerKemasanBaru = $saldoPerKemasanSebelumnya +
                        round(($diterima - $dikeluarkan) / $pcsPerKemasan, 0);

                    KartuGudang::create([
                        'barang_id' => $barang->id,
                        'tanggal' => $request->tanggal,
                        'diterima' => $diterima,
                        'dikeluarkan' => $dikeluarkan,
                        'uraian' => 'Retur Pembelian - '.$retur->nomor_retur,
                        'saldo_persatuan' => $saldoPersatuanBaru,
                        'saldo_perkemasan' => $saldoPerKemasanBaru,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            $retur->update(['total' => $totalPurchaseAmount]);

            // Journal Entry
            $payableAccount = Account::where('user_id', auth()->id())->where('code', '2101')->first(); // Hutang Usaha
            $inventoryAccount = Account::where('user_id', auth()->id())->where('code', '1105')->first();  // Persediaan Barang Dagang

            if ($payableAccount && $inventoryAccount) {
                $journalEntry = JournalEntry::create([
                    'user_id' => auth()->id(),
                    'reference_number' => 'RPB-'.date('Ymd', strtotime($request->tanggal)).'-'.strtoupper(Str::random(6)),
                    'date' => $request->tanggal,
                    'description' => 'Retur Pembelian - '.$retur->nomor_retur,
                    'transaction_type' => 'retur_pembelian',
                ]);

                // 1. Debit: Hutang Usaha (2101) - Mengurangi Hutang
                $journalEntry->items()->create([
                    'user_id' => auth()->id(),
                    'account_id' => $payableAccount->id,
                    'debit' => $totalPurchaseAmount,
                    'credit' => 0,
                    'sub_ledger_type' => Pelanggan::class,
                    'sub_ledger_id' => $spp->supplier_id,
                ]);

                // 2. Credit: Persediaan (1105) - Mengurangi Persediaan
                $journalEntry->items()->create([
                    'user_id' => auth()->id(),
                    'account_id' => $inventoryAccount->id,
                    'debit' => 0,
                    'credit' => $totalPurchaseAmount,
                ]);
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error generating retur pembelian', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return false;
        }
    }

    public function destroy($returId)
    {
        DB::beginTransaction();
        try {
            $retur = ReturPembelian::with('detail')
                ->where('id', $returId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            foreach ($retur->detail as $detail) {
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

                    // Reversal: Barang yang sebelumnya dikeluarkan, sekarang diterima (batal retur)
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
                        'uraian' => 'Pembatalan Retur Pembelian - '.$retur->nomor_retur,
                        'saldo_persatuan' => $saldoPersatuanBaru,
                        'saldo_perkemasan' => $saldoPerKemasanBaru,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            // Hapus Journal Entry terkait
            JournalEntry::where('user_id', auth()->id())
                ->where('description', 'Retur Pembelian - '.$retur->nomor_retur)
                ->delete();

            // Hapus detail retur
            $retur->detail()->delete();

            // Hapus retur utama
            $retur->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting retur pembelian: ', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return false;
        }
    }

    public function generatePdf($returId)
    {
        $retur = ReturPembelian::with('detail', 'pesananPembelian.supplier')
            ->where('id', $returId)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $profileUser = Auth::user();

        // Generate PDF
        $pdf = Pdf::loadView(
            'administrasi.surat.memo-kredit.template-pdf-penjual',
            compact('retur', 'profileUser'),
        )->setPaper('A4', 'portrait');

        return $pdf->download(
            Str::slug('retur_pembelian_'.$retur->nomor_retur).'.pdf',
        );
    }

    private function cleanRupiah(string|int $value): int
    {
        return (int) preg_replace("/\D/", '', $value);
    }
}
