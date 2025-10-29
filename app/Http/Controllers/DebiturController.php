<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Http\Requests\DebiturRequest;
use Illuminate\Http\Request;

class DebiturController extends Controller
{
    public function list()
    {
        $pelanggan = Pelanggan::all();
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
}
