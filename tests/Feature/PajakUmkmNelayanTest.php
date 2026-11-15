<?php

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\KeuanganService;
use Database\Seeders\DefaultAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createVerifiedUserWithRole(string $role): User
{
    $user = User::factory()->create([
        'role' => $role,
        'alamat' => 'Jl. Pengujian Pajak No. 1',
        'nomor_telepon' => '08123456789',
        'is_verified' => true,
    ]);

    DefaultAccountSeeder::seedForUser($user->id);

    return $user;
}

function recordRevenueJournal(User $user, float $amount, string $date, string $reference, string $transactionType = 'penjualan'): void
{
    $kasAccount = Account::where('user_id', $user->id)->where('code', '1101')->firstOrFail();
    $pendapatanAccount = Account::where('user_id', $user->id)->where('code', '4101')->firstOrFail();

    $entry = JournalEntry::create([
        'user_id' => $user->id,
        'reference_number' => $reference,
        'date' => $date,
        'description' => 'Transaksi Pendapatan '.$reference,
        'transaction_type' => $transactionType,
    ]);

    $entry->items()->create([
        'user_id' => $user->id,
        'account_id' => $kasAccount->id,
        'debit' => $amount,
        'credit' => 0,
    ]);

    $entry->items()->create([
        'user_id' => $user->id,
        'account_id' => $pendapatanAccount->id,
        'debit' => 0,
        'credit' => $amount,
    ]);
}

test('perhitungan pajak UMKM dan Nelayan sesuai skenario batas omzet Rp500.000.000', function (string $role, float $omzet, float $expectedRate, float $expectedTax) {
    $user = createVerifiedUserWithRole($role);
    recordRevenueJournal($user, $omzet, now()->format('Y-m-d'), 'INV-'.strtoupper($role).'-001');

    $keuangan = app(KeuanganService::class);
    $labaRugi = $keuangan->hitungLabaRugi(null, null, $user->id);
    $neraca = $keuangan->hitungNeraca(null, $user->id);

    expect($labaRugi['omzet'])->toBe($omzet)
        ->and($labaRugi['pajakRate'])->toBe($expectedRate)
        ->and($labaRugi['pajak'])->toBe($expectedTax)
        ->and($labaRugi['labaSetelahPajak'])->toBe($labaRugi['labaSebelumPajak'] - $expectedTax)
        ->and($neraca['pajak'])->toBe($expectedTax)
        ->and($neraca['totalPasiva'])->toBe($neraca['totalAktiva']);

    $this->actingAs($user);

    $dashboardResponse = $this->get(route('dashboard'));
    $dashboardResponse->assertOk();
    expect($dashboardResponse->viewData('labaBersih'))->toBe($labaRugi['labaSetelahPajak']);

    $rugiLabaResponse = $this->get(route('laporan-keuangan.rugi-laba'));
    $rugiLabaResponse->assertOk();
    expect($rugiLabaResponse->viewData('pajak'))->toBe($expectedTax)
        ->and($rugiLabaResponse->viewData('labaSetelahPajak'))->toBe($labaRugi['labaSetelahPajak']);

    if ($expectedTax > 0) {
        $rugiLabaResponse->assertSee('Pajak UKM dan Nelayan (0,5%)');
    } else {
        $rugiLabaResponse->assertSee('Pajak UKM dan Nelayan (0% - Bebas Pajak / PTKP)');
    }

    $neracaResponse = $this->get(route('laporan-keuangan.neraca-akhir'));
    $neracaResponse->assertOk();
    expect($neracaResponse->viewData('pajak'))->toBe($expectedTax);
})->with([
    'ukm omzet 100jt' => ['ukm', 100000000.0, 0.0, 0.0],
    'ukm omzet 300jt' => ['ukm', 300000000.0, 0.0, 0.0],
    'ukm omzet 500jt' => ['ukm', 500000000.0, 0.0, 0.0],
    'ukm omzet 500jt + 1' => ['ukm', 500000001.0, 0.005, 2500000.005],
    'ukm omzet 600jt' => ['ukm', 600000000.0, 0.005, 3000000.0],
    'nelayan omzet 100jt' => ['nelayan', 100000000.0, 0.0, 0.0],
    'nelayan omzet 300jt' => ['nelayan', 300000000.0, 0.0, 0.0],
    'nelayan omzet 500jt' => ['nelayan', 500000000.0, 0.0, 0.0],
    'nelayan omzet 500jt + 1' => ['nelayan', 500000001.0, 0.005, 2500000.005],
    'nelayan omzet 600jt' => ['nelayan', 600000000.0, 0.005, 3000000.0],
]);

test('akumulasi omzet dari beberapa transaksi dalam satu periode menentukan tarif pajak UMKM dan Nelayan', function (string $role) {
    $user = createVerifiedUserWithRole($role);
    $today = now()->format('Y-m-d');

    recordRevenueJournal($user, 300000000.0, $today, 'INV-ACC-001', 'penjualan');
    recordRevenueJournal($user, 300000000.0, $today, 'INV-ACC-002', 'pendapatan_lain');

    $keuangan = app(KeuanganService::class);
    $labaRugi = $keuangan->hitungLabaRugi(null, null, $user->id);

    expect($labaRugi['omzet'])->toBe(600000000.0)
        ->and($labaRugi['pajak'])->toBe(3000000.0)
        ->and($labaRugi['labaSetelahPajak'])->toBe(597000000.0);
})->with(['ukm', 'nelayan']);

test('neraca akhir menghitung pajak berdasarkan omzet tahun pajak berjalan tanpa mencampur tahun sebelumnya', function (string $role) {
    $user = createVerifiedUserWithRole($role);

    recordRevenueJournal($user, 300000000.0, now()->subYear()->format('Y-06-15'), 'INV-PREV-001');
    recordRevenueJournal($user, 300000000.0, now()->format('Y-06-15'), 'INV-CURR-001');

    $keuangan = app(KeuanganService::class);
    $neraca = $keuangan->hitungNeraca(now()->format('Y-m-d'), $user->id);

    expect($neraca['pajak'])->toBe(0.0)
        ->and($neraca['totalPasiva'])->toBe($neraca['totalAktiva']);
})->with(['ukm', 'nelayan']);

test('role koperasi tetap menggunakan tarif pajak 15 persen dari laba sebelum pajak', function () {
    $user = createVerifiedUserWithRole('koperasi');
    recordRevenueJournal($user, 100000000.0, now()->format('Y-m-d'), 'INV-KOP-001');

    $keuangan = app(KeuanganService::class);
    $labaRugi = $keuangan->hitungLabaRugi(null, null, $user->id);

    expect($labaRugi['pajakRate'])->toBe(0.15)
        ->and($labaRugi['pajak'])->toBe(15000000.0)
        ->and($labaRugi['labaSetelahPajak'])->toBe(85000000.0);
});
