<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrinterController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('pengaturan.printer.index', compact('user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'is_printer_enabled' => 'boolean',
            'printer_store_name' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $user->update([
            'is_printer_enabled' => $request->has('is_printer_enabled'),
            'printer_store_name' => $request->printer_store_name,
        ]);

        return redirect()->back()->with('success', 'Pengaturan printer berhasil disimpan.');
    }
}
