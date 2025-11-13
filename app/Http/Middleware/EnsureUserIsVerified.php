<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika belum login
        if (!Auth::check()) {
            return redirect()
                ->route("login")
                ->with("errors", "Silakan login terlebih dahulu");
        }

        // Abaikan pengecekan jika sedang di halaman verifikasi
        if ($request->routeIs("account-verification.*")) {
            return $next($request);
        }

        // Jika belum diverifikasi
        if (!Auth::user()->is_verified) {
            return redirect()
                ->route("account-verification.show")
                ->with("status", "Silakan verifikasi akun Anda");
        }

        return $next($request);
    }
}
