<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\MailSend;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view("auth.login");
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        switch ($user->role) {
            case "superadmin":
                return redirect()->intended(
                    route("superadmin.dashboard", absolute: false),
                );
            case "ukm":
            case "nelayan":
            case "koperasi":
                if ($user->is_verified) {
                    return redirect()->intended(
                        route("dashboard", absolute: false),
                    );
                } else {
                    // Auth::guard("web")->logout();
                    // $request->session()->invalidate();
                    // $request->session()->regenerateToken();
                    Mail::to($user->email)->send(
                        new MailSend($user->otp, $user->name, $user->email),
                    );

                    return redirect()
                        ->route("account-verification.show")
                        ->with("verification_email", $user->email)
                        ->with(
                            "status",
                            "Silakan masukkan kode OTP yang telah kami kirim ke email Anda.",
                        );
                }
            // return redirect()->intended(
            //     route("dashboard", absolute: false),
            // );
            default:
                return redirect()->intended(
                    route("dashboard", absolute: false),
                );
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard("web")->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect("/");
    }
}
