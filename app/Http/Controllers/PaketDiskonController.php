<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaketDiskonRequest;
use App\Models\Barang;
use App\Models\PaketDiskon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PaketDiskonController extends Controller
{
    public function index(): View
    {
        $paketDiskons = PaketDiskon::where('user_id', auth()->id())
            ->with('barang')
            ->latest()
            ->get();

        return view('keuangan.paket-diskon.index', compact('paketDiskons'));
    }

    public function create(): View
    {
        $barang = Barang::where('user_id', auth()->id())->get();
        return view('keuangan.paket-diskon.create', compact('barang'));
    }

    public function store(PaketDiskonRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        PaketDiskon::create($data);

        return redirect()->route('keuangan.paket-diskon.index')->with('success', 'Paket Diskon berhasil ditambahkan.');
    }

    public function edit(PaketDiskon $paketDiskon): View
    {
        if ($paketDiskon->user_id !== auth()->id()) {
            abort(403);
        }
        $barang = Barang::where('user_id', auth()->id())->get();

        return view('keuangan.paket-diskon.edit', compact('paketDiskon', 'barang'));
    }

    public function update(PaketDiskonRequest $request, PaketDiskon $paketDiskon): RedirectResponse
    {
        if ($paketDiskon->user_id !== auth()->id()) {
            abort(403);
        }
        $paketDiskon->update($request->validated());

        return redirect()->route('keuangan.paket-diskon.index')->with('success', 'Paket Diskon berhasil diperbarui.');
    }

    public function destroy(PaketDiskon $paketDiskon): RedirectResponse
    {
        if ($paketDiskon->user_id !== auth()->id()) {
            abort(403);
        }
        $paketDiskon->delete();

        return redirect()->route('keuangan.paket-diskon.index')->with('success', 'Paket Diskon berhasil dihapus.');
    }
}
