<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_exam_id')->constrained('course_exams')->cascadeOnDelete();
            $table->text('question_text');
            $table->json('options'); // ["A", "B", "C", "D"]
            $table->unsignedTinyInteger('correct_index'); // 0-based index of correct option
            $table->unsignedSmallInteger('sequence')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_exam_questions');
    }
};
