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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    // Relasi ke transaksi
    public function penerimaanKas()
    {
        return $this->hasMany(PenerimaanKas::class);
    }

    public function pengeluaranKas()
    {
        return $this->hasMany(PengeluaranKas::class);
    }

    public function bukuBesarKas()
    {
        return $this->hasMany(BukuBesarKas::class);
    }

    public function bukuBesarPiutang()
    {
        return $this->hasMany(BukuBesarPiutang::class);
    }

    public function bukuBesarHutang()
    {
        return $this->hasMany(BukuBesarHutang::class);
    }

    public function kartuGudang()
    {
        return $this->hasMany(KartuGudang::class);
    }

    public function biayaOperasional()
    {
        return $this->hasMany(BiayaOperasional::class);
    }

    public function neracaAwal()
    {
        return $this->hasOne(NeracaAwal::class);
    }

    public function neracaAkhir()
    {
        return $this->hasOne(NeracaAkhir::class);
    }

    public function rugiLaba()
    {
        return $this->hasOne(RugiLaba::class);
    }
}