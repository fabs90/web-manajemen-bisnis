<?php

namespace App\Models;

use App\Traits\ClearsDashboardCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KartuGudang extends Model
{
    use HasFactory, ClearsDashboardCache;

    protected $table = 'kartu_gudang';

    protected $fillable = [
        'barang_id',
        'tanggal',
        'diterima',
        'dikeluarkan',
        'user_id',
        'uraian',
        'saldo_persatuan',
        'saldo_perkemasan',
        'journal_entry_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
