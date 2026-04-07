<?php

namespace App\Exceptions;

use App\Models\Product;
use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(public Product $product)
    {
        parent::__construct("Insufficient stock for product: {$product->name}");
    }
}
