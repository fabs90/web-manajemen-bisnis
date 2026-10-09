# Skema Paket Langganan & Strategi Fitur Berbayar

Dokumen ini mendefinisikan rincian pembagian paket langganan (*pricing tier*) untuk akun **Personal** dan **Organisasi**, pemetaan fitur berbayar, serta implementasinya ke skema database `plans` dan `plan_features`.

---

## 1. Tabel Matriks Skema Paket Langganan

| Kriteria / Fitur | 🆓 Free (Gratis) | 👤 Personal Pro | 🏢 Org Starter | 🏬 Org Enterprise |
| :--- | :---: | :---: | :---: | :---: |
| **Target Pengguna** | Pengguna Baru / Uji Coba | Toko / Freelancer Mandiri | UMKM / Bisnis Tim Kecil | Bisnis Menengah & Korporat |
| **Scope (`plans.scope`)** | `user` | `user` | `organization` | `organization` |
| **Tipe Penagihan (`billing_type`)** | `one_time` (Free) | `recurring` (Bulanan / Tahunan) | `recurring` (Bulanan / Tahunan) | `recurring` (Tahunan) / Custom |
| **Batas Anggota (`max_members`)** | 1 User (Pemilik saja) | 1 User (Pemilik saja) | Maks. **5 Anggota** | **Unlimited** Anggota |
| **Kasir (POS) & Transaksi Harian** | ✅ Aktif | ✅ Aktif | ✅ Aktif | ✅ Aktif |
| **Input Pendapatan & Pengeluaran** | ✅ Aktif | ✅ Aktif | ✅ Aktif | ✅ Aktif |
| **Batas Jumlah Barang / SKU** | Maks. 30 Barang | **Unlimited** | **Unlimited** | **Unlimited** |
| **Cetak Struk Thermal Printer** | ❌ Terkunci | ✅ **Aktif** | ✅ **Aktif** | ✅ **Aktif** |
| **Ekspor Laporan Keuangan (Laba Rugi & Neraca PDF)** | ❌ (Hanya lihat di layar) | ✅ **Aktif (PDF)** | ✅ **Aktif (PDF)** | ✅ **Aktif (PDF)** |
| **Paket Diskon Kasir** | ❌ Terkunci | ✅ **Aktif** | ✅ **Aktif** | ✅ **Aktif** |
| **Administrasi Surat Bisnis (SPB, SPP, Faktur, Memo Kredit)** | ❌ Terkunci | ❌ Terkunci | ✅ **Aktif** | ✅ **Aktif** |
| **Manajemen Rapat & Notulen + TTD Digital** | ❌ Terkunci | ❌ Terkunci | ❌ Terkunci | ✅ **Aktif** |
| **Custom Branding (Logo & Kop Surat Resmi di PDF)** | ❌ (Watermark sistem) | ❌ | ✅ **Aktif** | ✅ **Aktif** |
| **Kirim Invoice & Surat via Email Otomatis** | ❌ Terkunci | ❌ Terkunci | ❌ Terkunci | ✅ **Aktif** |
| **Prioritas Layanan Dukungan (Support)** | Standar / Komunitas | Standar | Prioritas | Dedicated Account Manager |

---

## 2. Pemetaan Fitur ke Enum `App\Enum\Feature`

Untuk mendukung pembagian paket di atas, berikut adalah nilai enum yang direkomendasikan pada `app/Enum/Feature.php`:

```php
namespace App\Enum;

enum Feature: string
{
    // Fitur Dasar & Kasir
    case ThermalPrinter = 'thermal_printer';
    case DiscountPackage = 'discount_package';
    case UnlimitedInventory = 'unlimited_inventory';

    // Laporan Keuangan
    case FinancialReportExport = 'financial_report_export';

    // Administrasi Bisnis (Faktur, SPB, SPP, Memo Kredit)
    case AdministrationDocuments = 'administration_documents';

    // Kesekretariatan & Rapat
    case MeetingManagement = 'meeting_management';

    // Branding & Otomasi
    case CustomBranding = 'custom_branding';
    case EmailDispatch = 'email_dispatch';

    public function label(): string
    {
        return match ($this) {
            self::ThermalPrinter => 'Cetak Struk Thermal Printer',
            self::DiscountPackage => 'Manajemen Paket Diskon',
            self::UnlimitedInventory => 'Katalog Barang Tanpa Batas',
            self::FinancialReportExport => 'Ekspor PDF Laporan Keuangan (Laba Rugi & Neraca)',
            self::AdministrationDocuments => 'Dokumen Bisnis (Faktur, SPB, SPP, Memo Kredit)',
            self::MeetingManagement => 'Manajemen Rapat & Notulen Resmi',
            self::CustomBranding => 'Kop Surat & Logo Perusahaan Kustom',
            self::EmailDispatch => 'Kirim Surat/Invoice via Email Otomatis',
        };
    }
}
```

---

## 3. Contoh Data Seeder Database (`PlanSeeder.php`)

Berikut adalah representasi data seeder untuk tabel `plans` dan relasi pivot `plan_features`:

```php
namespace Database\Seeders;

use App\Enum\BillingType;
use App\Enum\Feature;
use App\Enum\PlanScope;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Paket Free (Personal)
        $free = Plan::updateOrCreate(['code' => 'personal_free'], [
            'name' => 'Free',
            'scope' => PlanScope::User,
            'billing_type' => BillingType::OneTime,
            'price' => 0,
            'duration_days' => null,
            'max_members' => 1,
            'is_active' => true,
        ]);
        // Free tidak memiliki fitur berbayar khusus (fitur standar terbuka)

        // 2. Paket Personal Pro
        $personalPro = Plan::updateOrCreate(['code' => 'personal_pro_monthly'], [
            'name' => 'Personal Pro (Bulanan)',
            'scope' => PlanScope::User,
            'billing_type' => BillingType::Recurring,
            'price' => 49000,
            'duration_days' => 30,
            'max_members' => 1,
            'is_active' => true,
        ]);
        $personalPro->planFeatures()->createMany([
            ['feature' => Feature::ThermalPrinter->value],
            ['feature' => Feature::DiscountPackage->value],
            ['feature' => Feature::FinancialReportExport->value],
            ['feature' => Feature::UnlimitedInventory->value],
        ]);

        // 3. Paket Organisasi Starter
        $orgStarter = Plan::updateOrCreate(['code' => 'org_starter_monthly'], [
            'name' => 'Organization Starter (Bulanan)',
            'scope' => PlanScope::Organization,
            'billing_type' => BillingType::Recurring,
            'price' => 149000,
            'duration_days' => 30,
            'max_members' => 5,
            'is_active' => true,
        ]);
        $orgStarter->planFeatures()->createMany([
            ['feature' => Feature::ThermalPrinter->value],
            ['feature' => Feature::DiscountPackage->value],
            ['feature' => Feature::FinancialReportExport->value],
            ['feature' => Feature::UnlimitedInventory->value],
            ['feature' => Feature::AdministrationDocuments->value],
            ['feature' => Feature::CustomBranding->value],
        ]);

        // 4. Paket Organisasi Enterprise
        $orgEnterprise = Plan::updateOrCreate(['code' => 'org_enterprise_yearly'], [
            'name' => 'Organization Enterprise (Tahunan)',
            'scope' => PlanScope::Organization,
            'billing_type' => BillingType::Recurring,
            'price' => 1499000,
            'duration_days' => 365,
            'max_members' => null, // null = unlimited
            'is_active' => true,
        ]);
        $orgEnterprise->planFeatures()->createMany([
            ['feature' => Feature::ThermalPrinter->value],
            ['feature' => Feature::DiscountPackage->value],
            ['feature' => Feature::FinancialReportExport->value],
            ['feature' => Feature::UnlimitedInventory->value],
            ['feature' => Feature::AdministrationDocuments->value],
            ['feature' => Feature::CustomBranding->value],
            ['feature' => Feature::MeetingManagement->value],
            ['feature' => Feature::EmailDispatch->value],
        ]);
    }
}
```

---

## 4. Pola Pengecekan Fitur di Aplikasi (Feature Authorization)

Contoh cara mengamankan controller atau tombol UI:

### Pada Blade View:
```blade
@can('access-feature', \App\Enum\Feature::ThermalPrinter)
    <a href="{{ route('printer.index') }}" class="btn btn-primary">Atur Thermal Printer</a>
@else
    <button class="btn btn-disabled" title="Tersedia di paket Pro ke atas" disabled>
        Atur Thermal Printer 🔒
    </button>
@endcan
```

### Pada Controller / Route Middleware:
```php
public function exportToPdf()
{
    abort_unless(
        auth()->user()->hasFeature(\App\Enum\Feature::FinancialReportExport),
        403,
        'Fitur ekspor laporan keuangan hanya tersedia untuk paket Pro & Organisasi.'
    );

    // Proses render PDF...
}
```
