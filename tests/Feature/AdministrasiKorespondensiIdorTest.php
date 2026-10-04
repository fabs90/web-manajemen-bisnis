<?php

use App\Models\AgendaJanjiTemu;
use App\Models\AgendaSuratKeluar;
use App\Models\KasKecil;
use App\Models\Rapat\AgendaRapat;
use App\Models\SuratUndanganRapat;
use App\Models\User;
use App\Services\AgendaJanjiTemuService;
use App\Services\ManajemenRapatService;
use App\Services\SuratUndanganRapatService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->userA = User::factory()->create([
        'email' => 'userA_admin@test.com',
        'alamat' => 'Jl. Merdeka No. 1',
        'nomor_telepon' => '081234567890',
        'is_verified' => 1,
    ]);

    $this->userB = User::factory()->create([
        'email' => 'userB_admin@test.com',
        'alamat' => 'Jl. Sudirman No. 2',
        'nomor_telepon' => '089876543210',
        'is_verified' => 1,
    ]);
});

test('tenant cannot edit or download pdf of another tenants surat undangan rapat', function () {
    $suratB = SuratUndanganRapat::create([
        'user_id' => $this->userB->id,
        'nomor_surat' => '001/UND/2026',
        'perihal' => 'Rapat Koordinasi B',
        'nama_penerima' => 'Direktur B',
        'email_penerima' => 'direktur@b.com',
        'judul_rapat' => 'Rapat Rahasia B',
        'tanggal_rapat' => now()->format('Y-m-d'),
    ]);

    // User A cannot view edit page of User B's surat
    $responseEdit = $this->actingAs($this->userA)->get(route('administrasi.surat-undangan-rapat.edit', $suratB->id));
    $responseEdit->assertNotFound();

    // User A cannot download PDF of User B's surat via service
    $this->actingAs($this->userA);
    $service = app(SuratUndanganRapatService::class);
    expect(fn () => $service->generatePdf($suratB->id))
        ->toThrow(ModelNotFoundException::class);

    // User A cannot update User B's surat (returns false and does not change data)
    $updateResult = $service->update($suratB->id, ['nomor_surat' => 'HACKED']);
    expect($updateResult)->toBeFalse();
    expect($suratB->fresh()->nomor_surat)->toBe('001/UND/2026');
});

test('tenant cannot edit, delete, or download pdf of another tenants notulen rapat', function () {
    $rapatB = AgendaRapat::create([
        'user_id' => $this->userB->id,
        'nomor_surat' => '002/NOT/2026',
        'judul_rapat' => 'Notulen Rapat Rahasia Tenant B',
        'tempat' => 'Ruang Rapat Utama B',
        'tanggal' => now()->format('Y-m-d'),
        'waktu' => '09:00:00',
        'pemimpin_rapat' => 'CEO B',
        'keputusan_rapat' => 'Keputusan Finansial B',
        'nama_kota' => 'Makassar',
        'nama_notulis' => 'Notulis B',
        'agenda_rapat' => 'Pembahasan Strategis B',
    ]);

    // User A cannot view edit page of User B's rapat
    $responseEdit = $this->actingAs($this->userA)->get(route('administrasi.rapat.edit', $rapatB->id));
    $responseEdit->assertNotFound();

    // User A cannot download PDF of User B's rapat
    $responsePdf = $this->actingAs($this->userA)->get(route('administrasi.rapat.generatePdf', $rapatB->id));
    $responsePdf->assertSessionHas('error');

    // User A cannot delete User B's rapat
    $service = app(ManajemenRapatService::class);
    expect(fn () => $service->destroy($rapatB->id))
        ->toThrow(ModelNotFoundException::class);
});

test('tenant cannot download pdf of another tenants janji temu', function () {
    $janjiTemuB = AgendaJanjiTemu::create([
        'user_id' => $this->userB->id,
        'tgl_membuat' => now()->format('Y-m-d'),
        'nama_pembuat' => 'Pembuat B',
        'jabatan_title' => 'Manajer B',
        'perusahaan' => 'PT B',
        'nomor_telpon' => '081234567899',
        'tgl_janji' => now()->format('Y-m-d'),
        'waktu' => '10:00:00',
        'bertemu_dengan' => 'Klien Rahasia B',
        'tempat_pertemuan' => 'Kantor B',
        'keperluan' => 'Investasi Rahasia',
        'status' => 'terkonfirmasi',
        'dicatat_oleh' => 'Sekretaris B',
        'dicatat_tgl' => now()->format('Y-m-d'),
    ]);

    $this->actingAs($this->userA);
    $service = app(AgendaJanjiTemuService::class);
    expect(fn () => $service->generatePdf($janjiTemuB->id))
        ->toThrow(ModelNotFoundException::class);
});

test('tenant cannot download pdf of another tenants kas kecil', function () {
    $kasB = KasKecil::create([
        'user_id' => $this->userB->id,
        'tanggal' => now()->format('Y-m-d'),
        'nomor_referensi' => 'KK-B-001',
        'penerimaan' => 1000000,
        'pengeluaran' => 0,
        'saldo_akhir' => 1000000,
    ]);

    $response = $this->actingAs($this->userA)->get(route('administrasi.kas-kecil.generatePdf', $kasB->id));
    $response->assertNotFound();
});

test('tenant can access own surat keluar signature and lampiran via proxy controller, but other tenant gets 404', function () {
    Storage::fake('local');

    $ttdPath = 'private/surat-keluar/ttd/'.md5($this->userB->email).'/signature-test.png';
    $lampiranPath = 'private/surat-keluar/lampiran/'.md5($this->userB->email).'/lampiran-test.pdf';

    Storage::disk('local')->put($ttdPath, 'fake-signature-content');
    Storage::disk('local')->put($lampiranPath, 'fake-lampiran-content');

    $suratB = AgendaSuratKeluar::create([
        'user_id' => $this->userB->id,
        'nomor_surat' => '003/SK/2026',
        'perihal' => 'Pemberitahuan B',
        'tanggal_surat' => now()->format('Y-m-d'),
        'nama_penerima' => 'Penerima B',
        'alamat_penerima' => 'Jl. Kenari No. 5',
        'email_penerima' => 'penerima@b.com',
        'paragraf_pembuka' => 'Pembuka B',
        'paragraf_isi' => 'Isi Surat Penting B',
        'paragraf_penutup' => 'Penutup B',
        'nama_pengirim' => 'Pengirim B',
        'jabatan_pengirim' => 'Direktur',
        'ttd' => $ttdPath,
        'file_lampiran' => $lampiranPath,
    ]);

    // User A (unauthorized tenant) gets 404 when trying to access User B's signature or attachment
    $this->actingAs($this->userA)
        ->get(route('administrasi.surat-keluar.view-signature', $suratB->id))
        ->assertNotFound();

    $this->actingAs($this->userA)
        ->get(route('administrasi.surat-keluar.view-lampiran', $suratB->id))
        ->assertNotFound();

    // User B (the owner) can successfully access both
    $this->actingAs($this->userB)
        ->get(route('administrasi.surat-keluar.view-signature', $suratB->id))
        ->assertSuccessful();

    $this->actingAs($this->userB)
        ->get(route('administrasi.surat-keluar.view-lampiran', $suratB->id))
        ->assertSuccessful();
});
