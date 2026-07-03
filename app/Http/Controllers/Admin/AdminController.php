<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::all();
        $totalUsers = User::where('role', '!=', 'superadmin')->count();
        $verifiedUsers = User::where('role', '!=', 'superadmin')->where('is_verified', true)->count();
        $unverifiedUsers = User::where('role', '!=', 'superadmin')->where('is_verified', false)->count();

        return view('superadmin.dashboard.superadmin', compact('users', 'totalUsers', 'verifiedUsers', 'unverifiedUsers'));
    }

    public function logout(\Illuminate\Http\Request $request)
    {
        auth()->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login')->with('success', 'Berhasil logout');
    }
}
