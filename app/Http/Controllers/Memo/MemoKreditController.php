<?php

namespace App\Http\Controllers\Memo;

use App\Http\Controllers\Controller;
use App\Models\Faktur\FakturPenjualan;
use App\Models\MemoKredit\MemoKredit;
use App\Services\MemoKreditService;
use Illuminate\Http\Request;

class MemoKreditController extends Controller
{
    public function index()
    {
        $memoKredit = FakturPenjualan::with("memoKredit")
            ->where("user_id", auth()->id())
            ->get();
        return view(
            "administrasi.surat.memo-kredit.index",
            compact("memoKredit"),
        );
    }

    public function create($fakturId)
    {
        $faktur = FakturPenjualan::findOrFail($fakturId);
        return view("administrasi.surat.memo-kredit.create", compact("faktur"));
    }

    public function store(Request $request, MemoKreditService $services)
    {
        $serviceApp = $services->store($request);
        if ($serviceApp) {
            return redirect()
                ->route("administrasi.memo-kredit.index")
                ->with("success", "Memo Kredit berhasil disimpan!");
        }
        return redirect()
            ->route("administrasi.memo-kredit.index")
            ->with("error", "Memo Kredit gagal disimpan!");
    }

    public function destroy($fakturId, MemoKreditService $services)
    {
        $serviceApp = $services->destroy($fakturId);
        if ($serviceApp) {
            return redirect()
                ->route("administrasi.memo-kredit.index")
                ->with("success", "Memo Kredit berhasil dihapus!");
        }
        return redirect()
            ->route("administrasi.memo-kredit.index")
            ->with("error", "Memo Kredit gagal dihapus!");
    }

    public function generatePdf($fakturId, MemoKreditService $services)
    {
        return $services->generatePdf($fakturId);
    }
}
