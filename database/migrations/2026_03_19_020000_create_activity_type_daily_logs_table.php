<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_type_daily_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_type_id')->constrained('activity_types')->cascadeOnDelete();
            $table->unsignedInteger('day_number');
            $table->text('task_description')->nullable();
            $table->text('script_idea')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->unique(['activity_type_id', 'day_number'], 'activity_type_day_unique');
            $table->index('day_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_type_daily_logs');
    }
};
