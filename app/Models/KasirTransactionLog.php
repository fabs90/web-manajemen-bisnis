<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasirTransactionLog extends Model
{
    protected $table = 'kasir_transaction_logs';

    protected $fillable = [
        'journal_entry_id',
        'uraian',
        'tanggal_transaksi',
        'jumlah',
        'bayar',
        'kembalian',
        'user_id',
        'diskon',
        'paket_diskon_id',
    ];

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}