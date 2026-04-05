<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->integer('price');
            $table->string('price_type'); // 'Unit', 'Weight', 'Volume'
            $table->string('image_path')->nullable();
            $table->string('sku')->nullable();
            $table->foreignId('shop_id')->constrained('shops'); // No cascade
            $table->decimal('stock_quantity', 10, 3)->nullable();
            $table->decimal('stock_weight', 10, 3)->nullable();
            $table->decimal('stock_volume', 10, 3)->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories');
            $table->boolean('available')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['shop_id', 'name']);
            $table->index('sku');
            $table->index('available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
