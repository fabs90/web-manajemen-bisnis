<?php

use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('storage_resolve_path resolves files from both local and public disks', function () {
    $localFile = 'uploads/test-local.txt';
    $publicFile = 'uploads/test-public.txt';

    Storage::disk('local')->put($localFile, 'local content');
    Storage::disk('public')->put($publicFile, 'public content');

    expect(storage_resolve_path($localFile))->toBe(Storage::disk('local')->path($localFile));
    expect(storage_resolve_path($publicFile))->toBe(Storage::disk('public')->path($publicFile));
    expect(storage_resolve_path('non-existent-file.txt'))->toBeNull();
});

test('image_to_base64 converts images from local or public disks to data URI', function () {
    // 1x1 transparent PNG in base64
    $pngContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

    $localImg = 'logo/local_logo.png';
    $publicImg = 'logo/public_logo.png';

    Storage::disk('local')->put($localImg, $pngContent);
    Storage::disk('public')->put($publicImg, $pngContent);

    $base64Local = image_to_base64($localImg);
    $base64Public = image_to_base64($publicImg);

    expect($base64Local)->toBeString();
    expect($base64Local)->toStartWith('data:image/png;base64,');

    expect($base64Public)->toBeString();
    expect($base64Public)->toStartWith('data:image/png;base64,');

    expect(image_to_base64('non-existent-image.png'))->toBeNull();
});

test('laporan rugi laba pdf can be generated with logo from public and local disk', function () {
    $pngContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

    // 1. With logo on public disk
    $publicLogo = 'logo/company_public.png';
    Storage::disk('public')->put($publicLogo, $pngContent);

    $userWithPublicLogo = User::factory()->create([
        'email' => 'user_rugilaba_public@test.com',
        'is_verified' => 1,
        'alamat' => 'Jl. Sudirman No 1',
        'nomor_telepon' => '08123456789',
        'logo_perusahaan' => $publicLogo,
    ]);

    $responsePublic = $this->actingAs($userWithPublicLogo)->get(route('laporan-keuangan.rugi-laba.pdf'));
    $responsePublic->assertSuccessful();
    $responsePublic->assertHeader('Content-Type', 'application/pdf');

    // 2. With logo on local disk
    $localLogo = 'logo/company_local.png';
    Storage::disk('local')->put($localLogo, $pngContent);

    $userWithLocalLogo = User::factory()->create([
        'email' => 'user_rugilaba_local@test.com',
        'is_verified' => 1,
        'alamat' => 'Jl. Thamrin No 2',
        'nomor_telepon' => '08987654321',
        'logo_perusahaan' => $localLogo,
    ]);

    $responseLocal = $this->actingAs($userWithLocalLogo)->get(route('laporan-keuangan.rugi-laba.pdf'));
    $responseLocal->assertSuccessful();
    $responseLocal->assertHeader('Content-Type', 'application/pdf');
});
