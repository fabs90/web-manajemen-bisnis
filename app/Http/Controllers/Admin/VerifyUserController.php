<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyUserController extends Controller
{
    public function index()
    {
        $users = User::where('role', '!=', 'superadmin')->get();
        return view('superadmin.dashboard.verify-account.index', compact('users'));
    }

    public function verify(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'is_verified' => 1,
            'email_verified_at' => now(),
            'otp' => null,
            'otp_expires_at' => null,
            'updated_at' => now(),
        ]);

        Log::info("Verify User Success: ", [
            'submitted by' => $request->user()->name . '-' . $request->user()->email,
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
        ]);

        return redirect()->route('superadmin.user-management.index')->with('success', 'User berhasil diverifikasi.');
    }
}
