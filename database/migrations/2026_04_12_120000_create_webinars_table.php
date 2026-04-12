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
        Schema::create('webinars', function (Blueprint $row) {
            $row->id();
            $row->string('title');
            $row->text('description')->nullable();
            $row->string('zoom_link')->nullable();
            $row->string('image_url')->nullable();
            $row->dateTime('scheduled_at')->nullable();
            $row->boolean('is_active')->default(true);
            $row->boolean('is_promotional')->default(false);
            $row->string('target_tier')->nullable(); // Consultant, Rainmaker, Titan
            $row->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webinars');
    }
};
