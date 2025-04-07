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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->foreignId('trainer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('category');
            $table->string('difficulty_level')->default('beginner');
            $table->integer('duration_minutes');
            $table->integer('max_participants');
            $table->string('location');
            $table->json('equipment_needed')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
