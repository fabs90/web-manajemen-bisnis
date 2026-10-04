<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    public function upload(UploadedFile $file, $folder = 'uploads', $email = 'user@email.com')
    {
        $extension = $file->extension();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slugName = Str::slug($originalName);
        $safeName = $slugName.'-'.Str::uuid().'.'.$extension;

        return $file->storeAs("{$folder}/{$email}", $safeName, 'local');
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function resolveDisk(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Storage::disk('local')->exists($path)) {
            return 'local';
        }

        if (Storage::disk('public')->exists($path)) {
            return 'public';
        }

        return null;
    }

    public function resolvePath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $disk = $this->resolveDisk($path);
        if ($disk) {
            return Storage::disk($disk)->path($path);
        }

        $storagePath = public_path('storage/'.$path);
        if (file_exists($storagePath) && is_file($storagePath)) {
            return $storagePath;
        }

        $appPublicPath = storage_path('app/public/'.$path);
        if (file_exists($appPublicPath) && is_file($appPublicPath)) {
            return $appPublicPath;
        }

        $appLocalLegacyPath = storage_path('app/'.$path);
        if (file_exists($appLocalLegacyPath) && is_file($appLocalLegacyPath)) {
            return $appLocalLegacyPath;
        }

        $directPublicPath = public_path($path);
        if (file_exists($directPublicPath) && is_file($directPublicPath)) {
            return $directPublicPath;
        }

        return null;
    }

    public function toBase64(?string $path): ?string
    {
        $resolvedPath = $this->resolvePath($path);
        if (! $resolvedPath || ! file_exists($resolvedPath) || ! is_file($resolvedPath)) {
            return null;
        }

        $mime = mime_content_type($resolvedPath) ?: 'image/png';
        $base64 = base64_encode(file_get_contents($resolvedPath));

        return "data:{$mime};base64,{$base64}";
    }
}
