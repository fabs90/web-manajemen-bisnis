<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBarangRequest;
use App\Http\Requests\UpdateBarangRequest;
use App\Models\Barang;
use App\Models\KartuGudang;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BarangController extends Controller
{
    public function index(): View
    {
        $barang = Barang::where('user_id', auth()->id())->get();

        if (auth()->user()->role === 'nelayan') {
            return view('barang.nelayan.index', compact('barang'));
        }

        return view('barang.index', compact('barang'));
    }

    public function create(): View
    {
        if (auth()->user()->role === 'nelayan') {
            return view('barang.nelayan.create');
        }

        return view('barang.create');
    }

    public function store(StoreBarangRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $data = Barang::create(array_merge($validated, [
                'user_id' => auth()->id(),
            ]));

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
        } catch (Exception $e) {
            Log::error('Gagal menyimpan barang: '.$e->getMessage());

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
        if (! $barang) {
            return back()->with([
                'error' => 'Barang tidak ditemukan.',
            ]);
        }

        if (auth()->user()->role === 'nelayan') {
            return view('barang.nelayan.edit', compact('barang'));
        }

        return view('barang.edit', compact('barang'));
    }

    public function update(UpdateBarangRequest $request, int|string $id): RedirectResponse
    {
        $barang = Barang::where('user_id', auth()->id())->find($id);
        if (! $barang) {
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
}
