<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_verified',
        'alamat',
        'nomor_telepon',
        'logo_perusahaan',
        'ttd_pemimpin',
        'otp',
        'otp_expires_at',
        'is_printer_enabled',
        'printer_store_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function checkIsVerified(): bool
    {
        return $this->is_verified;
    }

    // Relasi ke master data
    public function pelanggan()
    {
        return $this->hasMany(Pelanggan::class);
    }

    public function supplier()
    {
        return $this->hasMany(Supplier::class);
    }

    public function barang()
    {
        return $this->hasMany(Barang::class);
    }

    // Relasi ke sistem akuntansi baru
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function journalItems()
    {
        return $this->hasMany(JournalItem::class);
    }

    public function kartuGudang()
    {
        return $this->hasMany(KartuGudang::class);
    }

    public function agendaSuratMasuk()
    {
        return $this->hasMany(AgendaSuratMasuk::class);
    }

    public function agendaTelpon()
    {
        return $this->hasMany(AgendaTelpon::class);
    }
}
