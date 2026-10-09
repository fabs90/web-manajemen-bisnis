<?php

namespace App\Enum;

enum PlanScope: string
{
    case User = 'user';
    case Organization = 'organization';
}
