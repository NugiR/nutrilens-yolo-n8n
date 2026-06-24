<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meal_logs', function (Blueprint $table) {
            $table->string('detected_food_name')->nullable();
            $table->decimal('detection_confidence', 5, 4)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('meal_logs', function (Blueprint $table) {
            $table->dropColumn(['detected_food_name', 'detection_confidence']);
        });
    }
};
