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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->integer('amount');
            $table->string('status')->index();
            $table->string('transaction_reference')->nullable();
            $table->string('currency');
            $table->string('gateway');
            $table->timestamps();

            $table->unique(['gateway', 'transaction_reference']);
            $table->index('order_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
