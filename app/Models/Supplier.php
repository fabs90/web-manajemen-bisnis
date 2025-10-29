<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $table = 'supplier';
    protected $fillable = ['nama', 'kontak', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bukuBesarHutang()
    {
        return $this->hasMany(BukuBesarHutang::class);
    }
}
