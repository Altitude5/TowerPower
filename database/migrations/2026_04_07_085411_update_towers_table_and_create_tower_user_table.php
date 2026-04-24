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
        Schema::table('towers', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->foreignId('street_id')->after('name')->constrained()->cascadeOnDelete();
            $table->string('house_number')->after('street_id');
            $table->string('zipcode')->after('house_number');
            $table->foreignId('city_id')->after('zipcode')->constrained()->cascadeOnDelete();
            $table->string('state')->after('city_id')->nullable();
            $table->string('country')->after('state')->nullable();
            $table->decimal('latitude', 10, 7)->after('country')->nullable();
            $table->decimal('longitude', 10, 7)->after('latitude')->nullable();

            $table->index('name');
            $table->index('city_id');
            $table->index('street_id');
        });

        Schema::create('tower_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tower_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('apartment_number');
            $table->string('floor');
            $table->timestamps();

            $table->index('tower_id');
            $table->index('user_id');
            $table->unique(['tower_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tower_user');

        Schema::table('towers', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['city_id']);
            $table->dropIndex(['street_id']);

            $table->dropForeign(['street_id']);
            $table->dropForeign(['city_id']);

            $table->dropColumn([
                'street_id',
                'house_number',
                'zipcode',
                'city_id',
                'state',
                'country',
                'latitude',
                'longitude',
            ]);

            $table->string('name')->nullable(false)->change();
        });
    }
};
