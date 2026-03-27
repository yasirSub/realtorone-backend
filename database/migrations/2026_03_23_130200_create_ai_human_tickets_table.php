<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_human_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('chat_session_id')->nullable()->constrained('chat_sessions')->onDelete('set null');

            // Created when user requests human help (handoff).
            $table->text('request_message')->nullable();

            // Admin resolution / summary.
            $table->text('admin_resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('status')->default('open'); // open, resolved

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_human_tickets');
    }
};

