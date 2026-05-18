<?php

namespace App\Services;

use App\Http\Requests\PermintaanKasKecilRequest;
use App\Models\BukuBesarKas;
use App\Models\KasKecil;
use App\Models\KasKecilDetail;
use App\Models\KasKecilFormulir;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermintaanKasKecilService
{
    /**
     * Store a new Permintaan Kas Kecil transaction.
     *
     * @throws \Exception
     */
    public function store(PermintaanKasKecilRequest $request): KasKecil
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $userId = Auth::id();
            $total = $this->cleanRupiah($data['total']);

            // 1. Get current balance and validate
            $saldoLama = KasKecil::where('user_id', $userId)
                ->latest()
                ->value('saldo_akhir') ?? 0;

            $isPengeluaran = ($data['jenis'] === 'pengeluaran');

            if ($isPengeluaran && $total > $saldoLama) {
                throw new \Exception('Saldo kas kecil tidak mencukupi untuk pengeluaran ini. Saldo saat ini: Rp '.number_format($saldoLama, 0, ',', '.'));
            }

            // 2. Calculate new values
            $penerimaan = $isPengeluaran ? 0 : $total;
            $pengeluaran = $isPengeluaran ? $total : 0;
            $saldoBaru = $saldoLama + $penerimaan - $pengeluaran;

            // 3. Save Transaction
            $kasKecil = KasKecil::create([
                'user_id' => $userId,
                'tanggal' => $data['tanggal'],
                'nomor_referensi' => $data['nomor'],
                'penerimaan' => $penerimaan,
                'pengeluaran' => $pengeluaran,
                'saldo_akhir' => $saldoBaru,
            ]);

            // 3a. Record to BukuBesarKas if it's an addition (penambahan)
            if (! $isPengeluaran) {
                $kasBesarLatest = BukuBesarKas::where('user_id', $userId)->latest()->first();
                $saldoKasBesarBaru = ($kasBesarLatest->saldo ?? 0) - $total;

                BukuBesarKas::create([
                    'kode' => $data['nomor'],
                    'tanggal' => $data['tanggal'],
                    'uraian' => 'Penambahan Kas Kecil - Ref: '.$data['nomor'],
                    'debit' => 0,
                    'kredit' => $total,
                    'saldo' => $saldoKasBesarBaru,
                    'user_id' => $userId,
                ]);
            }

            // 4. Save Formulir and Details
            $this->saveFormulir($kasKecil, $request, $data, $userId);
            $this->saveDetails($kasKecil, $request, $userId);

            return $kasKecil;
        });
    }

    /**
     * Save the formulir and upload necessary signatures.
     */
    private function saveFormulir(KasKecil $kasKecil, $request, array $data, int $userId): void
    {
        KasKecilFormulir::create([
            'user_id' => $userId,
            'kas_kecil_id' => $kasKecil->id,
            'nama_pemohon' => $data['nama_pemohon'],
            'departemen' => $data['departemen'],
            'nama_atasan_langsung' => $data['nama_atasan_langsung'],
            'nama_bagian_keuangan' => $data['nama_bagian_keuangan'],
            'ttd_nama_pemohon' => $this->uploadFile($request, 'ttd_nama_pemohon', 'kas_kecil/ttd_pemohon'),
            'ttd_atasan_langsung' => $this->uploadFile($request, 'ttd_nama_atasan_langsung', 'kas_kecil/ttd_atasan'),
            'ttd_bagian_keuangan' => $this->uploadFile($request, 'ttd_nama_bagian_keuangan', 'kas_kecil/ttd_keuangan'),
        ]);
    }

    /**
     * Save transaction details.
     */
    private function saveDetails(KasKecil $kasKecil, $request, int $userId): void
    {
        if (empty($request->keterangan)) {
            return;
        }

        foreach ($request->keterangan as $index => $keterangan) {
            KasKecilDetail::create([
                'user_id' => $userId,
                'kas_kecil_id' => $kasKecil->id,
                'keterangan' => $keterangan,
                'kategori' => $request->kategori[$index] ?? null,
                'jumlah' => $this->cleanRupiah($request->jumlah[$index] ?? 0),
            ]);
        }
    }

    private function cleanRupiah(string|int $value): int
    {
        return (int) preg_replace('/\D/', '', (string) $value);
    }

    private function uploadFile($request, string $field, string $folder): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        return $request->file($field)->store($folder, 'public');
    }
}
