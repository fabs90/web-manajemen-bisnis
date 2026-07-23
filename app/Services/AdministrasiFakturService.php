<?php

namespace App\Services;

use App\Jobs\SendSFakturPenjualanJob;
use App\Models\Account;
use App\Models\Barang;
use App\Models\Faktur\FakturPenjualan;
use App\Models\JournalEntry;
use App\Models\Pelanggan;
use App\Models\SPB\SuratPengirimanBarang;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdministrasiFakturService
{
    public function store($data)
    {
        DB::beginTransaction();
        try {
            $faktur = FakturPenjualan::create([
                'spb_id' => $data['spb_id'],
                'kode_faktur' => $data['kode_faktur'],
                'tanggal_faktur' => $data['tanggal_faktur'],
                'user_id' => auth()->user()->id,
            ]);

            // Penjurnalan (Journaling)
            $spb = SuratPengirimanBarang::with([
                'pesananPenjualan.pelanggan',
                'suratPengirimanBarangDetail.pesananPenjualanDetail.barang',
            ])->find($data['spb_id']);

            if ($spb && $spb->pesananPenjualan) {
                $totalSalesAmount = 0;
                $totalHppAmount = 0;

                foreach ($spb->suratPengirimanBarangDetail as $detail) {
                    $qty = $detail->jumlah_dikirim;
                    if ($qty > 0 && $detail->pesananPenjualanDetail) {
                        $hargaJual = $detail->pesananPenjualanDetail->harga ?? 0;
                        $totalSalesAmount += ($qty * $hargaJual);

                        $barangId = $detail->pesananPenjualanDetail->barang_id;
                        $barang = null;
                        if ($barangId) {
                            $barang = Barang::where('id', $barangId)->where('user_id', auth()->id())->first();
                        } else {
                            $barang = Barang::where('nama', $detail->pesananPenjualanDetail->nama_barang)->where('user_id', auth()->id())->first();
                        }

                        if ($barang) {
                            $totalHppAmount += ($qty * $barang->harga_beli_per_unit);
                        }
                    }
                }

                $piutangAccount = Account::where('user_id', auth()->id())->where('code', '1104')->first();
                $persediaanAccount = Account::where('user_id', auth()->id())->where('code', '1105')->first();
                $pendapatanAccount = Account::where('user_id', auth()->id())->where('code', '4101')->first();
                $hppAccount = Account::where('user_id', auth()->id())->where('code', '5101')->first();

                if ($piutangAccount && $persediaanAccount && $pendapatanAccount && $hppAccount) {
                    $journalEntry = JournalEntry::create([
                        'user_id' => auth()->id(),
                        'reference_number' => 'FAK-'.date('Ymd', strtotime($data['tanggal_faktur'])).'-'.strtoupper(Str::random(6)),
                        'date' => $data['tanggal_faktur'],
                        'description' => 'Faktur Penjualan Kredit - '.$faktur->kode_faktur,
                        'transaction_type' => 'penjualan',
                    ]);

                    // 1. Debit: Piutang Usaha
                    $journalEntry->items()->create([
                        'user_id' => auth()->id(),
                        'account_id' => $piutangAccount->id,
                        'debit' => $totalSalesAmount,
                        'credit' => 0,
                        'sub_ledger_type' => Pelanggan::class,
                        'sub_ledger_id' => $spb->pesananPenjualan->pelanggan_id ?? null,
                    ]);

                    // 2. Credit: Pendapatan Penjualan
                    $journalEntry->items()->create([
                        'user_id' => auth()->id(),
                        'account_id' => $pendapatanAccount->id,
                        'debit' => 0,
                        'credit' => $totalSalesAmount,
                    ]);

                    // 3. Debit: HPP
                    $journalEntry->items()->create([
                        'user_id' => auth()->id(),
                        'account_id' => $hppAccount->id,
                        'debit' => $totalHppAmount,
                        'credit' => 0,
                    ]);

                    // 4. Credit: Persediaan
                    $journalEntry->items()->create([
                        'user_id' => auth()->id(),
                        'account_id' => $persediaanAccount->id,
                        'debit' => 0,
                        'credit' => $totalHppAmount,
                    ]);
                }
            }

            DB::commit();

            // Dispatch job email
            SendSFakturPenjualanJob::dispatch($faktur, auth()->user());

            return $faktur;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function generatePdf($id)
    {
        try {
            $faktur = FakturPenjualan::with([
                'suratPengirimanBarang',
                'suratPengirimanBarang.pesananPenjualan.pelanggan',
            ])
                ->where('user_id', auth()->id())
                ->findOrFail($id);

            $profileUser = Auth::user();

            $pdf = Pdf::loadView(
                'administrasi.surat.faktur-penjualan.template-pdf',
                compact('faktur', 'profileUser'),
            )->setPaper('A4', 'portrait');

            return $pdf->download(
                Str::slug('Faktur Penjualan-'.
                    $faktur->kode_faktur)
                .'.pdf',
            );
        } catch (\Exception $e) {
            Log::error('Error generate PDF faktur: '.$e->getMessage());
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $faktur = FakturPenjualan::where('user_id', auth()->id())
                ->findOrFail($id);

            // Hapus Journal Entry terkait Faktur
            JournalEntry::where('user_id', auth()->id())
                ->where('description', 'Faktur Penjualan - '.$faktur->kode_faktur)
                ->delete();

            $faktur->delete();

            DB::commit();

            return true;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning(
                "Faktur tidak ditemukan saat destroy: ID $id | User: ".
                auth()->id(),
            );

            return false;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus faktur: '.$e->getMessage());

            return false;
        }
    }
}
