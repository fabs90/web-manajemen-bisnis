<?php

namespace App\Http\Controllers;

use App\Mail\MailSend;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UserVerificationController extends Controller
{
    public function show()
    {
        if (!Auth::check()) {
            return redirect()
                ->route("login")
                ->with("errors", "Silakan login terlebih dahulu");
        }
        return view("auth.verify-account");
    }

    public function verify(Request $request)
    {
        $user = $request->user();
        $otp = $request->input("otp");
        $otpKey = implode($otp);
        if ($user->otp === $otpKey) {
            $user->is_verified = true;
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();
            return redirect()
                ->route("dashboard.getStarted")
                ->with("success", "Akun Anda berhasil diverifikasi!");
        }
        return response()->json(["message" => "Invalid OTP"], 400);
    }

    public function regenerateOtp(Request $request)
    {
        $user = $request->user();
        $otp = random_int(100000, 999999);
        $expiresAt = Carbon::now("Asia/Makassar")->addMinutes(30);
        $user->otp = $otp;
        $user->otp_expires_at = $expiresAt;
        $user->save();
        Mail::to($user->email)->send(
            new MailSend($otp, $user->name, $user->email),
        );
        return redirect()
            ->route("account-verification.show")
            ->with("status", "Berhasil mengirim ulang OTP");
    }
}
