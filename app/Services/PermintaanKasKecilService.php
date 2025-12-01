<?php

namespace App\Services;

use App\Http\Requests\PermintaanKasKecilRequest;
use App\Models\KasKecil;
use App\Models\KasKecilFormulir;
use App\Models\KasKecilDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PermintaanKasKecilService
{
    /**
     * Simpan permintaan kas kecil baru.
     *
     * @return KasKecil  // berhasil
     * @throws \Exception // gagal (akan ditangkap controller)
     */
    public function store(PermintaanKasKecilRequest $request): KasKecil
    {
        $data = $request->validated();

        return DB::transaction(function () use ($request, $data) {
            // 1. Ambil saldo terakhir
            $saldoLama = KasKecil::where('user_id', auth()->id())
                ->latest('tanggal') // lebih eksplisit dari ->latest()
                ->value('saldo_akhir') ?? 0;

            $total = $this->cleanRupiah($data['total']);

            // 2. Validasi bisnis: saldo tidak boleh minus
            if ($data['jenis'] === 'pengeluaran' && $total > $saldoLama) {
                throw new \Exception('Saldo kas kecil tidak mencukup untuk pengeluaran ini.');
            }

            // 3. Hitung nilai baru
            $penerimaan = $data['jenis'] === 'pengeluaran' ? 0 : $total;
            $pengeluaran = $data['jenis'] === 'pengeluaran' ? $total : 0;
            $saldoBaru = $saldoLama + $penerimaan - $pengeluaran;

            // 4. Simpan transaksi kas kecil
            $kasKecil = KasKecil::create([
                'user_id' => auth()->id(),
                'tanggal' => $data['tanggal'],
                'nomor_referensi' => $data['nomor'],
                'penerimaan' => $penerimaan,
                'pengeluaran' => $pengeluaran,
                'saldo_akhir' => $saldoBaru,
            ]);

            // 5. Upload tanda tangan
            $ttdPemohon = $this->uploadFile($request, 'ttd_nama_pemohon', 'kas_kecil/ttd_nama_pemohon');
            $ttdAtasan = $this->uploadFile($request, 'ttd_nama_atasan_langsung', 'kas_kecil/ttd_atasan_langsung');
            $ttdKeuangan = $this->uploadFile($request, 'ttd_nama_bagian_keuangan', 'kas_kecil/ttd_bagian_keuangan');

            // 6. Simpan formulir
            KasKecilFormulir::create([
                'user_id' => auth()->id(),
                'kas_kecil_id' => $kasKecil->id,
                'nama_pemohon' => $data['nama_pemohon'],
                'departemen' => $data['departemen'],
                'ttd_nama_pemohon' => $ttdPemohon,
                'nama_atasan_langsung' => $data['nama_atasan_langsung'],
                'ttd_atasan_langsung' => $ttdAtasan,
                'nama_bagian_keuangan' => $data['nama_bagian_keuangan'],
                'ttd_bagian_keuangan' => $ttdKeuangan,
            ]);

            // 7. Simpan detail
            foreach ($request->keterangan as $i => $keterangan) {
                KasKecilDetail::create([
                    'user_id' => auth()->id(),
                    'kas_kecil_id' => $kasKecil->id,
                    'keterangan' => $keterangan,
                    'kategori' => $request->kategori[$i] ?? null,
                    'jumlah' => $this->cleanRupiah($request->jumlah[$i] ?? 0),
                ]);
            }

            return $kasKecil; // berhasil
        });
    }

    private function cleanRupiah(string|int $value): int
    {
        return (int) preg_replace('/\D/', '', $value);
    }

    private function uploadFile($request, string $field, string $folder): ?string
    {
        return $request->hasFile($field)
            ? $request->file($field)->store($folder, 'public')
            : null;
    }
}
