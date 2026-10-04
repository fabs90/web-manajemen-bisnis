<?php

use App\Models\AgendaSuratKeluar;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('check download and view lampiran headers and status', function () {
    $user = User::factory()->create([
        'email' => 'user_test_headers@test.com',
        'nomor_telepon' => '081234567890',
        'alamat' => 'Jl. Mawar No 1',
        'is_verified' => 1,
    ]);

    $lampiranPath = 'private/surat-keluar/lampiran/test/lampiran.pdf';
    Storage::disk('local')->put($lampiranPath, '%PDF-1.4 test content');

    $surat = AgendaSuratKeluar::create([
        'user_id' => $user->id,
        'nomor_surat' => 'SK/2026/001',
        'perihal' => 'Test Perihal',
        'tanggal_surat' => '2026-10-03',
        'nama_penerima' => 'Test Penerima',
        'email_penerima' => 'penerima@test.com',
        'alamat_penerima' => 'Test Alamat',
        'jabatan_penerima' => 'Manager',
        'paragraf_pembuka' => 'Pembuka',
        'paragraf_isi' => 'Isi',
        'paragraf_penutup' => 'Penutup',
        'nama_pengirim' => 'Pengirim',
        'jabatan_pengirim' => 'Direktur',
        'file_lampiran' => $lampiranPath,
    ]);

    // 1. Inline stream PDF
    $responsePdf = $this->actingAs($user)->get(route('administrasi.surat-keluar.downloadPdf', $surat->id));
    $responsePdf->assertSuccessful();
    $responsePdf->assertHeader('Content-Type', 'application/pdf');
    expect($responsePdf->headers->get('Content-Disposition'))->toContain('inline');
    expect($responsePdf->headers->get('Cache-Control'))->toContain('private');
    expect($responsePdf->headers->get('Cache-Control'))->not->toContain('no-store');
    expect($responsePdf->headers->has('Pragma'))->toBeFalse();

    // 2. Inline view Lampiran (PDF)
    $responseLampiran = $this->actingAs($user)->get(route('administrasi.surat-keluar.view-lampiran', $surat->id));
    $responseLampiran->assertSuccessful();
    $responseLampiran->assertHeader('Content-Type', 'application/pdf');
    expect($responseLampiran->headers->get('Content-Disposition'))->toContain('inline');
    expect($responseLampiran->headers->get('Cache-Control'))->toContain('private');
    expect($responseLampiran->headers->get('Cache-Control'))->not->toContain('no-store');
    expect($responseLampiran->headers->has('Pragma'))->toBeFalse();

    // 3. Non-inline attachment (e.g. DOCX) automatically downloads
    $docxPath = 'private/surat-keluar/lampiran/test/document.docx';
    Storage::disk('local')->put($docxPath, 'fake-docx-content');
    $suratDocx = AgendaSuratKeluar::create([
        'user_id' => $user->id,
        'nomor_surat' => 'SK/2026/DOCX',
        'perihal' => 'Docx Test',
        'tanggal_surat' => '2026-10-03',
        'nama_penerima' => 'Receiver',
        'email_penerima' => 'penerima2@test.com',
        'alamat_penerima' => 'Address',
        'jabatan_penerima' => 'Staff',
        'paragraf_pembuka' => 'Pembuka',
        'paragraf_isi' => 'Isi',
        'paragraf_penutup' => 'Penutup',
        'nama_pengirim' => 'Pengirim',
        'jabatan_pengirim' => 'Direktur',
        'file_lampiran' => $docxPath,
    ]);
    $responseDocx = $this->actingAs($user)->get(route('administrasi.surat-keluar.view-lampiran', $suratDocx->id));
    $responseDocx->assertSuccessful();
    expect($responseDocx->headers->get('Content-Disposition'))->toContain('attachment');
});
