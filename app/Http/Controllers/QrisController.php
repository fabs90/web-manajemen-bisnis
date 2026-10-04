<?php

namespace App\Http\Controllers;

use App\Services\FileUploadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QrisController extends Controller
{
    public function __construct(protected FileUploadService $fileUploadService) {}

    public function index(): View
    {
        $user = Auth::user();

        return view('qris.index', compact('user'));
    }

    public function showImage(): BinaryFileResponse
    {
        $user = Auth::user();

        abort_unless($user && $user->qris_image, 404);

        $filePath = storage_resolve_path($user->qris_image);

        abort_unless($filePath && file_exists($filePath), 404);

        return response()->file($filePath, [
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'qris_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('qris_image')) {
            if ($user->qris_image) {
                $this->fileUploadService->delete($user->qris_image);
            }

            $path = $this->fileUploadService->upload($request->file('qris_image'), 'qris', $user->email);
            $user->qris_image = $path;
            $user->save();

            return redirect()->back()->with('success', 'QRIS barcode berhasil diperbarui.');
        }

        return redirect()->back()->with('info', 'Tidak ada file yang diunggah.');
    }

    public function destroy(): RedirectResponse
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
