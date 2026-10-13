<?php

namespace App\Models;

use App\Enum\Feature;
use Illuminate\Database\Eloquent\Model;

class PlanFeature extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'plan_id',
        'feature',
    ];

    protected function casts(): array
    {
        return ['feature' => Feature::class];
    }
}
