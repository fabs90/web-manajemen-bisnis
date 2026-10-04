<?php

use App\Services\FileUploadService;

if (! function_exists('format_rupiah')) {
    function format_rupiah($angka, $with_prefix = true)
    {
        if ($angka === null) {
            $angka = 0;
        }

        $hasil = number_format($angka, 0, ',', '.');

        return $with_prefix ? 'Rp '.$hasil : $hasil;
    }
}

if (! function_exists('storage_resolve_path')) {
    function storage_resolve_path(?string $path): ?string
    {
        return app(FileUploadService::class)->resolvePath($path);
    }
}

if (! function_exists('image_to_base64')) {
    function image_to_base64(?string $path): ?string
    {
        return app(FileUploadService::class)->toBase64($path);
    }
}
