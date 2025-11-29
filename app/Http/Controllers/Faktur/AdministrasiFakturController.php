<?php

namespace App\Http\Controllers\Faktur;

use App\Services\AdministrasiFakturService;
use App\Http\Controllers\Controller;
use App\Models\Faktur\FakturPenjualan;
use App\Services\ManajemenRapatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdministrasiFakturController extends Controller
{
    public function index()
    {
        $faktur = FakturPenjualan::where("user_id", auth()->id())->get();
        return view(
            "administrasi.surat.faktur-penjualan.index",
            compact("faktur"),
        );
    }

    public function create()
    {
        return view("administrasi.surat.faktur-penjualan.create");
    }

    public function store(Request $request)
    {
        try {
            $manajemenRapatServices = app(AdministrasiFakturService::class);
            $manajemenRapatServices->store($request->all());
            return redirect()
                ->route("administrasi.faktur-penjualan.index")
                ->with("success", "Faktur penjualan berhasil ditambahkan.");
        } catch (\Throwable $th) {
            Log::error(
                "Gagal menambahkan faktur penjualan: " . $th->getMessage(),
            );
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Gagal menambahkan faktur penjualan: " . $th->getMessage(),
                );
        }
    }

    public function show($id)
    {
        return view("administrasi.surat.faktur-penjualan.show", [
            "faktur" => FakturPenjualan::findOrFail($id),
        ]);
    }

    public function edit($id)
    {
        return view("administrasi.surat.faktur-penjualan.edit", [
            "faktur" => FakturPenjualan::findOrFail($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        // Implement update logic here
    }

    public function generatePdf($id)
    {
        $manajemenRapatServices = app(AdministrasiFakturService::class);
        return $manajemenRapatServices->generatePdf($id);
    }

    public function destroy($id)
    {
        // Implement destroy logic here
        try {
            $manajemenRapatServices = app(AdministrasiFakturService::class);
            $manajemenRapatServices->destroy($id);
            return redirect()
                ->route("administrasi.faktur-penjualan.index")
                ->with("success", "Faktur penjualan berhasil dihapus🗑️.");
        } catch (\Throwable $th) {
            Log::error(
                "Gagal menghapus faktur penjualan: " . $th->getMessage(),
            );
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Gagal menghapus faktur penjualan: " . $th->getMessage(),
                );
        }
    }
}
