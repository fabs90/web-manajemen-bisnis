<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    public function upload(UploadedFile $file, $folder = 'uploads', $email = 'user@email.com')
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slugName = Str::slug($originalName);
        $fileName = $slugName . '-' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder . '/' . $email, $fileName, 'public');
        return $path;
    }

    public function delete(?string $path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

    }
}