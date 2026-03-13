<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Http\Requests\DebiturRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebiturController extends Controller
{
    public function list()
    {
        $pelanggan = Pelanggan::where("user_id", auth()->id())->get();
        return view("debitur.list", compact("pelanggan"));
    }

    public function create()
    {
        return view("debitur.create");
    }

    public function store(DebiturRequest $request)
    {
        $validate = $request->validated();

        $debitur = Pelanggan::create([
            "user_id" => auth()->id(),
            "nama" => $validate["nama"],
            "kontak" => $validate["kontak"] ?? null,
            "alamat" => $validate["alamat"] ?? null,
            "email" => $validate["email"] ?? null,
            "jenis" => $validate["jenis"] ?? null,
        ]);

        return redirect()
            ->route("debitur-kreditur.create")
            ->with("success", "Berhasil menambahkan debitur/kreditur.");
    }

    public function destroy(Pelanggan $pelanggan)
    {
        if ($pelanggan) {
            $pelanggan->delete();
            return redirect()
                ->route("debitur-kreditur.list")
                ->with("success", "Berhasil menghapus debitur/kreditur.");
        } else {
            Log::error("Failed to delete debitur/kreditur", [
                "function" => __FUNCTION__,
                "line" => __LINE__,
                "pelanggan_id" => $pelanggan->id ?? null,
            ]);
            return redirect()
                ->route("debitur-kreditur.list")
                ->with("error", "Gagal menghapus debitur/kreditur.");
        }
    }
}
