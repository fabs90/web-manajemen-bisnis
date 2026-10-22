<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Mail\MailSend;
use App\Models\User;
use Database\Seeders\DefaultAccountSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $plainOtp = (string) random_int(100000, 999999);
        $hashedOtp = hash('sha256', $plainOtp);
        $expiresAt = Carbon::now('Asia/Makassar')->addMinutes(10);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'ukm',
            'is_verified' => false,
            'remember_token' => Str::random(60),
            'otp' => $hashedOtp,
            'otp_expires_at' => $expiresAt,
            'alamat' => null,
            'nomor_telepon' => null,
            'logo_perusahaan' => null,
        ]);

        DefaultAccountSeeder::seedForUser($user->id);

        event(new Registered($user));

        Mail::to($user->email)->send(
            new MailSend($plainOtp, $user->name, $user->email),
        );

        Auth::login($user);

        return redirect(route('account-verification.show', absolute: false));
    }
}
