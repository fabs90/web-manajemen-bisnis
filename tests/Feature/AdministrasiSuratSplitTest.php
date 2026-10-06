<?php

use App\Models\AgendaJanjiTemu;
use App\Models\AgendaPerjalanan;
use App\Models\AgendaTelpon;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'admin_surat@test.com',
        'alamat' => 'Jl. Administrasi No. 1',
        'nomor_telepon' => '081234567890',
        'is_verified' => 1,
    ]);
});

test('administrasi surat index hub renders correctly', function () {
    $response = $this->actingAs($this->user)->get(route('administrasi.surat.index'));

    $response->assertSuccessful();
    $response->assertSee(route('administrasi.agenda-telpon.index'));
    $response->assertSee(route('administrasi.agenda-perjalanan.index'));
    $response->assertSee(route('administrasi.janji-temu.index'));
});

test('agenda telpon controller handles index, store, update, updateIsDone, and destroy', function () {
    // 1. Index
    $response = $this->actingAs($this->user)->get(route('administrasi.agenda-telpon.index'));
    $response->assertSuccessful();

    // 2. Store validation
    $responseErr = $this->actingAs($this->user)->post(route('administrasi.agenda-telpon.store'), []);
    $responseErr->assertSessionHasErrors(['tgl_panggilan', 'nama_penelpon', 'keperluan']);

    // 3. Store success
    $storeResponse = $this->actingAs($this->user)->post(route('administrasi.agenda-telpon.store'), [
        'tgl_panggilan' => now()->format('Y-m-d'),
        'waktu_panggilan' => '10:00',
        'nama_penelpon' => 'Budi Client',
        'perusahaan' => 'PT Maju',
        'nomor_telpon' => '08123456789',
        'keperluan' => 'Diskusi Proyek',
        'tingkat_status' => 'penting',
        'status' => 'terkonfirmasi',
        'dicatat_oleh' => 'Sekretaris',
        'dicatat_tgl' => now()->format('Y-m-d'),
    ]);
    $storeResponse->assertRedirect();
    $this->assertDatabaseHas('agenda_telpon', [
        'user_id' => $this->user->id,
        'nama_penelpon' => 'Budi Client',
        'is_done' => 0,
    ]);

    $agenda = AgendaTelpon::where('user_id', $this->user->id)->first();

    // 4. Update is_done
    $doneResponse = $this->actingAs($this->user)->patch(route('administrasi.agenda-telpon.update-done', $agenda->id));
    $doneResponse->assertRedirect();
    expect($agenda->fresh()->is_done)->toBe(1);

    // 5. Update data
    $updateResponse = $this->actingAs($this->user)->patch(route('administrasi.agenda-telpon.update', $agenda->id), [
        'tgl_panggilan' => $agenda->tgl_panggilan,
        'waktu_panggilan' => '11:00',
        'nama_penelpon' => 'Budi Updated',
        'perusahaan' => 'PT Maju Bersama',
        'nomor_telpon' => '08123456789',
        'keperluan' => 'Diskusi Selesai',
        'tingkat_status' => 'normal',
        'status' => 'selesai',
        'dicatat_oleh' => 'Sekretaris',
        'dicatat_tgl' => $agenda->dicatat_tgl,
    ]);
    $updateResponse->assertRedirect(route('administrasi.agenda-telpon.index'));
    expect($agenda->fresh()->nama_penelpon)->toBe('Budi Updated');

    // 6. Destroy
    $deleteResponse = $this->actingAs($this->user)->delete(route('administrasi.agenda-telpon.destroy', $agenda->id));
    $deleteResponse->assertRedirect();
    $this->assertDatabaseMissing('agenda_telpon', ['id' => $agenda->id]);
});

test('agenda perjalanan controller handles index, store, and destroy', function () {
    // 1. Index
    $response = $this->actingAs($this->user)->get(route('administrasi.agenda-perjalanan.index'));
    $response->assertSuccessful();

    // 2. Store
    $storeResponse = $this->actingAs($this->user)->post(route('administrasi.agenda-perjalanan.store'), [
        'nama_pelaksana' => 'Ahmad Pelaksana',
        'jabatan' => 'Staff Lapangan',
        'tujuan' => 'Jakarta',
        'tanggal_mulai' => now()->format('Y-m-d'),
        'tanggal_selesai' => now()->addDays(2)->format('Y-m-d'),
        'keperluan' => 'Audit Keuangan',
        'disiapkan_oleh' => 'Sekretaris',
        'tanggal_disiapkan' => now()->format('Y-m-d'),
        'disetujui_oleh' => 'Direktur',
        'tanggal_disetujui' => now()->format('Y-m-d'),
        'transport' => '500000',
        'akomodasi' => '300000',
        'konsumsi' => '200000',
        'lain_lain' => '0',
        'total_biaya' => '1000000',
        'transportasi_pergi' => 'Pesawat GA-101',
        'transportasi_pulang' => 'Pesawat GA-102',
        'kode_booking' => 'BK12345',
        'transportasi_lokal' => 'Taxi',
        'akomodasi_hotel' => 'Hotel Grand',
        'akomodasi_alamat' => 'Jl. Thamrin',
        'akomodasi_telpon' => '021123456',
        'akomodasi_check_in' => now()->format('Y-m-d'),
        'akomodasi_check_out' => now()->addDays(2)->format('Y-m-d'),
        'akomodasi_booking_no' => 'HTL-999',
        'jadwal' => [
            [
                'tanggal' => now()->format('Y-m-d'),
                'items' => [
                    [
                        'waktu' => '09:00',
                        'kegiatan' => 'Berangkat',
                        'lokasi' => 'Bandara',
                    ],
                ],
            ],
        ],
        'kontak' => [
            [
                'nama' => 'Contact Person',
                'tel' => '0812345678',
            ],
        ],
    ]);
    $storeResponse->assertRedirect();

    $this->assertDatabaseHas('agenda_perjalanan', [
        'user_id' => $this->user->id,
        'nama_pelaksana' => 'Ahmad Pelaksana',
    ]);

    $agenda = AgendaPerjalanan::where('user_id', $this->user->id)->first();

    // 3. Destroy
    $deleteResponse = $this->actingAs($this->user)->delete(route('administrasi.agenda-perjalanan.destroy', $agenda->id));
    $deleteResponse->assertRedirect();
    $this->assertDatabaseMissing('agenda_perjalanan', ['id' => $agenda->id]);
});

test('agenda janji temu controller handles index, store, and destroy', function () {
    // 1. Index
    $response = $this->actingAs($this->user)->get(route('administrasi.janji-temu.index'));
    $response->assertSuccessful();

    // 2. Store
    $storeResponse = $this->actingAs($this->user)->post(route('administrasi.janji-temu.store'), [
        'tgl_membuat' => now()->format('Y-m-d'),
        'nama_pembuat' => 'Pembuat Test',
        'jabatan_title' => 'Manajer',
        'perusahaan' => 'PT Mitra',
        'nomor_telpon' => '0812345678',
        'tgl_janji' => now()->format('Y-m-d'),
        'waktu' => '09:00:00',
        'bertemu_dengan' => 'Klien Mitra',
        'tempat_pertemuan' => 'Ruang Meeting 1',
        'keperluan' => 'Kerjasama Bisnis',
        'status' => 'terkonfirmasi',
        'dicatat_oleh' => 'Staff',
        'dicatat_tgl' => now()->format('Y-m-d'),
    ]);
    $storeResponse->assertRedirect(route('administrasi.janji-temu.index'));

    $this->assertDatabaseHas('agenda_janji_temu', [
        'user_id' => $this->user->id,
        'nama_pembuat' => 'Pembuat Test',
    ]);

    $janji = AgendaJanjiTemu::where('user_id', $this->user->id)->first();

    // 3. Destroy
    $deleteResponse = $this->actingAs($this->user)->delete(route('administrasi.janji-temu.destroy', $janji->id));
    $deleteResponse->assertRedirect();
    $this->assertDatabaseMissing('agenda_janji_temu', ['id' => $janji->id]);
});
