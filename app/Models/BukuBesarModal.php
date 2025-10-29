<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BukuBesarModal extends Model
{
    use HasFactory;

    protected $table = "buku_besar_modal";

    protected $fillable = [
        "user_id",
        "kode",
        "neraca_awal_id",
        "rugi_laba_id",
        "tanggal",
        "uraian",
        "debit",
        "kredit",
        "saldo",
    ];

    // === Events ===
    protected static function boot()
    {
        parent::boot();

        // Otomatis isi UUID kalau belum ada
        static::creating(function ($model) {
            if (empty($model->kode)) {
                $model->kode = Str::uuid();
            }
        });
    }

    // === Relasi ke tabel lain ===

    /**
     * User yang memiliki buku besar modal ini
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Neraca Awal (setoran modal pertama)
     */
    public function neracaAwal()
    {
        return $this->belongsTo(NeracaAwal::class, "neraca_awal_id");
    }

    /**
     * Relasi ke Rugi Laba (penambahan modal dari laba bersih)
     */
    public function rugiLaba()
    {
        return $this->belongsTo(RugiLaba::class, "rugi_laba_id");
    }

    /**
     * Relasi ke Buku Besar Kas (jika diperlukan untuk pencocokan kode transaksi)
     */
    public function kas()
    {
        return $this->hasOne(BukuBesarKas::class, "kode", "kode");
    }

    // === Accessor (opsional) ===

    /**
     * Format saldo dalam rupiah.
     */
    public function getSaldoFormattedAttribute()
    {
        return "Rp " . number_format($this->saldo, 0, ",", ".");
    }

    /**
     * Menentukan apakah transaksi ini adalah penambahan atau pengurangan modal.
     */
    public function getTipeTransaksiAttribute()
    {
        if ($this->debit > 0 && $this->kredit == 0) {
            return "Pengurangan Modal (Prive)";
        } elseif ($this->kredit > 0 && $this->debit == 0) {
            return "Penambahan Modal";
        } else {
            return "Penyesuaian";
        }
    }
}
