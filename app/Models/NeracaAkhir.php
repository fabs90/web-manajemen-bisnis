<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NeracaAkhir extends Model
{
    use HasFactory;
    protected $table = 'neraca_akhir';
    protected $fillable = ['total_kas', 'total_piutang', 'total_hutang', 'total_persediaan', 'modal', 'laba', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
