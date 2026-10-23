<?php

namespace App\Models;

use App\Enum\PlanScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_members')
            ->withPivot('role')->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function planScope(): PlanScope
    {
        return PlanScope::Organization;
    }

    public function owners(): BelongsToMany
    {
        return $this->members()->wherePivot('role', 'owner');
    }

    protected function email(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->owners->first()?->email);
    }
}
