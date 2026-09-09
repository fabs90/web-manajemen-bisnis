<?php

namespace App\Http\Controllers;

use App\Mail\MailSend;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UserVerificationController extends Controller
{
    public function show()
    {
        if (! Auth::check()) {
            return redirect()
                ->route('login')
                ->with('errors', 'Silakan login terlebih dahulu');
        }
        $user = Auth::user();

        return view('auth.verify-account', compact('user'));
    }

    public function verify(Request $request)
    {
        $user = $request->user();
        $otp = $request->input('otp');
        $otpKey = is_array($otp) ? implode('', $otp) : (string) $otp;

        if ($user->otp && $user->otp === $otpKey) {
            if ($user->otp_expires_at && Carbon::now('Asia/Makassar')->gt($user->otp_expires_at)) {
                return back()->withErrors(['otp' => 'Kode OTP telah kedaluwarsa. Silakan klik "Kirim Ulang OTP".']);
            }

            $user->is_verified = true;
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            return redirect()
                ->route('dashboard.getStarted')
                ->with('success', 'Akun Anda berhasil diverifikasi!');
        }

        return back()->withErrors(['otp' => 'Kode OTP yang Anda masukkan salah atau telah kedaluwarsa. Silakan periksa kembali email Anda.']);
    }

    public function resetEmailView()
    {
        return view('auth.reset-email');
    }

    public function resetEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,'.$request->user()->id,
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar. Silakan gunakan email lain.',
        ]);

        $user = $request->user();
        $newEmail = $request->input('email');
        $user->email = $newEmail;
        $user->save();

        return $this->generateAndSendOtp($user);
    }

    public function regenerateOtp(Request $request)
    {
        return $this->generateAndSendOtp($request->user());
    }

    public function generateAndSendOtp(User $user)
    {
        $otp = random_int(100000, 999999);
        $expiresAt = Carbon::now('Asia/Makassar')->addMinutes(30);
        $user->otp = $otp;
        $user->otp_expires_at = $expiresAt;
        $user->save();
        Mail::to($user->email)->send(
            new MailSend($otp, $user->name, $user->email),
        );

        return redirect()
            ->route('account-verification.show')
            ->with('status', 'Kode OTP baru telah berhasil dikirim ke email Anda.');
    }
}
