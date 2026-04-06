<?php

namespace App\Enums;

enum SubOrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Returned = 'returned';
}
