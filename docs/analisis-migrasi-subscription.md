# Analisis & Evaluasi Migrasi Fitur Subscription (Personal & Organization)

Dokumen ini berisi analisis kelayakan skema migrasi database yang telah dibuat untuk mendukung fitur langganan (*subscription*) bagi **Personal** dan **Organisasi**.

---

## 1. Ringkasan Evaluasi

**Apakah migrasi saat ini sudah cukup?**
> **Secara arsitektur dasar: SUDAH SANGAT BAIK.**
> Konsep eksklusivitas kepemilikan (*personal* vs *organisasi*), *scope* paket, dan *payment tracking* sudah dirancang dengan matang.
>
> **Namun, BELUM 100% SIAP PRODUKSI** karena masih ada **inkonsistensi nama kolom antara migrasi dan model Eloquent** (yang memicu SQL error saat dijalankan) serta **beberapa atribut esensial paket organisasi yang belum tersedia** (seperti kuota anggota / *seat limit*).

---

## 2. Kelebihan Skema Saat Ini (Arsitektur yang Sudah Tepat)

1. **Integritas Kepemilikan (Personal vs Organization) yang Kuat**
   * Lokasi: `database/migrations/2026_10_09_002752_create_subscription_payments_table.php`
   * Menggunakan relasi langsung dengan constraint XOR:
     ```sql
     CHECK ((user_id IS NULL) <> (organization_id IS NULL))
     ```
   * **Keuntungan**: Menjamin setiap langganan tepat dimiliki oleh 1 entitas (User *atau* Organisasi). Cara ini jauh lebih aman dan menjaga integritas *Foreign Key* dibandingkan polymorphic standar Laravel (`subscribable_type` dan `subscribable_id`).

2. **Pemisahan Scope Paket (`plans.scope`)**
   * Menggunakan enum `PlanScope` (`user` vs `organization`) sehingga paket personal tidak dapat dibeli untuk organisasi, dan sebaliknya.

3. **Fleksibilitas Billing Type**
   * Mendukung skema *one-time* (seumur hidup/sekali bayar) dan *recurring* (berulang dengan `duration_days`).
   * Adanya constraint: `CHECK (billing_type = 'one_time' OR duration_days IS NOT NULL)`.

4. **Silsilah Perpanjangan/Upgrade (`replace_subscription_id`)**
   * Memungkinkan pencatatan riwayat pergantian atau *upgrade* paket antar langganan.

5. **Struktur Pembayaran Terpisah (`payments`)**
   * Sudah mencakup kolom referensi transaksi, status, provider (Midtrans/Xendit), dan payload mentah JSON dari webhook.

---

## 3. Bug & Inkonsistensi Kritis (Harus Diperbaiki)

Terdapat ketidaksesuaian antara skema database dan implementasi Model Eloquent yang ada:

| No | Isu | Database (Migration) | Model Eloquent | Dampak & Resiko |
|---|---|---|---|---|
| 1 | **Nama Kolom Expired** | Kolom: `ends_at` | Model `Subscription.php` menggunakan `expires_at` pada `$casts` dan method `scopeLapsed()` | **Fatal SQL Error:** `Unknown column 'expires_at'` saat scheduler mengecek expired subscription. |
| 2 | **Konteks Organisasi User** | Kolom: `organization_id` di tabel `users` | Model `User.php` mendefinisikan relasi: `belongsTo(Organization::class, 'current_organization_id')` | Relasi `$user->currentOrganization` gagal menemukan foreign key yang cocok. |
| 3 | **Tipe Data Slug Organisasi** | Menggunakan `$table->text('slug')` | Seharusnya unik untuk URL route | Kolom tipe `TEXT` tidak dapat diberi constraint `UNIQUE` di MySQL tanpa spesifikasi panjang prefix index. |

---

## 4. Kebutuhan Fitur yang Belum Terjawab (Feature Gaps)

### A. Kuota Anggota Organisasi (*Seat Limit / Member Limit*)
* **Masalah**: Pembeda utama harga paket organisasi biasanya adalah jumlah anggota yang boleh diundang (contoh: *Starter Org*: maks 5 anggota, *Pro Org*: maks 25 anggota).
* **Kondisi saat ini**: Tabel `plans` belum memiliki kolom batasan kuota anggota.
* **Rekomendasi**: Tambahkan kolom `max_members` (nullable integer) di tabel `plans`.

### B. Audit Siapa yang Membayar di Organisasi (*Payer Tracking*)
* **Masalah**: Pada akun organisasi, pembayaran langganan dapat dipicu oleh *Owner* atau salah satu *Admin*.
* **Kondisi saat ini**: Tabel `payments` hanya terhubung ke `subscription_id`.
* **Rekomendasi**: Tambahkan `$table->foreignId('user_id')->nullable()->constrained('users')` pada tabel `payments` untuk mencatat siapa user yang melakukan *checkout*.

### C. Siklus Hidup Status Langganan
* **Kondisi saat ini**:
  * Migrasi memiliki kolom `canceled_at`.
  * Namun enum `SubscriptionStatus` hanya memiliki: `Pending`, `Active`, `Expired`.
* **Rekomendasi**: Tambahkan status `Canceled` ke dalam `SubscriptionStatus` jika Anda ingin membedakan antara langganan yang sengaja dibatalkan dengan yang habis masa berlakunya secara alami.

### D. Dampak Terhadap Kepemilikan Data Bisnis (*Multi-Tenancy Scoping*)
* **Kondisi saat ini**: Seluruh model transaksi bisnis di aplikasi (`Barang`, `Pelanggan`, `Account`, `KasirTransactionLog`, dll.) berelasi langsung ke `user_id`.
* **Pertanyaan Arsitektur**:
  * Jika sebuah organisasi berlangganan, apakah seluruh anggota organisasi mengakses pembukuan yang sama?
  * Jika **Ya**, tabel transaksi dan master data perlu mulai mendukung `organization_id` (nullable untuk personal user) atau sistem personal workspace otomatis.

---

## 5. Rekomendasi Solusi Teknis

### Langkah 1: Buat Migration Alter Tambahan
Jalankan migrasi perbaikan berikut untuk menyempurnakan tabel yang sudah ada:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Perbaiki slug organisasi agar unik dan ramah URL
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('slug', 100)->unique()->change();
        });

        // 2. Samakan foreign key di tabel users untuk active workspace/organization
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('organization_id', 'current_organization_id');
        });

        // 3. Tambahkan kuota anggota di tabel plans
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('max_members')->nullable()->after('duration_days');
        });

        // 4. Catat user yang membayar (payer) di tabel payments
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('subscription_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('max_members');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('current_organization_id', 'organization_id');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->text('slug')->change();
        });
    }
};
```

### Langkah 2: Sinkronkan Model `Subscription.php`
Pastikan nama kolom waktu kedaluwarsa konsisten:
Gunakan **`ends_at`** (standar Laravel) atau ubah kolom database menjadi **`expires_at`**.

Contoh perbaikan di `app/Models/Subscription.php`:
```php
protected function casts(): array
{
    return [
        'status' => SubscriptionStatus::class,
        'starts_at' => 'datetime',
        'ends_at' => 'datetime', // Diubah dari expires_at ke ends_at
        'canceled_at' => 'datetime',
    ];
}

public function scopeLapsed(Builder $q): Builder
{
    $cutoff = now()->subDays(config('subscription.grace_days'));
    return $q->where('status', SubscriptionStatus::Active)->whereNotNull('ends_at')
        ->where(fn($q) => $q
            ->where(fn($q) => $q->where('ends_at', '<', now())
                ->whereHas('plan', fn($p) => $p->where('billing_type', BillingType::OneTime)))
            ->orWhere(fn($q) => $q->where('ends_at', '<', $cutoff)
                ->whereHas('plan', fn($p) => $p->where('billing_type', BillingType::Recurring))));
}
```
