<?php

namespace App\Enum;

enum PlanScope: string
{
    case User = 'user';
    case Organization = 'organization';

    public function label(): string
    {
        return match ($this) {
            self::User => 'User',
            self::Organization => 'Organization',
        };
    }
}
