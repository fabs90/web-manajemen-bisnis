<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaketDiskonRequest;
use App\Models\Barang;
use App\Models\PaketDiskon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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

    public function edit($id): View
    {
        $paket_diskon = PaketDiskon::findOrFail($id);
        if ($paket_diskon->user_id != auth()->id()) {
            abort(403);
        }
        $barang = Barang::where('user_id', auth()->id())->get();

        return view('keuangan.paket-diskon.edit', ['paketDiskon' => $paket_diskon, 'barang' => $barang]);
    }

    public function update(PaketDiskonRequest $request, $id): RedirectResponse
    {
        $paket_diskon = PaketDiskon::findOrFail($id);
        if ($paket_diskon->user_id != auth()->id()) {
            abort(403);
        }
        $paket_diskon->update($request->validated());

        return redirect()->route('keuangan.paket-diskon.index')->with('success', 'Paket Diskon berhasil diperbarui.');
    }

    public function destroy($id): RedirectResponse
    {
        $paket_diskon = PaketDiskon::findOrFail($id);
        if ($paket_diskon->user_id != auth()->id()) {
            abort(403);
        }
        $paket_diskon->delete();

        return redirect()->route('keuangan.paket-diskon.index')->with('success', 'Paket Diskon berhasil dihapus.');
    }
}
