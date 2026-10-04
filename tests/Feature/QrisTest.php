<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
});

test('user can view qris index page', function () {
    $user = User::factory()->create([
        'is_verified' => 1,
    ]);

    $response = $this->actingAs($user)->get(route('qris.index'));

    $response->assertSuccessful();
    $response->assertSee('Manajemen QRIS');
    $response->assertSee('Anda belum mengunggah QRIS barcode.');
});

test('user can upload qris image and view it through qris.image route', function () {
    $user = User::factory()->create([
        'email' => 'qristest@example.com',
        'is_verified' => 1,
    ]);

    $file = UploadedFile::fake()->image('qris-sample.png', 300, 300);

    $responseUpload = $this->actingAs($user)->post(route('qris.update'), [
        'qris_image' => $file,
    ]);

    $responseUpload->assertRedirect();
    $responseUpload->assertSessionHas('success');

    $user->refresh();
    expect($user->qris_image)->not->toBeNull();

    // View index page should now display the QRIS image
    $responseIndex = $this->actingAs($user)->get(route('qris.index'));
    $responseIndex->assertSuccessful();
    $responseIndex->assertSee(route('qris.image'));

    // Image route should serve the binary image file
    $responseImage = $this->actingAs($user)->get(route('qris.image'));
    $responseImage->assertSuccessful();
    expect($responseImage->headers->get('Content-Type'))->toContain('image');
    expect($responseImage->headers->get('Cache-Control'))->toContain('private');
});

test('qris.image route returns 404 when user has no qris image', function () {
    $user = User::factory()->create([
        'is_verified' => 1,
        'qris_image' => null,
    ]);

    $response = $this->actingAs($user)->get(route('qris.image'));
    $response->assertNotFound();
});

test('tenant data isolation: user only accesses own qris image', function () {
    $userA = User::factory()->create([
        'email' => 'userA@test.com',
        'is_verified' => 1,
    ]);
    $userB = User::factory()->create([
        'email' => 'userB@test.com',
        'is_verified' => 1,
    ]);

    $fileA = UploadedFile::fake()->image('qrisA.png', 100, 100);
    $this->actingAs($userA)->post(route('qris.update'), ['qris_image' => $fileA]);

    $userA->refresh();
    $userB->refresh();

    // User A can access their image
    $responseA = $this->actingAs($userA)->get(route('qris.image'));
    $responseA->assertSuccessful();

    // User B has no QRIS and cannot access A's image via qris.image
    $responseB = $this->actingAs($userB)->get(route('qris.image'));
    $responseB->assertNotFound();
});

test('user can delete their qris image', function () {
    $user = User::factory()->create([
        'email' => 'qrisdelete@example.com',
        'is_verified' => 1,
    ]);

    $file = UploadedFile::fake()->image('qris-delete.png', 200, 200);
    $this->actingAs($user)->post(route('qris.update'), ['qris_image' => $file]);

    $user->refresh();
    $uploadedPath = $user->qris_image;
    expect($uploadedPath)->not->toBeNull();

    $responseDelete = $this->actingAs($user)->delete(route('qris.destroy'));
    $responseDelete->assertRedirect();
    $responseDelete->assertSessionHas('success');

    $user->refresh();
    expect($user->qris_image)->toBeNull();

    $responseImage = $this->actingAs($user)->get(route('qris.image'));
    $responseImage->assertNotFound();
});
