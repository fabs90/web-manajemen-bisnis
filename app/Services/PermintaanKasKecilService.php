<?php

namespace App\Services;

use App\Http\Requests\PermintaanKasKecilRequest;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\KasKecil;
use App\Models\KasKecilDetail;
use App\Models\KasKecilFormulir;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

            $formattedDate = now()->format('Ymd');
            $random = strtoupper(Str::random(6));
            $referenceNumber = "PKK-{$formattedDate}-{$random}";

            // 3. Save Transaction
            $kasKecil = KasKecil::create([
                'user_id' => $userId,
                'tanggal' => $data['tanggal'],
                'nomor_referensi' => $referenceNumber,
                'penerimaan' => $penerimaan,
                'pengeluaran' => $pengeluaran,
                'saldo_akhir' => $saldoBaru,
            ]);

            // 3a. Jurnal
            $accounts = Account::where('user_id', $userId)->get()->keyBy('code');

            if ($accounts->isEmpty()) {
                throw new \Exception('Akun belum diatur. Silakan atur akun di Neraca Awal.');
            }

            $kasUtama = $accounts['1101'] ?? $accounts->firstWhere('category', 'asset');
            $kasKecilAccount = $accounts['1102'] ?? $accounts->firstWhere('category', 'asset');
            $expenseAccount = $accounts['5202'] ?? $accounts->firstWhere('category', 'expense');

            if (! $kasUtama || ! $kasKecilAccount || ! $expenseAccount) {
                throw new \Exception('Beberapa akun standar (Kas Utama / Kas Kecil / Beban) tidak ditemukan.');
            }

            $entry = JournalEntry::create([
                'user_id' => $userId,
                'reference_number' => $referenceNumber,
                'date' => $data['tanggal'],
                'description' => ($isPengeluaran ? 'Pengeluaran Kas Kecil: ' : 'Penambahan Kas Kecil: ').$referenceNumber,
                'transaction_type' => 'kas_kecil',
            ]);

            if (! $isPengeluaran) {
                $entry->items()->create([
                    'user_id' => $userId,
                    'account_id' => $kasKecilAccount->id,
                    'debit' => $total,
                    'credit' => 0,
                ]);
                $entry->items()->create([
                    'user_id' => $userId,
                    'account_id' => $kasUtama->id,
                    'debit' => 0,
                    'credit' => $total,
                ]);
            } else {
                $entry->items()->create([
                    'user_id' => $userId,
                    'account_id' => $expenseAccount->id,
                    'debit' => $total,
                    'credit' => 0,
                ]);
                $entry->items()->create([
                    'user_id' => $userId,
                    'account_id' => $kasKecilAccount->id,
                    'debit' => 0,
                    'credit' => $total,
                ]);
            }

            \App\Models\PengisianKasKecilLog::create([
                'journal_entry_id' => $entry->id,
                'kas_kecil_id' => $kasKecil->id,
                'uraian' => ($isPengeluaran ? 'Pengeluaran Kas Kecil: ' : 'Penambahan Kas Kecil: ').$referenceNumber,
                'jumlah' => $total,
                'tanggal_transaksi' => $data['tanggal'],
                'user_id' => $userId,
            ]);

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
