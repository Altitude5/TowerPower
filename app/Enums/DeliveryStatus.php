<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Scheduled = 'scheduled';
    case Departed = 'departed';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}
