<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\KartuGudang;
use App\Models\SPP\PesananPembelian;
use App\Models\SPP\PesananPembelianDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SuratPesananPembelianService
{
    public function store($request)
    {
        DB::beginTransaction();
        try {
            if ($request['jenis'] == 'transaksi_keluar') {
                $suratPesananPembelian = PesananPembelian::create([
                    'jenis' => 'transaksi_keluar',
                    'pelanggan_id' => null,
                    'supplier_id' => $request->supplier_id,
                    'nomor_pesanan_pembelian' => $request->nomor_pesanan_pembelian,
                    'tanggal_pesanan_pembelian' => $request->tanggal_pesanan_pembelian,
                    'tanggal_kirim_pesanan_pembelian' => $request->tanggal_kirim_pesanan_pembelian,
                    'nama_bagian_pembelian' => $request->nama_bagian_pembelian,
                    'user_id' => auth()->id(),
                ]);
            } else {
                $suratPesananPembelian = PesananPembelian::create([
                    'jenis' => 'transaksi_masuk',
                    'pelanggan_id' => $request->pelanggan_id,
                    'supplier_id' => null,
                    'nomor_pesanan_pembelian' => $request->nomor_pesanan_pembelian,
                    'tanggal_pesanan_pembelian' => $request->tanggal_pesanan_pembelian,
                    'tanggal_kirim_pesanan_pembelian' => $request->tanggal_kirim_pesanan_pembelian,
                    'nama_bagian_pembelian' => $request->nama_bagian_pembelian,
                    'user_id' => auth()->id(),
                ]);
            }

            foreach ($request->detail as $item) {
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
                    $dikeluarkan = 0;

                    if ($request['jenis'] == 'transaksi_keluar') {
                        $diterima = $kuantitas;
                    } else {
                        $dikeluarkan = $kuantitas;
                    }

                    $saldoPersatuanBaru = $saldoPersatuanSebelumnya + $diterima - $dikeluarkan;

                    $pcsPerKemasan = $barang->jumlah_unit_per_kemasan ?: 1;
                    $saldoPerKemasanBaru = $saldoPerKemasanSebelumnya +
                        round(($diterima - $dikeluarkan) / $pcsPerKemasan, 0);

                    KartuGudang::create([
                        'barang_id' => $barang->id,
                        'tanggal' => $request->tanggal_pesanan_pembelian,
                        'diterima' => $diterima,
                        'dikeluarkan' => $dikeluarkan,
                        'uraian' => 'Pesanan Pembelian - '.$suratPesananPembelian->nomor_pesanan_pembelian,
                        'saldo_persatuan' => $saldoPersatuanBaru,
                        'saldo_perkemasan' => $saldoPerKemasanBaru,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return $suratPesananPembelian;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($sppId)
    {
        try {
            $suratPesananPembelian = PesananPembelian::where(
                'user_id',
                auth()->user()->id,
            )->findOrFail($sppId);
            $suratPesananPembelian->pesananPembelianDetail()->delete();
            $suratPesananPembelian->delete();

            return true;
        } catch (Exception $e) {
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
