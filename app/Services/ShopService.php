<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\SubOrder;
use Exception;

class ShopService
{
    /**
     * Attempt to delete a Shop.
     * Enforces the business rules for shop deletion.
     *
     * @throws Exception
     */
    public function delete(Shop $shop): bool
    {
        if ($shop->products()->exists()) {
            throw new Exception('Cannot delete shop because it has products.');
        }

        if ($shop->owner_id !== null) {
            throw new Exception('Cannot delete shop because it has an owner.');
        }

        if ($shop->categoryCityAssignments()->exists()) {
            throw new Exception('Cannot delete shop because it has category-city assignments.');
        }

        if (class_exists(SubOrder::class) && method_exists($shop, 'subOrders') && $shop->subOrders()->exists()) {
            throw new Exception('Cannot delete shop because it has sub-orders.');
        }

        return $shop->delete();
    }
}
