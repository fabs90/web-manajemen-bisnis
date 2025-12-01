<?php

namespace App\Services;

use App\Models\BukuBesarPiutang;
use App\Models\MemoKredit\MemoKredit;
use App\Models\MemoKredit\MemoKreditDetail;
use App\Models\Faktur\FakturPenjualan;
use App\Models\Faktur\FakturPenjualanDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemoKreditService
{
    public function store($request)
    {
        DB::beginTransaction();
        try {
            $total = 0;
            foreach ($request->barang_id as $index => $barangDetailId) {
                $total +=
                    $request->jumlah_dipesan[$index] *
                    $this->cleanRupiah($request->harga[$index]);
            }
            // simpan memo utama
            $memo = MemoKredit::create([
                "nomor_memo" => $request->nomor_memo,
                "tanggal" => $request->tanggal,
                "faktur_penjualan_id" => $request->faktur_id,
                "alasan_pengembalian" => $request->alasan_pengembalian,
                "total" => $total,
                "user_id" => auth()->user()->id,
            ]);

            // simpan detail memo kredit
            foreach ($request->barang_id as $index => $barangDetailId) {
                $detailBarang = FakturPenjualanDetail::findOrFail(
                    $barangDetailId,
                );
                MemoKreditDetail::create([
                    "memo_kredit_id" => $memo->id,
                    "nama_barang" => $detailBarang->nama_barang,
                    "kuantitas" => $request->jumlah_dipesan[$index],
                    "harga_satuan" => $this->cleanRupiah(
                        $request->harga[$index],
                    ),
                    "jumlah" => $this->cleanRupiah($request->total[$index]),
                ]);
            }

            // Simpan ke Buku Piutang kolom kredit
            $dataFaktur = FakturPenjualan::findOrFail($request->faktur_id);
            $oldBukuPiutang = BukuBesarPiutang::where(
                "user_id",
                auth()->user()->id,
            )
                ->where("pelanggan_id", $dataFaktur->pelanggan->id)
                ->latest()
                ->first();

            if ($oldBukuPiutang) {
                $bukuPiutang = BukuBesarPiutang::create([
                    "pelanggan_id" => $dataFaktur->pelanggan->id,
                    "kode" => $dataFaktur->kode_faktur,
                    "uraian" =>
                        "Memo Kredit " .
                        $dataFaktur->tanggal .
                        " - " .
                        $dataFaktur->pelanggan->nama,
                    "tanggal" => $dataFaktur->tanggal,
                    "debit" => 0,
                    "kredit" => $total,
                    "saldo" => $oldBukuPiutang->saldo - $total,
                    "buku_besar_pendapatan_id" => null,
                    "buku_besar_pengeluaran_id" => null,
                    "neraca_awal_id" => null,
                    "neraca_akhir_id" => null,
                    "user_id" => auth()->user()->id,
                ]);
            } else {
                $saldoBukuPiutangOld = BukuBesarPiutang::where(
                    "user_id",
                    auth()->user()->id,
                )
                    ->latest()
                    ->first();

                $bukuPiutang = BukuBesarPiutang::create([
                    "pelanggan_id" => $dataFaktur->pelanggan->id,
                    "kode" => $dataFaktur->kode_faktur,
                    "uraian" =>
                        "Memo Kredit " .
                        $dataFaktur->tanggal .
                        " - " .
                        $dataFaktur->pelanggan->nama,
                    "tanggal" => $dataFaktur->tanggal,
                    "debit" => 0,
                    "kredit" => $total,
                    "saldo" => $saldoBukuPiutangOld->saldo - $total,
                    "buku_besar_pendapatan_id" => null,
                    "buku_besar_pengeluaran_id" => null,
                    "neraca_awal_id" => null,
                    "neraca_akhir_id" => null,
                    "user_id" => auth()->user()->id,
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error generating memo kredit: " . $e->getMessage());
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

            $dataFaktur = FakturPenjualan::findOrFail(
                $memo->faktur_penjualan_id,
            );

            // Hitung total memo (form sudah punya, jadi pakai langsung)
            $total = $memo->total;

            // Ambil last saldo piutang pelanggan
            $lastBukuPiutang = BukuBesarPiutang::where(
                "user_id",
                auth()->user()->id,
            )
                ->where("pelanggan_id", $dataFaktur->pelanggan->id)
                ->latest()
                ->first();

            $newSaldo = $lastBukuPiutang
                ? $lastBukuPiutang->saldo + $total
                : $total;

            // Insert pembalik ke Buku Piutang
            BukuBesarPiutang::create([
                "pelanggan_id" => $dataFaktur->pelanggan->id,
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
            Log::error("Error deleting memo kredit: " . $e->getMessage());
            return false;
        }
    }

    public function generatePdf($fakturId)
    {
        $faktur = FakturPenjualan::find($fakturId);
        $memo = MemoKredit::with("memoKreditDetail")
            ->where("faktur_penjualan_id", $fakturId)
            ->first();
        $userProfile = Auth::user();
        // Generate PDF
        $pdf = Pdf::loadView(
            "administrasi.surat.memo-kredit.template-pdf",
            compact("faktur", "memo", "userProfile"),
        );
        return $pdf->download("memo_kredit_- " . $memo->nomor_memo . ".pdf");
    }

    private function cleanRupiah(string|int $value): int
    {
        return (int) preg_replace("/\D/", "", $value);
    }
}
