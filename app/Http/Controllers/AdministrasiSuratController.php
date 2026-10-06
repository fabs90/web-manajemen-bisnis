<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AdministrasiSuratController extends Controller
{
    public function index(): View
    {
        return view('administrasi.surat.index');
    }
}
