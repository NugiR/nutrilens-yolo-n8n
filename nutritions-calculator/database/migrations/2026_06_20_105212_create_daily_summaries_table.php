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
        Schema::create('daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('total_calories', 8, 2)->default(0);
            $table->decimal('total_protein_g', 8, 2)->default(0);
            $table->decimal('total_carbs_g', 8, 2)->default(0);
            $table->decimal('total_fat_g', 8, 2)->default(0);
            $table->decimal('total_fiber_g', 8, 2)->default(0);
            $table->tinyInteger('meal_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_summaries');
    }
};
