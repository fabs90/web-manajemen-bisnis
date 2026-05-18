<?php

namespace App\Http\Controllers;

use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QrisController extends Controller
{
    public function __construct(protected FileUploadService $fileUploadService) {}

    public function index()
    {
        $user = Auth::user();

        return view('qris.index', compact('user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'qris_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('qris_image')) {
            // Hapus file lama jika ada
            if ($user->qris_image) {
                $this->fileUploadService->delete($user->qris_image);
            }

            // Upload file baru
            $path = $this->fileUploadService->upload($request->file('qris_image'), 'qris', $user->email);
            $user->qris_image = $path;
            $user->save();

            return redirect()->back()->with('success', 'QRIS barcode berhasil diperbarui.');
        }

        return redirect()->back()->with('info', 'Tidak ada file yang diunggah.');
    }

    public function destroy()
    {
        $user = Auth::user();

        if ($user->qris_image) {
            $this->fileUploadService->delete($user->qris_image);
            $user->qris_image = null;
            $user->save();

            return redirect()->back()->with('success', 'QRIS barcode berhasil dihapus.');
        }

        return redirect()->back()->with('info', 'Tidak ada QRIS barcode yang terunggah.');
    }
}
