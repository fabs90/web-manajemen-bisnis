<?php

namespace App\Http\Controllers;

use App\Services\KeuanganService;

class NeracaAkhirController extends Controller
{
    public function index(KeuanganService $keuangan)
    {
        $data = $keuangan->hitungNeraca();

        return view('keuangan.neraca-akhir.index', $data);
    }
}
