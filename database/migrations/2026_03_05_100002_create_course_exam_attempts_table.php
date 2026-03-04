<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_exam_id')->constrained('course_exams')->cascadeOnDelete();
            $table->unsignedTinyInteger('score_percent');
            $table->boolean('passed');
            $table->json('answers'); // [{ "question_id": 1, "selected_index": 0 }, ...]
            $table->timestamp('started_at');
            $table->timestamp('submitted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_exam_attempts');
    }
};
