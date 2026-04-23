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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ratable_type');
            $table->unsignedBigInteger('ratable_id');
            $table->unsignedTinyInteger('score');
            $table->timestamps();

            $table->unique(['user_id', 'ratable_type', 'ratable_id']);
            $table->index(['ratable_type', 'ratable_id']);
        });

        // Add check constraint for score between 1 and 5
        if (config('database.default') === 'pgsql') {
            DB::statement('ALTER TABLE ratings ADD CONSTRAINT check_score_range CHECK (score >= 1 AND score <= 5)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
