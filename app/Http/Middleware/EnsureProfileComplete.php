<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (empty($user->alamat) || empty($user->nomor_telepon) || empty($user->logo_perusahaan)) {
            return redirect()->route('profile.edit')->with('error', 'Lengkapi profil terlebih dahulu sebelum mengakses fitur ini.');
        }
        return $next($request);
    }
}
