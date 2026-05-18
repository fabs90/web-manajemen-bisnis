<?php

use App\Jobs\SendSuratKeluarJob;
use App\Mail\SuratKeluarMail;
use App\Models\AgendaSuratKeluar;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Queue::fake();
    Mail::fake();
});

it('can store surat keluar and dispatch SendSuratKeluarJob with email_penerima', function () {
    $this->withoutMiddleware();
    $user = User::factory()->create([
        'is_verified' => true,
        'alamat' => 'Jl. Test No. 1',
        'nomor_telepon' => '08123456789',
    ]);

    $data = [
        'nomor_surat' => '001/SK/2026',
        'lampiran_text' => '1 Berkas',
        'perihal' => 'Test Surat Keluar',
        'tanggal_surat' => '2026-05-14',
        'nama_penerima' => 'John Doe',
        'jabatan_penerima' => 'Manager',
        'alamat_penerima' => 'Jl. Test No. 123',
        'email_penerima' => 'recipient@example.com',
        'paragraf_pembuka' => 'Pembuka',
        'paragraf_isi' => 'Isi Surat',
        'paragraf_penutup' => 'Penutup',
        'nama_pengirim' => 'Jane Smith',
        'jabatan_pengirim' => 'Director',
        'tembusan' => 'Tembusan 1',
    ];

    $response = $this->actingAs($user)
        ->post(route('administrasi.surat-keluar.store'), $data);

    $response->assertRedirect(route('administrasi.surat-keluar.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('agenda_surat_keluar', [
        'nomor_surat' => '001/SK/2026',
        'email_penerima' => 'recipient@example.com',
    ]);

    $surat = AgendaSuratKeluar::where('nomor_surat', '001/SK/2026')->first();

    Queue::assertPushed(SendSuratKeluarJob::class, function ($job) use ($surat) {
        return $job->surat->id === $surat->id && $job->surat->email_penerima === 'recipient@example.com';
    });
});

it('skips sending email if email_penerima is empty in SendSuratKeluarJob', function () {
    $user = User::factory()->create([
        'is_verified' => true,
        'alamat' => 'Jl. Test No. 1',
        'nomor_telepon' => '08123456789',
    ]);
    $surat = AgendaSuratKeluar::create([
        'user_id' => $user->id,
        'nomor_surat' => '002/SK/2026',
        'perihal' => 'Test No Email',
        'tanggal_surat' => '2026-05-14',
        'nama_penerima' => 'John Doe',
        'alamat_penerima' => 'Jl. Test',
        'paragraf_pembuka' => 'Pembuka',
        'paragraf_isi' => 'Isi',
        'paragraf_penutup' => 'Penutup',
        'nama_pengirim' => 'Jane',
        'jabatan_pengirim' => 'Director',
        // email_penerima is NULL by default if not set (due to migration change)
    ]);

    $job = new SendSuratKeluarJob($surat, $user);
    $job->handle();

    Mail::assertNotSent(SuratKeluarMail::class);
});
