<?php

namespace App\Services;

use App\Models\SPB\SuratPengirimanBarangDetail;
use Illuminate\Support\Facades\{Auth, DB, Log};
use App\Models\BukuBesarPiutang;
use App\Models\MemoKredit\{MemoKredit, MemoKreditDetail};
use App\Models\Faktur\FakturPenjualan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class MemoKreditService
{
    public function store($request)
    {
        DB::beginTransaction();
        try {
            $total = 0;

            foreach ($request->barang_id as $index => $barangDetailId) {
                $total += $this->cleanRupiah($request->total[$index]);
            }

            $nomorMemo = $this->generateNomorMemo();

            // Simpan memo utama
            $memo = MemoKredit::create([
                'nomor_memo' => $nomorMemo,
                'tanggal' => $request->tanggal,
                'faktur_penjualan_id' => $request->faktur_id,
                'alasan_pengembalian' => $request->alasan_pengembalian,
                'total' => $total,
                'user_id' => auth()->id(),
            ]);

            // Simpan detail memo kredit
            foreach ($request->barang_id as $index => $barangDetailId) {
                $detailBarang = SuratPengirimanBarangDetail::with('pesananPembelianDetail')
                    ->where('spp_detail_id', $barangDetailId)
                    ->first();

                MemoKreditDetail::create([
                    'memo_kredit_id' => $memo->id,
                    'nama_barang' => $detailBarang->pesananPembelianDetail->nama_barang ?? '-',
                    'kuantitas' => $request->jumlah_dikembalikan[$index],
                    'harga_satuan' => $this->cleanRupiah($request->harga[$index]),
                    'jumlah' => $this->cleanRupiah($request->total[$index]),
                ]);
            }

            // Ambil data faktur
            $dataFaktur = FakturPenjualan::findOrFail($request->faktur_id);

            $pelangganId = $request->pelanggan_id ?? $request->supplier_id;

            // Ambil saldo terakhir pelanggan
            $lastPiutang = BukuBesarPiutang::where('user_id', auth()->id())
                ->where('pelanggan_id', $pelangganId)
                ->latest()
                ->first();
            $saldoAwal = $lastPiutang->saldo ?? 0;
            $namaRelasi =
                $dataFaktur->suratPengirimanBarang?->pesananPembelian?->pelanggan?->nama
                ?? $dataFaktur->suratPengirimanBarang?->pesananPembelian?->supplier?->nama
                ?? '-';

            // Simpan buku besar piutang
            BukuBesarPiutang::create([
                'pelanggan_id' => $pelangganId,
                'kode' => $dataFaktur->kode_faktur,
                'uraian' => 'Memo Kredit - ' . $namaRelasi,
                'tanggal' => $dataFaktur->tanggal_faktur,
                'debit' => 0,
                'kredit' => $total,
                'saldo' => $saldoAwal - $total,
                'buku_besar_pendapatan_id' => null,
                'buku_besar_pengeluaran_id' => null,
                'neraca_awal_id' => null,
                'neraca_akhir_id' => null,
                'user_id' => auth()->id(),
            ]);

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
            $memo = MemoKredit::with("memoKreditDetail")
                ->where("faktur_penjualan_id", $fakturId)
                ->first();

            $dataFaktur = FakturPenjualan::where('id', $fakturId)->where('user_id', auth()->user()->id)->first();
            // Hitung total memo (form sudah punya, jadi pakai langsung)
            $total = $memo->total;

            // Ambil last saldo piutang pelanggan
            $lastBukuPiutang = BukuBesarPiutang::where(
                "user_id",
                auth()->user()->id,
            )
                ->where("pelanggan_id", $dataFaktur->suratPengirimanBarang->pesananPembelian->pelanggan->id ?? $dataFaktur->suratPengirimanBarang->pesananPembelian->supplier->id)
                ->latest()
                ->first();
            $newSaldo = $lastBukuPiutang
                ? $lastBukuPiutang->saldo + $total
                : $total;

            // Insert pembalik ke Buku Piutang
            BukuBesarPiutang::create([
                "pelanggan_id" => $dataFaktur->suratPengirimanBarang->pesananPembelian->pelanggan->id ?? $dataFaktur->suratPengirimanBarang->pesananPembelian->supplier->id,
                "kode" => $dataFaktur->kode_faktur,
                "uraian" => "Pembatalan Memo Kredit - " . $memo->nomor_memo,
                "tanggal" => now()->format("Y-m-d"),
                "debit" => $total, // debit = menambah piutang kembali
                "kredit" => 0,
                "saldo" => $newSaldo,
                "buku_besar_pendapatan_id" => null,
                "buku_besar_pengeluaran_id" => null,
                "neraca_awal_id" => null,
                "neraca_akhir_id" => null,
                "user_id" => auth()->user()->id,
            ]);

            // Hapus detail memo
            MemoKreditDetail::where("memo_kredit_id", $memo->id)->delete();

            // Hapus memo utama
            $memo->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting memo kredit: ", [
                "error" => $e->getMessage(),
                "line" => $e->getLine(),
                "file" => $e->getFile(),
            ]);
            return false;
        }
    }

    public function generatePdf($fakturId)
    {
        $faktur = FakturPenjualan::find($fakturId);
        $memo = MemoKredit::with("memoKreditDetail")
            ->where("faktur_penjualan_id", $fakturId)
            ->first();
        $profileUser = Auth::user();
        // Generate PDF
        $pdf = Pdf::loadView(
            "administrasi.surat.memo-kredit.template-pdf",
            compact("faktur", "memo", "profileUser"),
        );
        return $pdf->download(Str::slug("memo_kredit_- " . $memo->nomor_memo) . ".pdf");
    }

    private function cleanRupiah(string|int $value): int
    {
        return (int) preg_replace("/\D/", "", $value);
    }

    private function generateNomorMemo()
    {
        $userId = auth()->id();
        $now = now();
        $tahunBulan = $now->format('Ym'); // Hasil: 202405

        // 1. Cari nomor terakhir untuk user ini di bulan & tahun yang sama
        $lastMemo = MemoKredit::where('user_id', $userId)
            ->whereYear('tanggal', $now->year)
            ->whereMonth('tanggal', $now->month)
            ->latest('id')
            ->first();

        // 2. Tentukan nomor urut
        if (!$lastMemo) {
            $nextNumber = 1;
        } else {
            $lastNumber = (int) substr($lastMemo->nomor_memo, -4);
            $nextNumber = $lastNumber + 1;
        }

        // 3. Format string (MK / ID User / YYYYMM / 0001)
        return sprintf(
            "MK/%s/%s/%s",
            str_pad($userId, 3, '0', STR_PAD_LEFT),
            $tahunBulan,
            str_pad($nextNumber, 4, '0', STR_PAD_LEFT)
        );
    }
}
