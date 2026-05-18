<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pelanggan;
use App\Services\ReturService;
use Illuminate\Http\Request;
use Throwable;

class ReturController extends Controller
{
    public function __construct(protected ReturService $service) {}

    public function list()
    {
        $returPenjualan = $this->service->getReturPenjualanList();
        $returPengeluaran = $this->service->getReturPembelianList();

        return view(
            'retur-kredit.list',
            compact('returPenjualan', 'returPengeluaran'),
        );
    }

    public function create()
    {
        $userId = auth()->id();
        $debitur = Pelanggan::where('user_id', $userId)
            ->where('jenis', 'debitur')
            ->orderBy('nama')
            ->get();

        $listPiutang = $this->service->getActivePiutang($userId);
        $barang = Barang::where('user_id', $userId)->get();

        return view('retur-kredit.create', compact('debitur', 'listPiutang', 'barang'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'nama_pelanggan' => 'required|exists:pelanggan,id',
            'retur_penanganan' => 'required|in:kurangi_piutang,tunai_kembali',
            'retur_keterangan' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric|min:0',
        ]);

        try {
            $this->service->storeReturPenjualan($validated);

            return redirect()
                ->route('retur.list')
                ->with('success', 'Retur penjualan berhasil dicatat.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal mencatat retur: '.$e->getMessage());
        }
    }

    public function create_retur_pembelian()
    {
        $userId = auth()->id();
        $kreditur = Pelanggan::where('user_id', $userId)
            ->where('jenis', 'kreditur')
            ->orderBy('nama')
            ->get();

        $listHutang = $this->service->getActiveHutang($userId);
        $barang = Barang::where('user_id', $userId)->get();

        return view(
            'retur-kredit.create-pembelian',
            compact('kreditur', 'listHutang', 'barang'),
        );
    }

    public function store_retur_pembelian(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'nama_pelanggan' => 'required|exists:pelanggan,id',
            'retur_penanganan' => 'required|in:kurangi_hutang,tunai_kembali',
            'retur_keterangan' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric|min:0',
        ]);

        try {
            $this->service->storeReturPembelian($validated);

            return redirect()
                ->route('retur.list')
                ->with('success', 'Retur pembelian berhasil dicatat.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal mencatat retur: '.$e->getMessage());
        }
    }
}
