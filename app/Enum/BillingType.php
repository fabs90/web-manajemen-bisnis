<?php

namespace App\Enum;

enum BillingType: string
{
    case OneTime = 'one_time';
    case Recurring = 'recurring';
}