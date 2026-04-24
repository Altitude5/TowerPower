<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Enums\SubOrderStatus;
use App\Models\SubOrder;

class SubOrderObserver
{
    /**
     * Handle the SubOrder "updated" event.
     */
    public function updated(SubOrder $subOrder): void
    {
        if ($subOrder->wasChanged('status')) {
            $order = $subOrder->order()->first(); // Get a fresh instance

            if (! $order) {
                return;
            }

            // Check if all suborders are completed
            $allCompleted = $order->subOrders()->where('status', '!=', SubOrderStatus::Completed->value)->doesntExist();

            if ($allCompleted && $order->canTransitionTo(OrderStatus::Completed)) {
                $order->update(['status' => OrderStatus::Completed]);
            }
        }
    }
}
