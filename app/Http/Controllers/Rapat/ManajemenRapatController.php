<?php

namespace App\Http\Controllers\Rapat;

use App\Http\Controllers\Controller;
use App\Models\Rapat\AgendaRapat;
use Illuminate\Http\Request;
use App\Services\ManajemenRapatService;
class ManajemenRapatController extends Controller
{
    public function index()
    {
        $agendaRapat = AgendaRapat::where('user_id', auth()->user()->id)->get();
        return view("administrasi.surat.notulen-rapat.index", compact('agendaRapat'));
    }

    public function create()
    {
        return view("administrasi.surat.notulen-rapat.create");
    }

    public function store(Request $request)
    {
        $validatedData = $request->all();
        $validatedData['user_id'] = auth()->user()->id;
        $manajemenRapatServices = app(ManajemenRapatService::class);
        $manajemenRapatServices->store($validatedData);
        return redirect()->route('administrasi.rapat.index')->with('success', 'Agenda rapat berhasil ditambahkan.');
    }
}
