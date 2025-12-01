<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClosedPeriod extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'year', 'closed_at'];

    protected $casts = [
        'closed_at' => 'datetime',
        'year' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}