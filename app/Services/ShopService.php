<?php

namespace App\Services;

use App\Models\Shop;
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
        if (class_exists(\App\Models\Product::class) && $shop->products()->exists()) {
            throw new Exception("Cannot delete shop because it has products.");
        }

        if ($shop->owner_id !== null) {
            throw new Exception("Cannot delete shop because it has an owner.");
        }

        if (class_exists(\App\Models\SubOrder::class) && method_exists($shop, 'subOrders') && $shop->subOrders()->exists()) {
            throw new Exception("Cannot delete shop because it has sub-orders.");
        }

        return $shop->delete();
    }
}
