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
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name')->nullable()->after('name');
            $table->enum('gender', ['laki-laki', 'perempuan'])->nullable()->after('full_name');
            $table->unsignedSmallInteger('height_cm')->nullable()->after('gender');
            $table->unsignedSmallInteger('weight_kg')->nullable()->after('height_cm');
            $table->string('photo_path')->nullable()->after('weight_kg');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['full_name', 'gender', 'height_cm', 'weight_kg', 'photo_path']);
        });
    }
};
