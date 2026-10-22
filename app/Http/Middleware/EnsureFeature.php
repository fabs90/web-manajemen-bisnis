<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (!$request->user()->hasFeature($feature)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke fitur ini.');
        }

        return $next($request);
    }
}