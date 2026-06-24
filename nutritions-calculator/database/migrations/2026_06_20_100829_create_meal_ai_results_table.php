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
        Schema::create('meal_ai_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_log_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('food_name');
            $table->decimal('calories', 8, 2);
            $table->decimal('protein_g', 8, 2)->default(0);
            $table->decimal('carbs_g', 8, 2)->default(0);
            $table->decimal('fat_g', 8, 2)->default(0);
            $table->decimal('fiber_g', 8, 2)->default(0);
            $table->json('vitamins_json')->nullable();
            $table->enum('calorie_status', ['kurang', 'cukup', 'kelebihan']);
            $table->text('summary');
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_ai_results');
    }
};
