<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBarangRequest;
use App\Http\Requests\StoreKartuGudangRequest;
use App\Http\Requests\UpdateBarangRequest;
use App\Models\Account;
use App\Models\Barang;
use App\Models\JournalEntry;
use App\Models\KartuGudang;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BarangController extends Controller
{
    public function index(): View
    {
        $barang = Barang::where('user_id', auth()->id())->get();

        return view('barang.index', compact('barang'));
    }

    public function create(): View
    {
        return view('barang.create');
    }

    public function store(StoreBarangRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $data = Barang::create(array_merge($validated, [
                'user_id' => auth()->id(),
            ]));

            if (!$data) {
                return back()
                    ->withErrors([
                        'error' => 'Terjadi kesalahan saat menyimpan data barang.',
                    ])
                    ->withInput();
            }

            return redirect()
                ->route('barang.create')
                ->with('success', 'Barang berhasil ditambahkan.');
        } catch (Exception $e) {
            Log::error('Gagal menyimpan barang: ' . $e->getMessage());

            return back()
                ->with([
                    'error' => "Terjadi kesalahan saat menyimpan data barang.: {$e->getMessage()}",
                ])
                ->withInput();
        }
    }

    public function show(int|string $id): View|RedirectResponse
    {
        $barang = Barang::where('user_id', auth()->id())->find($id);
        if (!$barang) {
            return back()->with([
                'error' => 'Barang tidak ditemukan.',
            ]);
        }

        return view('barang.edit', compact('barang'));
    }

    public function update(UpdateBarangRequest $request, int|string $id): RedirectResponse
    {
        $barang = Barang::where('user_id', auth()->id())->find($id);
        if (!$barang) {
            return back()->with([
                'error' => 'Barang tidak ditemukan.',
            ]);
        }

        $barang->update($request->validated());

        return redirect()
            ->route('barang.show', $barang->id)
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $barang = Barang::where('user_id', auth()->id())->find($id);
        if (!$barang) {
            return back()->with([
                'error' => 'Barang tidak ditemukan.',
            ]);
        }

        // hapus kartu gudang nya juga
        KartuGudang::where('barang_id', $barang->id)->delete();

        $barang->delete();

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil dihapus.');
    }

    public function indexKartuGudang(): View
    {
        // Eager load kartuGudang to optimize queries
        $barang = Barang::where('user_id', auth()->id())->with('kartuGudang')->get();

        $totalNilaiPersediaan = 0;
        foreach ($barang as $b) {
            // Sort by created_at or id to get the absolute latest entry
            $lastKartu = $b->kartuGudang->sortByDesc('id')->first();
            $saldoAkhir = $lastKartu ? $lastKartu->saldo_persatuan : 0;

            // Simpan nilai persediaan per barang ke property temporary untuk ditampilkan
            $b->saldo_akhir = $saldoAkhir;
            $b->nilai_persediaan = $saldoAkhir * $b->harga_beli_per_unit;

            $totalNilaiPersediaan += $b->nilai_persediaan;
        }

        return view('kartu-gudang.index', compact('barang', 'totalNilaiPersediaan'));
    }

    public function detailKartuGudang(int|string $barang_id): View|RedirectResponse
    {
        $barang = Barang::where('user_id', auth()->id())
            ->with([
                'kartuGudang' => function ($query) {
                    $query->orderBy('tanggal', 'asc')->orderBy('id', 'asc');
                }
            ])
            ->find($barang_id);

        if (!$barang) {
            return redirect()
                ->route('kartu-gudang.index')
                ->with('error', 'Barang tidak ditemukan.');
        }

        $lastKartu = $barang->kartuGudang->sortByDesc('id')->first();
        $saldoAkhir = $lastKartu ? $lastKartu->saldo_persatuan : 0;
        $barang->saldo_akhir = $saldoAkhir;
        $barang->nilai_persediaan = $saldoAkhir * $barang->harga_beli_per_unit;

        return view('kartu-gudang.detail', compact('barang'));
    }

    public function createKartuGudang(int|string $barang_id): View
    {
        $barang = Barang::find($barang_id);

        return view('kartu-gudang.create', compact('barang'));
    }

    public function storeKartuGudang(StoreKartuGudangRequest $request, int|string $barangId): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $barang = Barang::findOrFail($barangId);
            $lastKartu = KartuGudang::where('barang_id', $barangId)
                ->latest()
                ->first();

            $diterima = $request->input('diterima', 0);
            $dikeluarkan = $request->input('dikeluarkan', 0);

            // Ambil saldo sebelumnya (jika ada)
            $saldoPersatuanSebelumnya = $lastKartu->saldo_persatuan ?? 0;

            // Hitung saldo baru
            $saldoPersatuanBaru =
                $saldoPersatuanSebelumnya +
                $diterima -
                $dikeluarkan;

            // Hitung saldo per kemasan secara otomatis (pembulatan ke atas)
            $saldoPerKemasanBaru = $barang->jumlah_unit_per_kemasan > 0
                ? (int) ceil($saldoPersatuanBaru / $barang->jumlah_unit_per_kemasan)
                : 0;

            KartuGudang::create([
                'user_id' => auth()->id(),
                'barang_id' => $barangId,
                'tanggal' => $request->tanggal,
                'uraian' => $request->uraian,
                'diterima' => $diterima,
                'dikeluarkan' => $dikeluarkan,
                'saldo_persatuan' => $saldoPersatuanBaru,
                'saldo_perkemasan' => $saldoPerKemasanBaru,
            ]);

            // Penjurnalan (Account 1105 - Persediaan Barang)
            $inventoryAccount = Account::where('user_id', auth()->id())->where('code', '1105')->first();
            $modalAccount = Account::where('user_id', auth()->id())->where('code', '3100')->first(); // Menggunakan Modal Pemilik sebagai akun penyeimbang

            if ($inventoryAccount && $modalAccount && ($diterima > 0 || $dikeluarkan > 0)) {
                $journalEntry = JournalEntry::create([
                    'user_id' => auth()->id(),
                    'reference_number' => 'KG-' . date('Ymd', strtotime($request->tanggal)) . '-' . strtoupper(Str::random(6)),
                    'date' => $request->tanggal,
                    'description' => 'Penyesuaian Kartu Gudang: ' . $barang->nama . ' (' . $request->uraian . ')',
                    'transaction_type' => 'penyesuaian-kartu-gudang',
                ]);

                if ($diterima > 0) {
                    $amount = $diterima * $barang->harga_beli_per_unit;

                    // Debit: Persediaan Barang
                    $journalEntry->items()->create([
                        'user_id' => auth()->id(),
                        'account_id' => $inventoryAccount->id,
                        'debit' => $amount,
                        'credit' => 0,
                    ]);

                    // Kredit: Modal
                    $journalEntry->items()->create([
                        'user_id' => auth()->id(),
                        'account_id' => $modalAccount->id,
                        'debit' => 0,
                        'credit' => $amount,
                    ]);
                } elseif ($dikeluarkan > 0) {
                    $amount = $dikeluarkan * $barang->harga_beli_per_unit;

                    // Debit: Modal
                    $journalEntry->items()->create([
                        'user_id' => auth()->id(),
                        'account_id' => $modalAccount->id,
                        'debit' => $amount,
                        'credit' => 0,
                    ]);

                    // Kredit: Persediaan Barang
                    $journalEntry->items()->create([
                        'user_id' => auth()->id(),
                        'account_id' => $inventoryAccount->id,
                        'debit' => 0,
                        'credit' => $amount,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('kartu-gudang.detail', ['barang_id' => $barangId])
                ->with(
                    'success',
                    'Kartu gudang ' . $barang->nama . ' berhasil ditambahkan.',
                );
        } catch (Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors([
                    'error' => "Terjadi kesalahan saat menyimpan data kartu gudang.: {$e->getMessage()}",
                ])
                ->withInput();
        }
    }

    public function deleteKartuGudang(int|string $id): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $kartuGudang = KartuGudang::with('barang')->where('user_id', auth()->id())->where('id', $id)->firstOrFail();
            $barangId = $kartuGudang->barang_id;

            // Hapus Journal Entry terkait
            if ($kartuGudang->barang) {
                JournalEntry::where('user_id', auth()->id())
                    ->where('transaction_type', 'penyesuaian-kartu-gudang')
                    ->where('date', $kartuGudang->tanggal)
                    ->where('description', 'Penyesuaian Kartu Gudang: ' . $kartuGudang->barang->nama . ' (' . $kartuGudang->uraian . ')')
                    ->delete();
            }

            $kartuGudang->delete();

            DB::commit();

            return redirect()
                ->route('kartu-gudang.detail', ['barang_id' => $barangId])
                ->with('success', 'Kartu gudang berhasil dihapus.');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('kartu-gudang.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
}
