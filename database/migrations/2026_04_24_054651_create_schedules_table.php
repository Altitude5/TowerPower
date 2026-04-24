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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_person_id')->constrained('users')->restrictOnDelete();
            $table->string('type'); // positive / negative
            $table->string('recurrence');
            $table->unsignedTinyInteger('day_of_week')->nullable(); // 0 (Sun) to 6 (Sat)
            $table->date('date')->nullable();
            $table->timestamps();

            $table->index('shop_id');
            $table->index('city_id');
            $table->index('delivery_person_id');
            $table->index(['shop_id', 'city_id']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
