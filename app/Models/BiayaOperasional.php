<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiayaOperasional extends Model
{
    use HasFactory;
    protected $table = 'biaya_operasional';
    protected $fillable = ['tanggal', 'keterangan', 'jumlah', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
