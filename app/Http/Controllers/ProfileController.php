<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view("profile.edit", [
            "user" => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        DB::beginTransaction();
        try {
            /** -------------------------------
             * 1. UPDATE FIELD BIASA
             * Hanya field yang diinput & lolos validasi
             ---------------------------------*/
            // Hapus password jika kosong supaya tidak meng-overwrite
            if (empty($data["password"])) {
                unset($data["password"]);
                unset($data["password_confirmation"]);
            }

            // Jika email berubah reset verifikasi
            if ($user->email !== $data["email"]) {
                $data["email_verified_at"] = 0;
            }

            /** -------------------------------
             * 2. HANDLE LOGO PERUSAHAAN (UPLOAD)
             ---------------------------------*/
            if ($request->hasFile("logo_perusahaan")) {
                // Hapus logo lama jika ada
                if (
                    $user->logo_perusahaan &&
                    Storage::disk("public")->exists($user->logo_perusahaan)
                ) {
                    Storage::disk("public")->delete($user->logo_perusahaan);
                }

                // Simpan logo baru
                $data["logo_perusahaan"] = Storage::disk("public")->putFile(
                    "profile",
                    $request->file("logo_perusahaan"),
                );
            }

            /** -------------------------------
             * 3. APPLY UPDATE KE USER
             ---------------------------------*/
            $user->update($data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Profile update error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat menyimpan data profile.",
            );
        }

        return Redirect::route("profile.edit")->with(
            "success",
            "Profile berhasil diperbarui!",
        );
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag("userDeletion", [
            "password" => ["required", "current_password"],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to("/");
    }
}
