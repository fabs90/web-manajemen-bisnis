<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengisianKasKecilLog extends Model
{
    protected $table = 'pengisian_kas_kecil_logs';

    protected $fillable = [
        'journal_entry_id',
        'kas_kecil_id',
        'uraian',
        'jumlah',
        'tanggal_transaksi',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function kasKecil()
    {
        return $this->belongsTo(KasKecil::class);
    }
}
