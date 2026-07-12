<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\KartuGudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BarangController extends Controller
{
    public function index()
    {
        $barang = Barang::where('user_id', auth()->id())->get();

        return view('barang.index', compact('barang'));
    }

    public function create()
    {
        return view('barang.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_barang' => 'required|string|max:100',
            'nama' => 'required|string|max:255',
            'jumlah_max' => 'required|integer|min:0',
            'jumlah_min' => 'required|integer|min:0',
            'jumlah_unit_per_kemasan' => 'required|integer|min:0',
            'harga_beli_per_kemas' => 'required|numeric|min:0',
            'harga_beli_per_unit' => 'required|numeric|min:0',
            'harga_jual_per_unit' => 'required|numeric|min:0',
        ]);

        try {
            $data = Barang::create([
                'kode_barang' => $request->input('kode_barang'),
                'nama' => $request->input('nama'),
                'jumlah_max' => $request->input('jumlah_max'),
                'jumlah_min' => $request->input('jumlah_min'),
                'jumlah_unit_per_kemasan' => $request->input(
                    'jumlah_unit_per_kemasan',
                ),
                'harga_beli_per_kemas' => $request->input(
                    'harga_beli_per_kemas',
                ),
                'harga_beli_per_unit' => $request->input('harga_beli_per_unit'),
                'harga_jual_per_unit' => $request->input('harga_jual_per_unit'),
                'user_id' => auth()->id(),
            ]);

            if (! $data) {
                return back()
                    ->withErrors([
                        'error' => 'Terjadi kesalahan saat menyimpan data barang.',
                    ])
                    ->withInput();
            }

            return redirect()
                ->route('barang.create')
                ->with('success', 'Barang berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan barang: '.$e->getMessage());

            return back()
                ->with([
                    'error' => "Terjadi kesalahan saat menyimpan data barang.: {$e->getMessage()}",
                ])
                ->withInput();
        }
    }

    public function show($id)
    {
        $barang = Barang::find($id);
        if (! $barang) {
            return back()->with([
                'error' => 'Barang tidak ditemukan.',
            ]);
        }

        return view('barang.edit', compact('barang'));
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::find($id);
        if (! $barang) {
            return back()->with([
                'error' => 'Barang tidak ditemukan.',
            ]);
        }

        $validatedData = $request->validate([
            'kode_barang' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'jumlah_max' => 'required|integer|min:0',
            'jumlah_min' => 'required|integer|min:0',
            'jumlah_unit_per_kemasan' => 'required|integer|min:1',
            'harga_beli_per_kemas' => 'required|numeric|min:0',
            'harga_beli_per_unit' => 'required|numeric|min:0',
            'harga_jual_per_unit' => 'required|numeric|min:0',
        ]);

        $barang->update($validatedData);

        return redirect()
            ->route('barang.show', $barang->id)
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $barang = Barang::find($id);
        if (! $barang) {
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

    public function indexKartuGudang()
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

    public function createKartuGudang($barang_id)
    {
        $barang = Barang::find($barang_id);

        return view('kartu-gudang.create', compact('barang'));
    }

    public function storeKartuGudang(Request $request, $barangId)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'uraian' => 'required|string',
            'diterima' => 'nullable|integer|min:0',
            'dikeluarkan' => 'nullable|integer|min:0',
        ]);

        try {
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
                ? ceil($saldoPersatuanBaru / $barang->jumlah_unit_per_kemasan)
                : 0;

            $data = KartuGudang::create([
                'user_id' => auth()->id(),
                'barang_id' => $barangId,
                'tanggal' => $request->tanggal,
                'uraian' => $request->uraian,
                'diterima' => $diterima,
                'dikeluarkan' => $dikeluarkan,
                'saldo_persatuan' => $saldoPersatuanBaru,
                'saldo_perkemasan' => $saldoPerKemasanBaru,
            ]);

            return redirect()
                ->route('kartu-gudang.create', ['barang_id' => $barangId])
                ->with(
                    'success',
                    'Kartu gudang '.$barang->nama.' berhasil ditambahkan.',
                );
        } catch (\Exception $e) {
            return back()
                ->withErrors([
                    'error' => "Terjadi kesalahan saat menyimpan data kartu gudang.: {$e->getMessage()}",
                ])
                ->withInput();
        }
    }

    public function deleteKartuGudang($id)
    {
        $kartuGudang = KartuGudang::findOrFail($id);
        $kartuGudang->delete();

        return redirect()
            ->route('kartu-gudang.index')
            ->with('success', 'Kartu gudang berhasil dihapus.');
    }
}
