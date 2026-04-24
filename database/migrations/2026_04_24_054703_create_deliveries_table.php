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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_order_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('delivery_person_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('tower_id')->constrained()->restrictOnDelete();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->foreignId('city_id')->constrained()->restrictOnDelete();
            $table->date('date');
            $table->time('time')->nullable();
            $table->string('status');
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('image_url')->nullable();
            $table->timestamps();

            $table->index('schedule_id');
            $table->index('delivery_person_id');
            $table->index('customer_id');
            $table->index('tower_id');
            $table->index(['shop_id', 'city_id']);
            $table->index('date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
