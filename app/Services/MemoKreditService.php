<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Barang;
use App\Models\Faktur\FakturPenjualan;
use App\Models\JournalEntry;
use App\Models\KartuGudang;
use App\Models\MemoKredit\MemoKredit;
use App\Models\MemoKredit\MemoKreditDetail;
use App\Models\Pelanggan;
use App\Models\SPP\SuratPesananPenjualanDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MemoKreditService
{
    public function store($request)
    {
        DB::beginTransaction();
        try {
            $totalSalesAmount = 0;
            $totalCogsAmount = 0;

            $nomorMemo = $this->generateNomorMemo();

            // Simpan memo utama
            $memo = MemoKredit::create([
                'nomor_memo' => $nomorMemo,
                'tanggal' => $request->tanggal,
                'faktur_penjualan_id' => $request->faktur_id,
                'alasan_pengembalian' => $request->alasan_pengembalian,
                'total' => 0, // Akan diupdate nanti
                'user_id' => auth()->id(),
            ]);

            // Simpan detail memo kredit
            foreach ($request->barang_id as $index => $barangDetailId) {
                $qty = $request->jumlah_dikembalikan[$index];
                $hargaSatuan = $this->cleanRupiah($request->harga[$index]);
                $jumlah = $this->cleanRupiah($request->total[$index]);

                $sppDetail = SuratPesananPenjualanDetail::findOrFail($barangDetailId);

                MemoKreditDetail::create([
                    'memo_kredit_id' => $memo->id,
                    'nama_barang' => $sppDetail->nama_barang,
                    'kuantitas' => $qty,
                    'harga_satuan' => $hargaSatuan,
                    'jumlah' => $jumlah,
                ]);

                $totalSalesAmount += $jumlah;

                // Update Stok & Kartu Gudang
                $barang = Barang::where('nama', $sppDetail->nama_barang)
                    ->where('user_id', auth()->id())
                    ->first();

                if ($barang) {
                    $totalCogsAmount += ($qty * ($barang->harga_beli_per_unit ?? 0));

                    $lastKartu = KartuGudang::where('barang_id', $barang->id)
                        ->where('user_id', auth()->id())
                        ->latest()
                        ->first();

                    $saldoPersatuanSebelumnya = $lastKartu->saldo_persatuan ?? 0;
                    $saldoPerKemasanSebelumnya = $lastKartu->saldo_perkemasan ?? 0;

                    $diterima = $qty; // Barang kembali
                    $dikeluarkan = 0;

                    $saldoPersatuanBaru = $saldoPersatuanSebelumnya + $diterima - $dikeluarkan;

                    $pcsPerKemasan = $barang->jumlah_unit_per_kemasan ?: 1;
                    $saldoPerKemasanBaru = $saldoPerKemasanSebelumnya +
                        round(($diterima - $dikeluarkan) / $pcsPerKemasan, 0);

                    KartuGudang::create([
                        'barang_id' => $barang->id,
                        'tanggal' => $request->tanggal,
                        'diterima' => $diterima,
                        'dikeluarkan' => $dikeluarkan,
                        'uraian' => 'Memo Kredit - '.$memo->nomor_memo,
                        'saldo_persatuan' => $saldoPersatuanBaru,
                        'saldo_perkemasan' => $saldoPerKemasanBaru,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            $memo->update(['total' => $totalSalesAmount]);

            // Journal Entry
            $receivableAccount = Account::where('user_id', auth()->id())->where('code', '1104')->first(); // Piutang Usaha
            $revenueAccount = Account::where('user_id', auth()->id())->where('code', '4101')->first();    // Pendapatan Penjualan
            $hppAccount = Account::where('user_id', auth()->id())->where('code', '5101')->first();        // HPP
            $inventoryAccount = Account::where('user_id', auth()->id())->where('code', '1105')->first();  // Persediaan Barang Dagang

            if ($receivableAccount && $revenueAccount && $hppAccount && $inventoryAccount) {
                $journalEntry = JournalEntry::create([
                    'user_id' => auth()->id(),
                    'reference_number' => 'MK-'.date('Ymd', strtotime($request->tanggal)).'-'.strtoupper(Str::random(6)),
                    'date' => $request->tanggal,
                    'description' => 'Memo Kredit - '.$memo->nomor_memo,
                    'transaction_type' => 'penjualan_retur',
                ]);

                // 1. Debit: Pendapatan Penjualan (4101) - Mengurangi Pendapatan
                $journalEntry->items()->create([
                    'user_id' => auth()->id(),
                    'account_id' => $revenueAccount->id,
                    'debit' => $totalSalesAmount,
                    'credit' => 0,
                ]);

                // 2. Credit: Piutang Usaha (1104) - Mengurangi Piutang
                $journalEntry->items()->create([
                    'user_id' => auth()->id(),
                    'account_id' => $receivableAccount->id,
                    'debit' => 0,
                    'credit' => $totalSalesAmount,
                    'sub_ledger_type' => Pelanggan::class,
                    'sub_ledger_id' => $request->pelanggan_id,
                ]);

                // 3. Debit: Persediaan (1105) - Menambah Persediaan
                $journalEntry->items()->create([
                    'user_id' => auth()->id(),
                    'account_id' => $inventoryAccount->id,
                    'debit' => $totalCogsAmount,
                    'credit' => 0,
                ]);

                // 4. Credit: HPP (5101) - Mengurangi HPP
                $journalEntry->items()->create([
                    'user_id' => auth()->id(),
                    'account_id' => $hppAccount->id,
                    'debit' => 0,
                    'credit' => $totalCogsAmount,
                ]);
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error generating memo kredit', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return false;
        }
    }

    public function destroy($fakturId)
    {
        DB::beginTransaction();
        try {
            $memo = MemoKredit::with('memoKreditDetail')
                ->where('faktur_penjualan_id', $fakturId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            foreach ($memo->memoKreditDetail as $detail) {
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

                    // Reversal: Barang yang sebelumnya diterima kembali, sekarang dikeluarkan (batal retur)
                    $diterima = 0;
                    $dikeluarkan = $detail->kuantitas;

                    $saldoPersatuanBaru = $saldoPersatuanSebelumnya + $diterima - $dikeluarkan;

                    $pcsPerKemasan = $barang->jumlah_unit_per_kemasan ?: 1;
                    $saldoPerKemasanBaru = $saldoPerKemasanSebelumnya +
                        round(($diterima - $dikeluarkan) / $pcsPerKemasan, 0);

                    KartuGudang::create([
                        'barang_id' => $barang->id,
                        'tanggal' => now(),
                        'diterima' => $diterima,
                        'dikeluarkan' => $dikeluarkan,
                        'uraian' => 'Pembatalan Memo Kredit - '.$memo->nomor_memo,
                        'saldo_persatuan' => $saldoPersatuanBaru,
                        'saldo_perkemasan' => $saldoPerKemasanBaru,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            // Hapus Journal Entry terkait
            JournalEntry::where('user_id', auth()->id())
                ->where('description', 'Memo Kredit - '.$memo->nomor_memo)
                ->delete();

            // Hapus detail memo
            $memo->memoKreditDetail()->delete();

            // Hapus memo utama
            $memo->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting memo kredit: ', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return false;
        }
    }

    public function generatePdf($fakturId)
    {
        $faktur = FakturPenjualan::where('user_id', auth()->id())->findOrFail($fakturId);
        $memo = MemoKredit::with('memoKreditDetail')
            ->where('faktur_penjualan_id', $fakturId)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $profileUser = Auth::user();

        // Generate PDF
        $pdf = Pdf::loadView(
            'administrasi.surat.memo-kredit.template-pdf',
            compact('faktur', 'memo', 'profileUser'),
        )->setPaper('A4', 'portrait');

        return $pdf->download(
            Str::slug('memo_kredit_'.$memo->nomor_memo).'.pdf',
        );
    }

    private function cleanRupiah(string|int $value): int
    {
        return (int) preg_replace("/\D/", '', $value);
    }

    private function generateNomorMemo()
    {
        $userId = auth()->id();
        $now = now();
        $tahunBulan = $now->format('Ym');

        // 1. Cari nomor terakhir untuk user ini di bulan & tahun yang sama
        $lastMemo = MemoKredit::where('user_id', $userId)
            ->whereYear('tanggal', $now->year)
            ->whereMonth('tanggal', $now->month)
            ->latest('id')
            ->first();

        // 2. Tentukan nomor urut
        if (! $lastMemo) {
            $nextNumber = 1;
        } else {
            // Asumsi format: MK/001/202405/0001
            $parts = explode('/', $lastMemo->nomor_memo);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }

        // 3. Format string (MK / ID User / YYYYMM / 0001)
        return sprintf(
            'MK/%s/%s/%s',
            str_pad($userId, 3, '0', STR_PAD_LEFT),
            $tahunBulan,
            str_pad($nextNumber, 4, '0', STR_PAD_LEFT),
        );
    }
}
