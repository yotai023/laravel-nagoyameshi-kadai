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
        Schema::create('regular_holiday_restaurant', function (Blueprint $table) {
            $table->foreignId('restaurant_id')
                ->constrained('restaurants')
                ->cascadeOnDelete();
            $table->foreignId('regular_holiday_id')
                ->constrained('regular_holidays')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['restaurant_id', 'regular_holiday_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regular_holiday_restaurant');
    }
};
