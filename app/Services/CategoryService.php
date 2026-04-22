<?php

namespace App\Services;

use App\Models\Category;
use Exception;

class CategoryService
{
    /**
     * Delete a Category.
     * Enforces the business rules for category deletion.
     *
     * @throws Exception
     */
    public function delete(Category $category): bool
    {
        if ($category->products()->exists()) {
            throw new Exception('Cannot delete category because it has products.');
        }

        if ($category->categoryCityAssignments()->exists()) {
            throw new Exception('Cannot delete category because it has city-shop assignments.');
        }

        return $category->delete();
    }
}
