<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisteredAdminController extends Controller
{
    public function create()
    {
        return view('superadmin.auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->role !== 'superadmin') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->back()->withInput($request->only('email'))->with('error', 'Akun Anda tidak memiliki hak akses sebagai admin.');
            }

            return redirect()->route('superadmin.index')->with('success', 'Berhasil masuk ke halaman admin.');
        }

        return redirect()->back()->withInput($request->only('email'))->with('error', 'Email atau kata sandi yang Anda masukkan salah.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login')->with('success', 'Berhasil keluar.');
    }
}
