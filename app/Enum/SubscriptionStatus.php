<?php

namespace App\Enum;

enum SubscriptionStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Expired = 'expired';
    case Canceled = 'canceled';
}
