<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_course_knowledge_bases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('tier'); // Consultant, Rainmaker, Titan
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['course_id', 'tier'], 'ai_course_kb_course_tier_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_course_knowledge_bases');
    }
};

