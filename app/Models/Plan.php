<?php

namespace App\Models;

use App\Enum\BillingType;
use App\Enum\PlanScope;
use FontLib\TrueType\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected function casts(): array
    {
        return [
            'scope' => PlanScope::class,
            'billing_type' => BillingType::class,
            'is_active' => 'boolean'
        ];
    }
    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }
    public function features(): Collection
    {
        return $this->planFeatures->pluck('feature');
    }
}
