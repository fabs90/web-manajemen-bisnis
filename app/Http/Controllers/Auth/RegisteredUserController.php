<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\MailSend;
use App\Models\User;
use Database\Seeders\DefaultAccountSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
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
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:'.User::class,
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:ukm,nelayan,koperasi,superadmin'],
        ], [
            'name.required' => 'Nama UMKM/perusahaan wajib diisi.',
            'name.max' => 'Nama UMKM/perusahaan maksimal 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau masuk ke akun Anda.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'role.required' => 'Silakan pilih jenis akun.',
            'role.in' => 'Jenis akun yang dipilih tidak valid.',
        ]);
        $otp = random_int(100000, 999999);
        $expiresAt = Carbon::now('Asia/Makassar')->addMinutes(30);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'ukm',
            'is_verified' => false,
            'remember_token' => Str::random(60),
            'otp' => $otp,
            'otp_expires_at' => $expiresAt,
            'alamat' => null,
            'nomor_telepon' => null,
            'logo_perusahaan' => null,
        ]);

        DefaultAccountSeeder::seedForUser($user->id);

        event(new Registered($user));

        Mail::to($user->email)->send(
            new MailSend($otp, $user->name, $user->email),
        );

        Auth::login($user);

        return redirect(route('account-verification.show', absolute: false));
    }
}
