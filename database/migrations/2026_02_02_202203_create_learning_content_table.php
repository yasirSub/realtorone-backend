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
        Schema::create('learning_content', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', [
                'marketFundamentals',
                'leadSystems',
                'communication',
                'negotiation',
                'hniHandling',
                'commissionScaling',
                'dealArchitecture',
                'brandAuthority',
            ]);
            $table->enum('type', ['video', 'audio', 'article', 'quiz']);
            $table->enum('tier', ['free', 'premium'])->default('free');
            $table->string('thumbnail_url')->nullable();
            $table->string('content_url')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category', 'tier']);
        });
        
        // User progress tracking
        Schema::create('user_learning_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('learning_content_id')->constrained('learning_content')->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->integer('progress_percent')->default(0);
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'learning_content_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_learning_progress');
        Schema::dropIfExists('learning_content');
    }
};
